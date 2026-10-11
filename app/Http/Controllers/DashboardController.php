<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\Hewan;
use App\Models\RekamMedis;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'kepala_dinas') {
            $bulanIni = Carbon::now()->startOfMonth();
            $bulanDepan = $bulanIni->copy()->addMonth();
            $bulanLalu = $bulanIni->copy()->subMonth();
            $mulai = $bulanIni->copy()->subMonths(11);
            $labels = [];
            $jumlahPemeriksaan = [];

            for ($i = 0; $i < 12; $i++) {
                $bulan = $mulai->copy()->addMonths($i);
                $bulanBerikutnya = $bulan->copy()->addMonth();

                $labels[] = $bulan->locale('id')->translatedFormat('M Y');
                $jumlahPemeriksaan[] = RekamMedis::where('tanggal', '>=', $bulan)
                    ->where('tanggal', '<', $bulanBerikutnya)
                    ->count();
            }

            $pemeriksaanBulanIni = RekamMedis::where('tanggal', '>=', $bulanIni)
                ->where('tanggal', '<', $bulanDepan)
                ->count();
            $pemeriksaanBulanLalu = RekamMedis::where('tanggal', '>=', $bulanLalu)
                ->where('tanggal', '<', $bulanIni)
                ->count();
            $hewanUnikBulanIni = RekamMedis::where('tanggal', '>=', $bulanIni)
                ->where('tanggal', '<', $bulanDepan)
                ->distinct()
                ->count('id_hewan');
            $retribusiBulanIni = RekamMedis::query()
                ->join('pelayanan as p', 'rekam_medis.id_pelayanan', '=', 'p.id_pelayanan')
                ->where('rekam_medis.tanggal', '>=', $bulanIni)
                ->where('rekam_medis.tanggal', '<', $bulanDepan)
                ->sum('p.tarif');

            $kasusBulanIni = RekamMedis::query()
                ->join('diagnosa as d', 'rekam_medis.id_diagnosa', '=', 'd.id_diagnosa')
                ->where('rekam_medis.tanggal', '>=', $bulanIni)
                ->where('rekam_medis.tanggal', '<', $bulanDepan)
                ->whereNotNull('rekam_medis.id_diagnosa')
                ->where('d.nama_diagnosa', '!=', 'Sehat');

            $jumlahKasusBulanIni = (clone $kasusBulanIni)->count();
            $diagnosaTerbanyak = (clone $kasusBulanIni)
                ->select('d.nama_diagnosa as nama', DB::raw('COUNT(*) as jumlah'))
                ->groupBy('d.nama_diagnosa')
                ->orderByDesc('jumlah')
                ->first();
            $wilayahKasusTerbanyak = (clone $kasusBulanIni)
                ->join('hewan as h', 'rekam_medis.id_hewan', '=', 'h.id_hewan')
                ->join('pemilik as p', 'h.id_pemilik', '=', 'p.id_pemilik')
                ->select('p.alamat as nama', DB::raw('COUNT(*) as jumlah'))
                ->groupBy('p.alamat')
                ->orderByDesc('jumlah')
                ->first();

            $jenisHewanBulanIni = RekamMedis::query()
                ->join('hewan as h', 'rekam_medis.id_hewan', '=', 'h.id_hewan')
                ->join('jenis_hewan as jh', 'h.id_jenis', '=', 'jh.id_jenis')
                ->where('rekam_medis.tanggal', '>=', $bulanIni)
                ->where('rekam_medis.tanggal', '<', $bulanDepan)
                ->select('jh.nama_jenis as nama', DB::raw('COUNT(*) as jumlah'))
                ->groupBy('jh.nama_jenis')
                ->orderByDesc('jumlah')
                ->get();

            $jenisHewanChart = $jenisHewanBulanIni->take(5);
            $labelJenisHewan = $jenisHewanChart->pluck('nama')->values();
            $dataJenisHewan = $jenisHewanChart->pluck('jumlah')->map(fn ($jumlah) => (int) $jumlah)->values();
            $sisaJenisHewan = $jenisHewanBulanIni->slice(5)->sum('jumlah');

            if ($sisaJenisHewan > 0) {
                $labelJenisHewan->push('Lainnya');
                $dataJenisHewan->push((int) $sisaJenisHewan);
            }

            if ($pemeriksaanBulanLalu === 0) {
                $perbandinganPemeriksaan = $pemeriksaanBulanIni > 0
                    ? 'Bulan lalu belum ada pemeriksaan'
                    : 'Sama dengan bulan lalu';
            } elseif ($pemeriksaanBulanIni === $pemeriksaanBulanLalu) {
                $perbandinganPemeriksaan = 'Sama dengan bulan lalu';
            } else {
                $arah = $pemeriksaanBulanIni > $pemeriksaanBulanLalu ? 'Naik' : 'Turun';
                $persentase = abs(($pemeriksaanBulanIni - $pemeriksaanBulanLalu) / $pemeriksaanBulanLalu * 100);
                $perbandinganPemeriksaan = $arah . ' ' . number_format($persentase, 1, ',', '.') . '% dari bulan lalu';
            }

            return view('dashboard_kepala_dinas', [
                'labels' => $labels,
                'jumlahPemeriksaan' => $jumlahPemeriksaan,
                'totalPemeriksaan' => array_sum($jumlahPemeriksaan),
                'pemeriksaanBulanIni' => $pemeriksaanBulanIni,
                'perbandinganPemeriksaan' => $perbandinganPemeriksaan,
                'hewanUnikBulanIni' => $hewanUnikBulanIni,
                'retribusiBulanIni' => $retribusiBulanIni,
                'jumlahKasusBulanIni' => $jumlahKasusBulanIni,
                'diagnosaTerbanyak' => $diagnosaTerbanyak,
                'wilayahKasusTerbanyak' => $wilayahKasusTerbanyak,
                'jenisHewanBulanIni' => $jenisHewanBulanIni,
                'labelJenisHewan' => $labelJenisHewan,
                'dataJenisHewan' => $dataJenisHewan,
            ]);
        }

        $totalDokter = Dokter::count();
        $totalRegistrasiSatwa = Hewan::count();
        $totalKunjungan = RekamMedis::count();
        $kunjunganHariIni = RekamMedis::whereDate('tanggal', Carbon::today())->count();

        $totalRetribusiHariIni = RekamMedis::whereDate('tanggal', Carbon::today())
            ->with('pelayanan')
            ->get()
            ->sum(fn ($item) => $item->pelayanan?->tarif ?? 0);

        return view('dashboard', compact(
            'totalDokter',
            'totalRegistrasiSatwa',
            'totalKunjungan',
            'kunjunganHariIni',
            'totalRetribusiHariIni'
        ));
    }
}