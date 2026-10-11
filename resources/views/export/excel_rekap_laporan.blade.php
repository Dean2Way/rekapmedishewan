@php
    $selectedYear = request('year') ?: request('tahun');
    $reportYear = $selectedYear
        ?: (request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->year : (request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->year : date('Y')));
@endphp
<table style="border-collapse: collapse; font-family: Calibri, Arial, sans-serif; font-size: 11px;">
    <tr>
        <td colspan="19" style="font-family:'Times New Roman',serif; font-size:12px; text-align:center; border:none;">PEMERINTAH KOTA KEDIRI</td>
    </tr>
    <tr>
        <td colspan="19" style="font-family:'Times New Roman',serif; font-size:16px; font-weight:bold; text-align:center; border:none;">DINAS KETAHANAN PANGAN DAN PERTANIAN</td>
    </tr>
    <tr>
        <td colspan="19" style="font-family:'Times New Roman',serif; font-size:10px; text-align:center; border:none;">Jl. Brigjend. Pol. Imam Bachri H.P No.98A Kota Kediri</td>
    </tr>
    <tr>
        <td colspan="19" style="font-family:'Times New Roman',serif; font-size:10px; text-align:center; border:none;">surel : dkppkotakediri@kedirikota.go.id &nbsp;&nbsp; laman : dkpp.kedirikota.go.id</td>
    </tr>
    <tr>
        <td colspan="19" style="border-bottom:3px solid #000000;"></td>
    </tr>
    <tr>
        <td colspan="19" style="border-top:1px solid #000000;"></td>
    </tr>
    <tr>
        <td colspan="19" style="font-weight:bold; font-size:14px; text-align:center;">LAPORAN REKAP MEDIS</td>
    </tr>
    <tr>
        <td colspan="19"></td>
    </tr>
    <tr>
        <td colspan="19" style="font-weight:bold; font-size:16px; text-align:center;">REKAPITULASI SETORAN RETRIBUSI TAHUN {{ $reportYear }}</td>
    </tr>
    <tr>
        <td colspan="19" style="font-weight:bold; text-align:center;">PERIODE {{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('d F Y') : '01 JANUARI' }} SAMPAI DENGAN {{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('d F Y') : '31 DESEMBER' }}</td>
    </tr>
    <tr>
        <td colspan="19" style="font-weight:bold; text-align:center;">UPT PUSAT KESEHATAN HEWAN</td>
    </tr>
    <tr>
        <td colspan="19" style="font-weight:bold; text-align:center;">DINAS KETAHANAN PANGAN DAN PERTANIAN KOTA KEDIRI</td>
    </tr>
    <tr>
        <td colspan="19"></td>
    </tr>
    <tr>
        <td colspan="19" style="font-weight:bold;">Filter :</td>
    </tr>
    @if(count($filterInfo) > 0)
        @foreach($filterInfo as $label => $value)
            <tr>
                <td style="font-weight:bold;">{{ $label }}</td>
                <td colspan="18">{{ $value }}</td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="19">Tidak ada filter (menampilkan seluruh data)</td>
        </tr>
    @endif
    <tr>
        <td colspan="19"></td>
    </tr>

    {{-- ====== HEADER TABEL ====== --}}
    <tr style="font-weight:bold; text-align:center; vertical-align:middle; background:#FFD966; border:1px solid #000000;">
        <td rowspan="3" style="border:1px solid #000000; background:#FFD966;">No</td>
        <td rowspan="3" style="border:1px solid #000000; background:#FFD966;">Bulan</td>
        <td colspan="6" style="border:1px solid #000000; background:#FFD966;">Non Operatif</td>
        <td colspan="6" style="border:1px solid #000000; background:#FFD966;">Operatif</td>
        <td colspan="3" style="border:1px solid #000000; background:#FFD966;">Lain-lain</td>
        <td rowspan="3" style="border:1px solid #000000; background:#FFD966;">Total Pasien</td>
        <td rowspan="3" style="border:1px solid #000000; background:#FFD966;">Total Retribusi (Rp)</td>
    </tr>
    <tr style="font-weight:bold; text-align:center; vertical-align:middle; background:#FFD966;">
        <td colspan="3" style="border:1px solid #000000; background:#FFD966;">Pemeriksaan Umum</td>
        <td colspan="3" style="border:1px solid #000000; background:#FFD966;">Vaksinasi</td>
        <td colspan="3" style="border:1px solid #000000; background:#FFD966;">Operasi Kecil</td>
        <td colspan="3" style="border:1px solid #000000; background:#FFD966;">Operasi Besar</td>
        <td colspan="3" style="border:1px solid #000000; background:#FFD966;"></td>
    </tr>
    <tr style="font-weight:bold; text-align:center; vertical-align:middle; background:#FFD966;">
        <td style="border:1px solid #000000; background:#FFD966;">Kucing</td>
        <td style="border:1px solid #000000; background:#FFD966;">Anjing</td>
        <td style="border:1px solid #000000; background:#FFD966;">Unggas/Kelinci</td>
        <td style="border:1px solid #000000; background:#FFD966;">Kucing</td>
        <td style="border:1px solid #000000; background:#FFD966;">Anjing</td>
        <td style="border:1px solid #000000; background:#FFD966;">Unggas/Kelinci</td>
        <td style="border:1px solid #000000; background:#FFD966;">Kucing</td>
        <td style="border:1px solid #000000; background:#FFD966;">Anjing</td>
        <td style="border:1px solid #000000; background:#FFD966;">Unggas/Kelinci</td>
        <td style="border:1px solid #000000; background:#FFD966;">Kucing</td>
        <td style="border:1px solid #000000; background:#FFD966;">Anjing</td>
        <td style="border:1px solid #000000; background:#FFD966;">Unggas/Kelinci</td>
        <td style="border:1px solid #000000; background:#FFD966;">Kucing</td>
        <td style="border:1px solid #000000; background:#FFD966;">Anjing</td>
        <td style="border:1px solid #000000; background:#FFD966;">Unggas/Kelinci</td>
    </tr>

    {{-- ====== ISI DATA ====== --}}
    @foreach($summaryRows as $index => $row)
        @php
            $isTotal = $row['month'] === 'TOTAL';
            $rowBg = $isTotal ? '#BFBFBF' : ($index % 2 === 0 ? '#DCE6F1' : '#FFFFFF');
        @endphp
        <tr style="{{ $isTotal ? 'font-weight:bold;' : '' }} text-align:center; background:{{ $rowBg }};">
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $isTotal ? '' : $index + 1 }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }}; text-align:left;">{{ $row['month'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['pemeriksaan_umum']['kucing'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['pemeriksaan_umum']['anjing'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['pemeriksaan_umum']['unggas_kelinci'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['vaksinasi']['kucing'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['vaksinasi']['anjing'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['vaksinasi']['unggas_kelinci'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['operasi_kecil']['kucing'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['operasi_kecil']['anjing'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['operasi_kecil']['unggas_kelinci'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['operasi_besar']['kucing'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['operasi_besar']['anjing'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['operasi_besar']['unggas_kelinci'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['lain_lain']['kucing'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['lain_lain']['anjing'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['lain_lain']['unggas_kelinci'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ $row['total_pasien'] }}</td>
            <td style="border:1px solid #000000; background:{{ $rowBg }};">{{ number_format($row['total_retribusi'], 0, ',', '.') }}</td>
        </tr>
    @endforeach

    <tr>
        <td colspan="19"></td>
    </tr>
    <tr>
        <td colspan="19"></td>
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
        <td colspan="9" style="text-align:center;">Mengetahui,</td>
        <td colspan="1"></td>
        <td colspan="9" style="text-align:center;">Kediri, {{ now()->format('d F Y') }}</td>
    </tr>
    @if($ttd1Jabatan || $ttd2Jabatan)
        <tr>
            <td colspan="9" style="text-align:center;">{{ $ttd1Jabatan ?: 'Kepala Dinas Ketahanan Pangan dan Pertanian' }}</td>
            <td colspan="1"></td>
            <td colspan="9" style="text-align:center;">{{ $ttd2Jabatan ?: 'Kepala UPT Pusat Kesehatan Hewan' }}</td>
        </tr>
        <tr>
            <td colspan="9" style="text-align:center;"></td>
            <td colspan="1"></td>
            <td colspan="9" style="text-align:center;"></td>
        </tr>
    @else
        <tr>
            <td colspan="9" style="text-align:center;">Kepala Dinas Ketahanan Pangan dan Pertanian</td>
            <td colspan="1"></td>
            <td colspan="9" style="text-align:center;">Kepala UPT Pusat Kesehatan Hewan</td>
        </tr>
        <tr>
            <td colspan="9" style="text-align:center;">Kota Kediri</td>
            <td colspan="1"></td>
            <td colspan="9" style="text-align:center;">Dinas Ketahanan Pangan dan Pertanian</td>
        </tr>
        <tr>
            <td colspan="9" style="text-align:center;"></td>
            <td colspan="1"></td>
            <td colspan="9" style="text-align:center;">Kota Kediri</td>
        </tr>
    @endif
    <tr><td colspan="19"></td></tr>
    <tr><td colspan="19"></td></tr>
    <tr><td colspan="19"></td></tr>
    <tr><td colspan="19"></td></tr>

    <tr>
        <td colspan="9" style="text-align:center; font-weight:bold; text-decoration:underline;">{{ $ttd1Nama ?: 'NamaNanti1' }}</td>
        <td colspan="1"></td>
        <td colspan="9" style="text-align:center; font-weight:bold; text-decoration:underline;">{{ $ttd2Nama ?: 'NamaNanti2' }}</td>
    </tr>
    <tr>
        <td colspan="9" style="text-align:center;">NIP. {{ $ttd1Nip ?: '-' }}</td>
        <td colspan="1"></td>
        <td colspan="9" style="text-align:center;">NIP. {{ $ttd2Nip ?: '-' }}</td>
    </tr>
</table>