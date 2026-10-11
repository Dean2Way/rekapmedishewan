<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;
use App\Models\RekamMedis;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class RekapLaporanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
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

        $this->applyFilters($query, $this->filters);

        return $query->orderBy('tanggal', 'desc')->get();
    }

    public function map($item): array
    {
        $tanggal = \Carbon\Carbon::parse($item->tanggal);
        if ($tanggal->format('H:i:s') === '00:00:00' && $item->created_at) {
            $tanggal = $tanggal->setTime($item->created_at->hour, $item->created_at->minute, $item->created_at->second);
        }

        return [
            $tanggal->translatedFormat('d/m/Y H:i:s'),
            $item->hewan?->pemilik?->nama_pemilik ?? '-',
            $item->hewan?->pemilik?->alamat ?? '-',
            $item->hewan?->nama_hewan ?? '-',
            $item->hewan?->jenisHewan?->nama_jenis ?? '-',
            $item->hewan?->jenis_kelamin ?? '-',
            $item->anamnesas->pluck('nama_anamnesa')->implode(', ') ?: '-',
            $item->diagnosa?->nama_diagnosa ?? '-',
            $item->pelayanan?->nama_pelayanan ?? '-',
            $item->dokter?->nama_dokter ?? '-',
            $item->paramedis?->nama_paramedis ?? '-',
            $item->obats->pluck('nama_obat')->implode(', ') ?: '-',
            $item->no_karcis ?? '-',
            $item->pelayanan?->tarif ?? 0,
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Pemilik',
            'Alamat',
            'Nama Hewan',
            'Jenis Hewan',
            'Kelamin',
            'Anamnesa',
            'Diagnosa',
            'Pelayanan',
            'Dokter',
            'Paramedis',
            'Terapi',
            'No. Karcis',
            'Retribusi',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 7);

                $sheet->mergeCells('B1:N1');
                $sheet->mergeCells('B2:N2');
                $sheet->mergeCells('B3:N3');
                $sheet->mergeCells('B4:N4');
                $sheet->mergeCells('A6:N6');

                $sheet->setCellValue('B1', 'PEMERINTAH KOTA KEDIRI');
                $sheet->setCellValue('B2', 'DINAS KETAHANAN PANGAN DAN PERTANIAN');
                $sheet->setCellValue('B3', 'Jl. Brigjend. Pol. Imam Bachri H.P No.98A Kota Kediri');
                $sheet->setCellValue('B4', 'surel : dkppkotakediri@kedirikota.go.id    laman : dkpp.kedirikota.go.id');
                $sheet->setCellValue('A6', 'LAPORAN REKAP MEDIS');

                $sheet->getStyle('B1:B4')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('B1')->getFont()->setName('Times New Roman')->setSize(12);
                $sheet->getStyle('B2')->getFont()->setName('Times New Roman')->setSize(16)->setBold(true);
                $sheet->getStyle('B3:B4')->getFont()->setName('Times New Roman')->setSize(10);
                $sheet->getStyle('A6')->getFont()->setName('Times New Roman')->setSize(12)->setBold(true);
                $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');

                foreach ([1 => 20, 2 => 26, 3 => 19, 4 => 18, 5 => 8, 6 => 22, 7 => 3] as $row => $height) {
                    $sheet->getRowDimension($row)->setRowHeight($height);
                }

                $sheet->getStyle('A7:N7')->getBorders()->getBottom()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                $sheet->getStyle('A8:N8')->getBorders()->getTop()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $logoPath = public_path('img/logo kota kediri.png');
                if (is_file($logoPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo Kota Kediri');
                    $drawing->setDescription('Logo Kota Kediri');
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(90);
                    $drawing->setCoordinates('A1');
                    $drawing->setOffsetX(12);
                    $drawing->setOffsetY(4);
                    $drawing->setWorksheet($sheet);
                    $sheet->getColumnDimension('A')->setWidth(18);
                }
            },
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (!empty($filters['q'])) {
            $search = $filters['q'];
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

        if (!empty($filters['dokter'])) {
            $query->where('id_dokter', $filters['dokter']);
        }

        if (!empty($filters['jenis_hewan'])) {
            $query->whereHas('hewan', function ($sub) use ($filters) {
                $sub->where('id_jenis', $filters['jenis_hewan']);
            });
        }

        if (!empty($filters['pelayanan'])) {
            $query->where('id_pelayanan', $filters['pelayanan']);
        }

        if (!empty($filters['diagnosa'])) {
            $query->where('id_diagnosa', $filters['diagnosa']);
        }

        if (!empty($filters['anamnesa'])) {
            $query->whereHas('anamnesas', function ($sub) use ($filters) {
                $sub->where('anamnesa.id_anamnesa', $filters['anamnesa']);
            });
        }

        if (!empty($filters['jenis_kelamin'])) {
            $query->whereHas('hewan', function ($sub) use ($filters) {
                $sub->where('jenis_kelamin', $filters['jenis_kelamin']);
            });
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('tanggal', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('tanggal', '<=', $filters['end_date']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('tanggal', $filters['year']);
        }
    }
}
