<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Setoran Retribusi</title>
    <style>
        @page {
            margin: 15px 20px;
            /* DomPDF baca orientation dari sini kalau dipanggil ->setPaper('a4','landscape') di controller */
        }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 8px;
            color: #000000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            border: 1px solid #000000;
            padding: 3px 4px;
        }
        .no-border {
            border: none;
        }
        .header-table {
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .kop-instansi {
            font-family: 'Times New Roman', Times, serif;
            text-align: center;
        }
    </style>
</head>
<body>

@php
    $logoPath = public_path('img/logo kota kediri.png');
    $logoData = is_file($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    $selectedYear = request('year') ?: request('tahun');
    $reportYear = $selectedYear
        ?: (request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->year : (request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->year : date('Y')));
@endphp
<table class="header-table">
    <tr>
        <td width="15%" style="text-align:center;">
            @if($logoData)
                <img src="data:image/png;base64,{{ $logoData }}" width="80" alt="Logo Kota Kediri">
            @endif
        </td>
        <td class="kop-instansi">
            <div style="font-size:15px; letter-spacing:0.5px;">PEMERINTAH KOTA KEDIRI</div>
            <div style="font-size:21px; font-weight:bold; margin-top:2px;">DINAS KETAHANAN PANGAN DAN PERTANIAN</div>
            <div style="font-size:11px; margin-top:4px;">Jl. Brigjend. Pol. Imam Bachri H.P No.98A Kota Kediri</div>
            <div style="font-size:11px; margin-top:1px;">surel : dkppkotakediri@kedirikota.go.id &nbsp;&nbsp; laman : dkpp.kedirikota.go.id</div>
        </td>
    </tr>
</table>
<div style="border-top:3px solid black; margin-top:8px;"></div>
<div style="border-top:1px solid black; margin-top:1px; margin-bottom:6px;"></div>

<table>
    <tr>
        <td colspan="20" class="no-border" style="font-weight:bold; font-size:13px; text-align:center;">REKAPITULASI SETORAN RETRIBUSI TAHUN {{ $reportYear }}</td>
    </tr>
    <tr>
        <td colspan="20" class="no-border" style="font-weight:bold; text-align:center;">PERIODE {{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('d F Y') : '01 JANUARI' }} SAMPAI DENGAN {{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('d F Y') : '31 DESEMBER' }}</td>
    </tr>
    <tr>
        <td colspan="20" class="no-border"></td>
    </tr>
    <tr>
        <td colspan="20" class="no-border" style="font-weight:bold;">Filter :</td>
    </tr>
    @if(count($filterInfo) > 0)
        @foreach($filterInfo as $label => $value)
            <tr>
                <td class="no-border" style="font-weight:bold;">{{ $label }}</td>
                <td colspan="19" class="no-border">{{ $value }}</td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="20" class="no-border">Tidak ada filter (menampilkan seluruh data)</td>
        </tr>
    @endif
    <tr>
        <td colspan="20" class="no-border"></td>
    </tr>

    {{-- ====== HEADER TABEL ====== --}}
    <tr style="font-weight:bold; text-align:center; background:#FFD966;">
        <td rowspan="3" style="background:#FFD966;">No</td>
        <td rowspan="3" style="background:#FFD966;">Bulan</td>
        <td colspan="6" style="background:#FFD966;">Non Operatif</td>
        <td colspan="6" style="background:#FFD966;">Operatif</td>
        <td colspan="3" style="background:#FFD966;">Lain-lain</td>
        <td rowspan="3" style="background:#FFD966;">Total Pasien</td>
        <td rowspan="3" style="background:#FFD966;">Total Retribusi (Rp)</td>
    </tr>
    <tr style="font-weight:bold; text-align:center; background:#FFD966;">
        <td colspan="3" style="background:#FFD966;">Pemeriksaan Umum</td>
        <td colspan="3" style="background:#FFD966;">Vaksinasi</td>
        <td colspan="3" style="background:#FFD966;">Operasi Kecil</td>
        <td colspan="3" style="background:#FFD966;">Operasi Besar</td>
        <td colspan="3" style="background:#FFD966;"></td>
    </tr>
    <tr style="font-weight:bold; text-align:center; background:#FFD966;">
        <td style="background:#FFD966;">Kucing</td>
        <td style="background:#FFD966;">Anjing</td>
        <td style="background:#FFD966;">Unggas/Kelinci</td>
        <td style="background:#FFD966;">Kucing</td>
        <td style="background:#FFD966;">Anjing</td>
        <td style="background:#FFD966;">Unggas/Kelinci</td>
        <td style="background:#FFD966;">Kucing</td>
        <td style="background:#FFD966;">Anjing</td>
        <td style="background:#FFD966;">Unggas/Kelinci</td>
        <td style="background:#FFD966;">Kucing</td>
        <td style="background:#FFD966;">Anjing</td>
        <td style="background:#FFD966;">Unggas/Kelinci</td>
        <td style="background:#FFD966;">Kucing</td>
        <td style="background:#FFD966;">Anjing</td>
        <td style="background:#FFD966;">Unggas/Kelinci</td>
    </tr>

    {{-- ====== ISI DATA ====== --}}
    @foreach($summaryRows as $index => $row)
        @php
            $isTotal = $row['month'] === 'TOTAL';
            $rowBg = $isTotal ? '#BFBFBF' : ($index % 2 === 0 ? '#DCE6F1' : '#FFFFFF');
        @endphp
        <tr style="{{ $isTotal ? 'font-weight:bold;' : '' }} text-align:center; background:{{ $rowBg }};">
            <td style="background:{{ $rowBg }};">{{ $isTotal ? '' : $index + 1 }}</td>
            <td style="background:{{ $rowBg }}; text-align:left;">{{ $row['month'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['pemeriksaan_umum']['kucing'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['pemeriksaan_umum']['anjing'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['pemeriksaan_umum']['unggas_kelinci'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['vaksinasi']['kucing'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['vaksinasi']['anjing'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['vaksinasi']['unggas_kelinci'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['operasi_kecil']['kucing'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['operasi_kecil']['anjing'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['operasi_kecil']['unggas_kelinci'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['operasi_besar']['kucing'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['operasi_besar']['anjing'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['operasi_besar']['unggas_kelinci'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['lain_lain']['kucing'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['lain_lain']['anjing'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['lain_lain']['unggas_kelinci'] }}</td>
            <td style="background:{{ $rowBg }};">{{ $row['total_pasien'] }}</td>
            <td style="background:{{ $rowBg }};">{{ number_format($row['total_retribusi'], 0, ',', '.') }}</td>
        </tr>
    @endforeach

    <tr>
        <td colspan="19" class="no-border"></td>
    </tr>
    <tr>
        <td colspan="19" class="no-border"></td>
    </tr>

    {{-- ====== TANDA TANGAN ====== --}}
    @php
        $ttd1Nama = request('ttd1_nama');
        $ttd1Jabatan = request('ttd1_jabatan');
        $ttd1Nip = request('ttd1_nip');

        $ttd2Nama = request('ttd2_nama');
        $ttd2Jabatan = request('ttd2_jabatan');
        $ttd2Nip = request('ttd2_nip');
    @endphp
    <tr>
        <td colspan="9" class="no-border" style="text-align:center;">Mengetahui,</td>
        <td colspan="1" class="no-border"></td>
        <td colspan="9" class="no-border" style="text-align:center;">Kediri, {{ now()->format('d F Y') }}</td>
    </tr>
    @if($ttd1Jabatan || $ttd2Jabatan)
        <tr>
            <td colspan="9" class="no-border" style="text-align:center;">{{ $ttd1Jabatan ?: 'Kepala Dinas Ketahanan Pangan dan Pertanian' }}</td>
            <td colspan="1" class="no-border"></td>
            <td colspan="9" class="no-border" style="text-align:center;">{{ $ttd2Jabatan ?: 'Kepala UPT Pusat Kesehatan Hewan' }}</td>
        </tr>
        <tr>
            <td colspan="9" class="no-border" style="text-align:center;"></td>
            <td colspan="1" class="no-border"></td>
            <td colspan="9" class="no-border" style="text-align:center;"></td>
        </tr>
    @else
        <tr>
            <td colspan="9" class="no-border" style="text-align:center;">Kepala Dinas Ketahanan Pangan dan Pertanian</td>
            <td colspan="1" class="no-border"></td>
            <td colspan="9" class="no-border" style="text-align:center;">Kepala UPT Pusat Kesehatan Hewan</td>
        </tr>
        <tr>
            <td colspan="9" class="no-border" style="text-align:center;">Kota Kediri</td>
            <td colspan="1" class="no-border"></td>
            <td colspan="9" class="no-border" style="text-align:center;">Dinas Ketahanan Pangan dan Pertanian</td>
        </tr>
        <tr>
            <td colspan="9" class="no-border" style="text-align:center;"></td>
            <td colspan="1" class="no-border"></td>
            <td colspan="9" class="no-border" style="text-align:center;">Kota Kediri</td>
        </tr>
    @endif
    <tr><td colspan="19" class="no-border"></td></tr>
    <tr><td colspan="19" class="no-border"></td></tr>
    <tr><td colspan="19" class="no-border"></td></tr>
    <tr><td colspan="19" class="no-border"></td></tr>

    <tr>
        <td colspan="9" class="no-border" style="text-align:center; font-weight:bold; text-decoration:underline;">{{ $ttd1Nama ?: 'NamaNanti1' }}</td>
        <td colspan="1" class="no-border"></td>
        <td colspan="9" class="no-border" style="text-align:center; font-weight:bold; text-decoration:underline;">{{ $ttd2Nama ?: 'NamaNanti2' }}</td>
    </tr>
    <tr>
        <td colspan="9" class="no-border" style="text-align:center;">NIP. {{ $ttd1Nip ?: '-' }}</td>
        <td colspan="1" class="no-border"></td>
        <td colspan="9" class="no-border" style="text-align:center;">NIP. {{ $ttd2Nip ?: '-' }}</td>
    </tr>
</table>

</body>
</html>