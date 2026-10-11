<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapLaporanExport2 implements FromView, ShouldAutoSize, WithStyles, WithEvents
{
    protected Collection $rekapData;
    protected array $filterInfo;
    protected int $totalEntri;
    protected float $totalRetribusi;
    protected int $totalHewanUnik;
    protected array $summaryRows;

    public function __construct(Collection $rekapData, array $filterInfo, int $totalEntri, float $totalRetribusi, int $totalHewanUnik)
    {
        $this->rekapData = $rekapData;
        $this->filterInfo = $filterInfo;
        $this->totalEntri = $totalEntri;
        $this->totalRetribusi = $totalRetribusi;
        $this->totalHewanUnik = $totalHewanUnik;
        $this->summaryRows = $this->buildSummaryRows();
    }

    public function view(): View
    {
        return view('export.excel_rekap_laporan', [
            'summaryRows' => $this->summaryRows,
            'filterInfo' => $this->filterInfo,
            'totalEntri' => $this->totalEntri,
            'totalRetribusi' => $this->totalRetribusi,
            'totalHewanUnik' => $this->totalHewanUnik,
            'logoPath' => public_path('img/logo kota kediri.png'),
        ]);
    }

    public function buildSummaryRows(): array
    {
        $months = [
            1 => 'JANUARI',
            2 => 'FEBRUARI',
            3 => 'MARET',
            4 => 'APRIL',
            5 => 'MEI',
            6 => 'JUNI',
            7 => 'JULI',
            8 => 'AGUSTUS',
            9 => 'SEPTEMBER',
            10 => 'OKTOBER',
            11 => 'NOVEMBER',
            12 => 'DESEMBER',
        ];

        $rows = [];
        foreach ($months as $number => $name) {
            $rows[$number] = [
                'month' => $name,
                'pemeriksaan_umum' => ['kucing' => 0, 'anjing' => 0, 'unggas_kelinci' => 0],
                'vaksinasi' => ['kucing' => 0, 'anjing' => 0, 'unggas_kelinci' => 0],
                'operasi_kecil' => ['kucing' => 0, 'anjing' => 0, 'unggas_kelinci' => 0],
                'operasi_besar' => ['kucing' => 0, 'anjing' => 0, 'unggas_kelinci' => 0],
                'lain_lain' => ['kucing' => 0, 'anjing' => 0, 'unggas_kelinci' => 0],
                'total_pasien' => 0,
                'total_retribusi' => 0,
            ];
        }

        foreach ($this->rekapData as $item) {
            $tanggal = Carbon::parse($item->tanggal);
            $month = $tanggal->month;
            $category = $this->mapCategory($item->pelayanan?->nama_pelayanan ?? '');
            $animalGroup = $this->mapAnimalGroup($item->hewan?->jenisHewan?->nama_jenis ?? $item->hewan?->jenis_kelamin ?? '');

            $rows[$month][$category][$animalGroup]++;
            $rows[$month]['total_pasien']++;
            $rows[$month]['total_retribusi'] += $item->pelayanan?->tarif ?? 0;
        }

        $rows[0] = [
            'month' => 'TOTAL',
            'pemeriksaan_umum' => ['kucing' => 0, 'anjing' => 0, 'unggas_kelinci' => 0],
            'vaksinasi' => ['kucing' => 0, 'anjing' => 0, 'unggas_kelinci' => 0],
            'operasi_kecil' => ['kucing' => 0, 'anjing' => 0, 'unggas_kelinci' => 0],
            'operasi_besar' => ['kucing' => 0, 'anjing' => 0, 'unggas_kelinci' => 0],
            'lain_lain' => ['kucing' => 0, 'anjing' => 0, 'unggas_kelinci' => 0],
            'total_pasien' => 0,
            'total_retribusi' => 0,
        ];

        foreach ($rows as $key => $row) {
            if ($key === 0) {
                continue;
            }

            foreach (['pemeriksaan_umum', 'vaksinasi', 'operasi_kecil', 'operasi_besar', 'lain_lain'] as $group) {
                foreach (['kucing', 'anjing', 'unggas_kelinci'] as $type) {
                    $rows[0][$group][$type] += $row[$group][$type];
                }
            }
            $rows[0]['total_pasien'] += $row['total_pasien'];
            $rows[0]['total_retribusi'] += $row['total_retribusi'];
        }

        return array_values($rows);
    }

    public function getSummaryRows(): array
    {
        return $this->summaryRows;
    }

    protected function mapCategory(string $name): string
    {
        $key = strtolower(trim($name));

        if (str_contains($key, 'pemeriksaan')) {
            return 'pemeriksaan_umum';
        }

        if (str_contains($key, 'vaksin')) {
            return 'vaksinasi';
        }

        if (str_contains($key, 'operasi kecil') || str_contains($key, 'kecil')) {
            return 'operasi_kecil';
        }

        if (str_contains($key, 'operasi besar') || str_contains($key, 'besar')) {
            return 'operasi_besar';
        }

        return 'lain_lain';
    }

    protected function mapAnimalGroup(string $type): string
    {
        $key = strtolower(trim($type));

        if (str_contains($key, 'kucing')) {
            return 'kucing';
        }

        if (str_contains($key, 'anjing')) {
            return 'anjing';
        }

        if (str_contains($key, 'unggas') || str_contains($key, 'kelinci')) {
            return 'unggas_kelinci';
        }

        return 'lain_lain';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            9 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $logoPath = public_path('img/logo kota kediri.png');

                foreach ([1 => 20, 2 => 26, 3 => 19, 4 => 18] as $row => $height) {
                    $sheet->getRowDimension($row)->setRowHeight($height);
                }

                if (is_file($logoPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo Kota Kediri');
                    $drawing->setDescription('Logo Kota Kediri');
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(80);
                    $drawing->setCoordinates('A1');
                    $drawing->setOffsetX(12);
                    $drawing->setOffsetY(4);
                    $drawing->setWorksheet($sheet);
                }
            },
        ];
    }
}
