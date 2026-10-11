<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pemilik;
use App\Models\Hewan;
use App\Models\RekamMedis;
use App\Models\Dokter;
use App\Models\Paramedis;
use App\Models\Pelayanan;
use App\Models\JenisHewan;
use App\Models\Diagnosa;
use App\Models\Anamnesa;
use App\Models\Obat;
use App\Models\Penandatangan;
use App\Exports\RekapLaporanExport;
use App\Exports\RekapLaporanExport2;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class RekamMedisController extends Controller
{
    /**
     * Rapikan alamat sebelum disimpan supaya grouping di modul Surveilans konsisten.
     */
    private function normalizeAlamat(?string $alamat): string
    {
        $alamat = trim($alamat ?? '');
        if ($alamat === '') {
            return '-';
        }
        if (str_contains($alamat, 'Kota Kediri')) {
            return $alamat;
        }
        return mb_convert_case($alamat, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Rapikan nama yang diketik manual lewat opsi "Lain-lain" (diagnosa/anamnesa/obat)
     */
    private function normalizeNamaMaster(string $nama): string
    {
        $nama = trim(preg_replace('/\s+/', ' ', $nama));
        return mb_convert_case($nama, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Tampilkan halaman Form Input Rekam Medis
     */
    public function index()
    {
        $dokters = Dokter::orderBy('nama_dokter')->get();
        $paramedis = Paramedis::orderBy('nama_paramedis')->get();
        $pelayanans = Pelayanan::orderBy('nama_pelayanan')->get();
        $jenisHewans = JenisHewan::orderBy('nama_jenis')->get();
        $anamnesas = Anamnesa::orderBy('nama_anamnesa')->get();
        $diagnosas = Diagnosa::orderBy('nama_diagnosa')->get();
        $obats = Obat::orderBy('nama_obat')->get();

        // Ambil ID Hewan terbesar di database, lalu tambah 1 untuk estimasi ID
        $nextIdHewan = \App\Models\Hewan::max('id_hewan') + 1;
        if (!$nextIdHewan) {
            $nextIdHewan = 1;
        }
        
        return view('rekam_medis.input', compact(
            'dokters', 
            'paramedis', 
            'pelayanans', 
            'jenisHewans', 
            'anamnesas', 
            'diagnosas', 
            'obats',
            'nextIdHewan'
        ));
    }

    /**
     * Endpoint API AJAX untuk Live Search Pasien & Pemilik
     */
    public function search(Request $request)
    {
        $q = $request->q;

        if (!$q || strlen($q) < 2) {
            return response()->json(['hewans' => [], 'pemiliks' => []]);
        }

        $hewans = Hewan::with('pemilik')
            ->where(function ($query) use ($q) {
                $query->where('nama_hewan', 'like', "%$q%")
                    ->orWhere('id_hewan', 'like', "%$q%")
                    ->orWhereHas('pemilik', function ($sub) use ($q) {
                        $sub->where('nama_pemilik', 'like', "%$q%")
                            ->orWhere('no_hp', 'like', "%$q%");
                    });
            })
            ->limit(10)
            ->get();

        $pemiliks = Pemilik::with('hewans')
            ->where('nama_pemilik', 'like', "%$q%")
            ->orWhere('no_hp', 'like', "%$q%")
            ->limit(10)
            ->get();

        return response()->json([
            'hewans' => $hewans,
            'pemiliks' => $pemiliks
        ]);
    }

    /**
     * Proses Simpan Data Transaksi (Create/Update berjenjang)
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'nama_pemilik' => 'required|string|max:100',
            'nama_hewan' => 'required|string|max:100',
            'jenis_hewan' => 'required',
            'jenis_kelamin' => 'required',
            'pelayanan' => 'required',
            'dokter' => 'required',
            'diagnosa_lain' => 'nullable|string|max:100',
            'anamnesa_lain.*' => 'nullable|string|max:100',
            'terapi_lain.*' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            // 1. Simpan/Update Pemilik
            if ($request->id_pemilik) {
                $pemilik = Pemilik::findOrFail($request->id_pemilik);
                $pemilik->update([
                    'nama_pemilik' => $request->nama_pemilik,
                    'no_hp' => $request->no_hp_pemilik ?? '-',
                    'alamat' => $this->normalizeAlamat($request->alamat)
                ]);
            } else {
                $pemilik = Pemilik::create([
                    'nama_pemilik' => $request->nama_pemilik,
                    'no_hp' => $request->no_hp_pemilik ?? '-',
                    'alamat' => $this->normalizeAlamat($request->alamat)
                ]);
            }

            $umur = trim($request->umur_hewan ?? '0');

            // 2. Simpan/Update Hewan
            if ($request->id_hewan) {
                $hewan = Hewan::findOrFail($request->id_hewan);
                $hewan->update([
                    'id_jenis' => $request->jenis_hewan,
                    'nama_hewan' => $request->nama_hewan,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'umur' => $umur,
                    'warna' => $request->warna_hewan ?? '-',
                    'berat_badan' => $request->berat_badan ?: null,
                ]);
            } else {
                $hewan = Hewan::create([
                    'id_pemilik' => $pemilik->id_pemilik,
                    'id_jenis' => $request->jenis_hewan,
                    'nama_hewan' => $request->nama_hewan,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'umur' => $umur,
                    'warna' => $request->warna_hewan ?? '-',
                    'berat_badan' => $request->berat_badan ?: null,
                ]);
            }

            // 3. Tentukan Diagnosa -- termasuk dukungan "Lain-lain"
            $id_diagnosa = null;
            if ($request->diagnosa === 'lainlain') {
                if ($request->filled('diagnosa_lain')) {
                    $diagnosaBaru = Diagnosa::firstOrCreate([
                        'nama_diagnosa' => $this->normalizeNamaMaster($request->diagnosa_lain),
                    ]);
                    $id_diagnosa = $diagnosaBaru->id_diagnosa;
                }
            } elseif ($request->filled('diagnosa')) {
                $id_diagnosa = is_array($request->diagnosa) ? $request->diagnosa[0] : $request->diagnosa;
            }

            // 4. Simpan ke Rekam Medis
            $rekamMedis = RekamMedis::create([
                'id_hewan' => $hewan->id_hewan,
                'id_dokter' => $request->dokter,
                'id_paramedis' => $request->paramedis,
                'id_pelayanan' => $request->pelayanan,
                'id_diagnosa' => $id_diagnosa,
                'tanggal' => $request->tanggal,
                'no_karcis' => $request->no_karcis ?? '-'
            ]);

            // 5. Simpan ke Tabel Pivot (Anamnesa), termasuk entri "Lain-lain"
            $anamnesaIds = $request->input('anamnesa', []);
            if (!is_array($anamnesaIds)) {
                $anamnesaIds = [$anamnesaIds];
            }
            if ($request->filled('anamnesa_lain')) {
                foreach ($request->input('anamnesa_lain', []) as $namaBaru) {
                    $namaBaru = $this->normalizeNamaMaster($namaBaru);
                    if ($namaBaru === '') {
                        continue;
                    }
                    $anamnesaBaru = Anamnesa::firstOrCreate(['nama_anamnesa' => $namaBaru]);
                    $anamnesaIds[] = $anamnesaBaru->id_anamnesa;
                }
            }
            if (!empty($anamnesaIds)) {
                $rekamMedis->anamnesas()->attach(array_unique($anamnesaIds));
            }

            // 6. Simpan ke Tabel Pivot (Terapi/Obat), termasuk entri "Lain-lain"
            $terapiIds = $request->input('terapi', []);
            if (!is_array($terapiIds)) {
                $terapiIds = [$terapiIds];
            }
            if ($request->filled('terapi_lain')) {
                foreach ($request->input('terapi_lain', []) as $namaBaru) {
                    $namaBaru = $this->normalizeNamaMaster($namaBaru);
                    if ($namaBaru === '') {
                        continue;
                    }
                    $obatBaru = Obat::firstOrCreate(['nama_obat' => $namaBaru]);
                    $terapiIds[] = $obatBaru->id_obat;
                }
            }
            if (!empty($terapiIds)) {
                $rekamMedis->obats()->attach(array_unique($terapiIds));
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data Rekam Medis berhasil disimpan!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Data Utama untuk Laporan
     */
    public function rekapLaporan(Request $request)
    {
        if (!$request->exists('year') && !$request->exists('tahun') && !$request->filled('start_date') && !$request->filled('end_date')) {
            $request->merge(['year' => now()->year]);
        }

        $dokters = Dokter::orderBy('nama_dokter')->get();
        $jenisHewans = JenisHewan::orderBy('nama_jenis')->get();
        $pelayanans = Pelayanan::with('jenisHewan')
            ->orderBy('nama_pelayanan')
            ->orderBy('id_jenis')
            ->orderBy('jenis_kelamin')
            ->get();
        $diagnosas = Diagnosa::orderBy('nama_diagnosa')->get();
        $anamnesas = Anamnesa::orderBy('nama_anamnesa')->get();

        $query = RekamMedis::with([
            'hewan.pemilik',
            'hewan.jenisHewan',
            'dokter',
            'paramedis',
            'pelayanan',
            'diagnosa',
            'anamnesas',
            'obats',
        ]);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('no_karcis', 'like', "%{$search}%")
                    ->orWhereHas('hewan', function ($sub) use ($search) {
                        $sub->where('nama_hewan', 'like', "%{$search}%")
                            ->orWhere('jenis_kelamin', 'like', "%{$search}%")
                            ->orWhereHas('pemilik', function ($sub2) use ($search) {
                                $sub2->where('nama_pemilik', 'like', "%{$search}%")
                                    ->orWhere('alamat', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('diagnosa', function ($sub) use ($search) {
                        $sub->where('nama_diagnosa', 'like', "%{$search}%");
                    })
                    ->orWhereHas('pelayanan', function ($sub) use ($search) {
                        $sub->where('nama_pelayanan', 'like', "%{$search}%");
                    })
                    ->orWhereHas('dokter', function ($sub) use ($search) {
                        $sub->where('nama_dokter', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('dokter')) {
            $query->where('id_dokter', $request->dokter);
        }

        if ($request->filled('jenis_hewan')) {
            $query->whereHas('hewan', function ($sub) use ($request) {
                $sub->where('id_jenis', $request->jenis_hewan);
            });
        }

        if ($request->filled('pelayanan')) {
            $query->where('id_pelayanan', $request->pelayanan);
        }

        if ($request->filled('diagnosa')) {
            $query->where('id_diagnosa', $request->diagnosa);
        }

        if ($request->filled('anamnesa')) {
            $query->whereHas('anamnesas', function ($sub) use ($request) {
                $sub->where('anamnesa.id_anamnesa', $request->anamnesa);
            });
        }

        if ($request->filled('jenis_kelamin')) {
            $query->whereHas('hewan', function ($sub) use ($request) {
                $sub->where('jenis_kelamin', $request->jenis_kelamin);
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        if ($request->filled('year')) {
            $query->whereYear('tanggal', $request->year);
        }

        $summaryData = $query->clone()->orderBy('tanggal', 'desc')->get();

        $rekapData = $query->orderBy('tanggal', 'desc')
            ->paginate(15)
            ->withQueryString();

        $minYear = RekamMedis::query()
            ->selectRaw('MIN(YEAR(tanggal)) as year')
            ->value('year');

        $minYear = $minYear ? (int) $minYear : now()->year;
        $years = range(now()->year, $minYear);

        $totalEntrySummary = $summaryData->count();
        $totalRetribusiSummary = $summaryData->sum(fn ($item) => $item->pelayanan?->tarif ?? 0);
        $totalHewanUnikSummary = $summaryData->pluck('id_hewan')->filter()->unique()->count();
        $dokterAktifSummary = $summaryData->pluck('id_dokter')->filter()->unique()->count();

        return view('data_master.rekap_laporan', compact(
            'rekapData',
            'dokters',
            'jenisHewans',
            'pelayanans',
            'diagnosas',
            'anamnesas',
            'years',
            'totalEntrySummary',
            'totalRetribusiSummary',
            'totalHewanUnikSummary',
            'dokterAktifSummary'
        ));
    }

    /**
     * Resolusi Data untuk Filter Export
     */
    private function resolveRekapLaporanViewData(Request $request): array
    {
        if (!$request->exists('year') && !$request->exists('tahun') && !$request->filled('start_date') && !$request->filled('end_date')) {
            $request->merge(['year' => now()->year]);
        }

        $search = $request->filled('search') ? $request->search : $request->q;
        $dokter = $request->filled('id_dokter') ? $request->id_dokter : $request->dokter;
        $jenisHewan = $request->filled('id_jenis') ? $request->id_jenis : $request->jenis_hewan;
        $pelayanan = $request->filled('id_pelayanan') ? $request->id_pelayanan : $request->pelayanan;
        $diagnosa = $request->filled('id_diagnosa') ? $request->id_diagnosa : $request->diagnosa;
        $jenisKelamin = $request->filled('jenis_kelamin') ? $request->jenis_kelamin : null;
        $tanggalMulai = $request->filled('tanggal_mulai') ? $request->tanggal_mulai : $request->start_date;
        $tanggalAkhir = $request->filled('tanggal_akhir') ? $request->tanggal_akhir : $request->end_date;
        $tahun = $request->filled('tahun') ? $request->tahun : $request->year;

        $query = RekamMedis::with([
            'hewan.pemilik',
            'hewan.jenisHewan',
            'dokter',
            'paramedis',
            'pelayanan',
            'diagnosa',
            'anamnesas',
            'obats',
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('no_karcis', 'like', "%{$search}%")
                    ->orWhereHas('hewan', fn($q2) => $q2->where('nama_hewan', 'like', "%{$search}%"))
                    ->orWhereHas('hewan.pemilik', fn($q2) => $q2->where('nama_pemilik', 'like', "%{$search}%"))
                    ->orWhereHas('diagnosa', fn($q2) => $q2->where('nama_diagnosa', 'like', "%{$search}%"));
            });
        }

        if ($dokter) {
            $query->where('id_dokter', $dokter);
        }

        if ($jenisHewan) {
            $query->whereHas('hewan', fn($q) => $q->where('id_jenis', $jenisHewan));
        }

        if ($pelayanan) {
            $query->where('id_pelayanan', $pelayanan);
        }

        if ($diagnosa) {
            $query->where('id_diagnosa', $diagnosa);
        }

        if ($jenisKelamin) {
            $query->whereHas('hewan', fn($q) => $q->where('jenis_kelamin', $jenisKelamin));
        }

        if ($tanggalMulai) {
            $query->whereDate('tanggal', '>=', $tanggalMulai);
        }

        if ($tanggalAkhir) {
            $query->whereDate('tanggal', '<=', $tanggalAkhir);
        }

        if ($tahun && !$tanggalMulai && !$tanggalAkhir) {
            $query->whereYear('tanggal', $tahun);
        }

        $rekapData = $query->orderBy('tanggal', 'desc')->get();

        $totalEntri = $rekapData->count();
        $totalRetribusi = $rekapData->sum(fn($item) => $item->pelayanan?->tarif ?? 0);
        $totalHewanUnik = $rekapData->pluck('id_hewan')->unique()->count();

        $filterInfo = [];

        if ($tanggalMulai || $tanggalAkhir) {
            $filterInfo['Periode'] =
                ($tanggalMulai ? \Carbon\Carbon::parse($tanggalMulai)->translatedFormat('d F Y') : 'Awal data')
                . ' s/d '
                . ($tanggalAkhir ? \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y') : 'sekarang');
        } elseif ($tahun) {
            $filterInfo['Tahun'] = $tahun;
        }

        if ($search) {
            $filterInfo['Kata Kunci'] = $search;
        }
        if ($dokter) {
            $filterInfo['Dokter'] = Dokter::find($dokter)?->nama_dokter ?? '-';
        }
        if ($jenisHewan) {
            $filterInfo['Jenis Hewan'] = JenisHewan::find($jenisHewan)?->nama_jenis ?? '-';
        }
        if ($pelayanan) {
            $filterInfo['Pelayanan'] = Pelayanan::find($pelayanan)?->nama_pelayanan ?? '-';
        }
        if ($diagnosa) {
            $filterInfo['Diagnosa'] = Diagnosa::find($diagnosa)?->nama_diagnosa ?? '-';
        }
        if ($jenisKelamin) {
            $filterInfo['Jenis Kelamin'] = $jenisKelamin;
        }

        return compact('rekapData', 'filterInfo', 'totalEntri', 'totalRetribusi', 'totalHewanUnik');
    }

    /**
     * Group of Export Methods
     */
    public function exportRekapLaporan(Request $request)
    {
        return Excel::download(new RekapLaporanExport($request->all()), 'laporan-rekam_medis-' . now()->format('Ymd_His') . '.xlsx');
    }

    public function exportRekapLaporanView(Request $request)
    {
        $exportData = $this->resolveRekapLaporanViewData($request);

        return Excel::download(
            new RekapLaporanExport2(
                $exportData['rekapData'],
                $exportData['filterInfo'],
                $exportData['totalEntri'],
                $exportData['totalRetribusi'],
                $exportData['totalHewanUnik']
            ),
            'rekapitulasi-rekam_medis-' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    // Halaman tampilan Rekapitulasi (tabel bulanan) di browser, terpisah dari halaman Rekap Laporan (list mentah)
    public function rekapitulasi(Request $request)
    {
        $exportData = $this->resolveRekapLaporanViewData($request);

        $export = new RekapLaporanExport2(
            $exportData['rekapData'],
            $exportData['filterInfo'],
            $exportData['totalEntri'],
            $exportData['totalRetribusi'],
            $exportData['totalHewanUnik']
        );

        $minYear = RekamMedis::query()
            ->selectRaw('MIN(YEAR(tanggal)) as year')
            ->value('year');
        $minYear = $minYear ? (int) $minYear : now()->year;
        $years = range(now()->year, $minYear);

        return view('data_master.rekapitulasi', [
            'summaryRows' => $export->getSummaryRows(),
            'filterInfo' => $exportData['filterInfo'],
            'totalEntri' => $exportData['totalEntri'],
            'totalRetribusi' => $exportData['totalRetribusi'],
            'totalHewanUnik' => $exportData['totalHewanUnik'],
            'years' => $years,
            'penandatangans' => Penandatangan::orderBy('nama')->get(),
        ]);
    }
    
    public function cetakRekapLaporan(Request $request)
    {
        $exportData = $this->resolveRekapLaporanViewData($request);

        $pdf = Pdf::setOptions(['isPhpEnabled' => true])
            ->loadView('export.pdf_rekap_laporan', [
                'rekapData' => $exportData['rekapData'],
                'totalEntri' => $exportData['totalEntri'],
                'totalRetribusi' => $exportData['totalRetribusi'],
                'totalHewanUnik' => $exportData['totalHewanUnik'],
                'filterInfo' => $exportData['filterInfo'],
            ])
            ->setPaper('a4', 'landscape');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="laporan-rekam_medis-' . now()->format('Ymd_His') . '.pdf"',
        ]);
    }

    public function cetakRekapLaporan2(Request $request)
    {
        $exportData = $this->resolveRekapLaporanViewData($request);

        $export = new RekapLaporanExport2(
            $exportData['rekapData'],
            $exportData['filterInfo'],
            $exportData['totalEntri'],
            $exportData['totalRetribusi'],
            $exportData['totalHewanUnik']
        );

        $pdf = Pdf::setOptions(['isPhpEnabled' => true])
            ->loadView('export.pdf_rekap_laporan2', [
                'summaryRows' => $export->getSummaryRows(),
                'filterInfo' => $exportData['filterInfo'],
            ])
            ->setPaper('a4', 'landscape');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="rekapitulasi-rekam_medis-' . now()->format('Ymd_His') . '.pdf"',
        ]);
    }
}