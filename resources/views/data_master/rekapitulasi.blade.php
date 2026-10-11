@extends('layouts.app')

@section('title', 'Rekapitulasi - S-ALPUKAT')
@section('page_title', 'Rekapitulasi Setoran Retribusi')

@section('content')
<div class="flex-1 flex flex-col space-y-4 max-w-[1600px] mx-auto w-full">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <form method="GET" action="{{ route('rekap-laporan.rekapitulasi') }}">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 w-full">
                <div class="space-y-1.5">
                    <label for="ttd1" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Penandatangan 1</label>
                    <select name="ttd1" id="ttd1" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 focus:outline-none focus:border-brand-primary dark:focus:border-brand-light transition-colors text-sm appearance-none cursor-pointer">
                        <option value="">-- Pilih penandatangan --</option>
                        @foreach($penandatangans as $p)
                            <option value="{{ $p->id_penandatangan }}"
                                data-nama="{{ $p->nama }}"
                                data-nip="{{ $p->nip }}"
                                data-jabatan="{{ $p->jabatan }}"
                                {{ request('ttd1') == $p->id_penandatangan ? 'selected' : '' }}>
                                {{ $p->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label for="ttd2" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Penandatangan 2</label>
                    <select name="ttd2" id="ttd2" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 focus:outline-none focus:border-brand-primary dark:focus:border-brand-light transition-colors text-sm appearance-none cursor-pointer">
                        <option value="">-- Pilih penandatangan --</option>
                        @foreach($penandatangans as $p)
                            <option value="{{ $p->id_penandatangan }}"
                                data-nama="{{ $p->nama }}"
                                data-nip="{{ $p->nip }}"
                                data-jabatan="{{ $p->jabatan }}"
                                {{ request('ttd2') == $p->id_penandatangan ? 'selected' : '' }}>
                                {{ $p->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Filter Tahun</label>
                    <select name="year" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 focus:outline-none focus:border-brand-primary dark:focus:border-brand-light transition-colors text-sm appearance-none cursor-pointer">
                        <option value="">Semua Tahun</option>
                        @foreach($years ?? [] as $yearOption)
                            <option value="{{ $yearOption }}" {{ request('year') == $yearOption ? 'selected' : '' }}>{{ $yearOption }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 mt-2">
                <button type="submit"
                    class="bg-brand-primary hover:bg-brand-dark text-white font-medium py-2 px-4 text-sm rounded-xl shadow-lg shadow-brand-primary/20 transition-all duration-200 flex items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Terapkan</span>
                </button>

                <a href="{{ route('rekap-laporan.rekapitulasi') }}"
                    class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium py-2 px-4 text-sm rounded-xl border border-gray-200 dark:border-gray-600 transition-all duration-200">
                    Reset
                </a>

                <a href="{{ route('rekap-laporan.export-view', request()->query()) }}"
                    class="js-export-link bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 text-sm rounded-xl shadow-lg shadow-green-600/20 transition-all duration-200 flex items-center gap-2">
                    <i class="fa-solid fa-file-excel"></i>
                    Excel Rekapitulasi
                </a>

                <a href="{{ route('rekap-laporan.pdf2', request()->query()) }}"
                    class="js-export-link bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 text-sm rounded-xl shadow-lg shadow-red-600/20 transition-all duration-200 flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf"></i>
                    PDF Rekapitulasi
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex-1 flex flex-col relative">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Rekapitulasi Setoran Retribusi</h3>
            @forelse($filterInfo as $label => $value)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $label }}: {{ $value }}</p>
            @empty
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tidak ada filter (menampilkan seluruh data)</p>
            @endforelse
        </div>

        <div class="table-container overflow-x-auto w-full h-full pb-2">
            <table class="w-full text-center text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap min-w-max border-collapse">
                <thead class="bg-brand-primary text-white sticky top-0 z-10 shadow-sm">
                    <tr>
                        <th rowspan="3" class="px-3 py-3 font-semibold border border-brand-dark/50 align-middle">No</th>
                        <th rowspan="3" class="px-3 py-3 font-semibold border border-brand-dark/50 align-middle">Bulan</th>
                        <th colspan="6" class="px-3 py-2 font-semibold border border-brand-dark/50">Non Operatif</th>
                        <th colspan="6" class="px-3 py-2 font-semibold border border-brand-dark/50">Operatif</th>
                        <th colspan="3" class="px-3 py-2 font-semibold border border-brand-dark/50">Lain-lain</th>
                        <th rowspan="3" class="px-3 py-3 font-semibold border border-brand-dark/50 align-middle">Total Pasien</th>
                        <th rowspan="3" class="px-3 py-3 font-semibold border border-brand-dark/50 align-middle">Total Retribusi (Rp)</th>
                    </tr>
                    <tr>
                        <th colspan="3" class="px-3 py-2 font-semibold border border-brand-dark/50">Pemeriksaan Umum</th>
                        <th colspan="3" class="px-3 py-2 font-semibold border border-brand-dark/50">Vaksinasi</th>
                        <th colspan="3" class="px-3 py-2 font-semibold border border-brand-dark/50">Operasi Kecil</th>
                        <th colspan="3" class="px-3 py-2 font-semibold border border-brand-dark/50">Operasi Besar</th>
                        <th colspan="3" class="px-3 py-2 font-semibold border border-brand-dark/50"></th>
                    </tr>
                    <tr>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Kucing</th>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Anjing</th>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Unggas/Kelinci</th>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Kucing</th>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Anjing</th>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Unggas/Kelinci</th>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Kucing</th>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Anjing</th>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Unggas/Kelinci</th>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Kucing</th>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Anjing</th>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Unggas/Kelinci</th>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Kucing</th>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Anjing</th>
                        <th class="px-3 py-2 font-medium border border-brand-dark/50">Unggas/Kelinci</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($summaryRows as $index => $row)
                        @php $isTotal = $row['month'] === 'TOTAL'; @endphp
                        <tr class="{{ $isTotal ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : 'hover:bg-gray-50 dark:hover:bg-gray-700/30' }} transition-colors">
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $isTotal ? '' : $index + 1 }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700 text-left font-medium text-gray-900 dark:text-white">{{ $row['month'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['pemeriksaan_umum']['kucing'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['pemeriksaan_umum']['anjing'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['pemeriksaan_umum']['unggas_kelinci'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['vaksinasi']['kucing'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['vaksinasi']['anjing'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['vaksinasi']['unggas_kelinci'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['operasi_kecil']['kucing'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['operasi_kecil']['anjing'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['operasi_kecil']['unggas_kelinci'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['operasi_besar']['kucing'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['operasi_besar']['anjing'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['operasi_besar']['unggas_kelinci'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['lain_lain']['kucing'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['lain_lain']['anjing'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700">{{ $row['lain_lain']['unggas_kelinci'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700 font-medium text-gray-900 dark:text-white">{{ $row['total_pasien'] }}</td>
                            <td class="px-3 py-2.5 border border-gray-200 dark:border-gray-700 font-medium text-gray-900 dark:text-white">{{ number_format($row['total_retribusi'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3">
                    <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Entri</div>
                    <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $totalEntri }}</div>
                </div>
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3">
                    <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Hewan (Unik)</div>
                    <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $totalHewanUnik }}</div>
                </div>
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3">
                    <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Retribusi</div>
                    <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">Rp {{ number_format($totalRetribusi, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .table-container::-webkit-scrollbar { height: 8px; }
    .table-container::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 4px; }
    .dark .table-container::-webkit-scrollbar-thumb { background: #64748b; }
</style>
@endpush

@push('scripts')
<script>
    // Isi parameter penandatangan pada link ekspor (Excel dan PDF)
    // dari pilihan dropdown. Nama parameter tetap sama seperti sebelumnya.
    function refreshExportLinks() {
        const params = {};

        [1, 2].forEach(function (i) {
            const select = document.getElementById('ttd' + i);
            const opt = select ? select.options[select.selectedIndex] : null;

            params['ttd' + i + '_nama']    = opt ? (opt.dataset.nama    || '') : '';
            params['ttd' + i + '_nip']     = opt ? (opt.dataset.nip     || '') : '';
            params['ttd' + i + '_jabatan'] = opt ? (opt.dataset.jabatan || '') : '';
        });

        document.querySelectorAll('.js-export-link').forEach(function (link) {
            link.target = '_blank';
            link.rel = 'noopener';

            const url = new URL(link.href, window.location.origin);
            url.searchParams.delete('ttd1');
            url.searchParams.delete('ttd2');

            Object.keys(params).forEach(function (key) {
                if (params[key]) {
                    url.searchParams.set(key, params[key]);
                } else {
                    url.searchParams.delete(key);
                }
            });
            link.href = url.toString();
        });
    }

    refreshExportLinks();
    document.getElementById('ttd1')?.addEventListener('change', refreshExportLinks);
    document.getElementById('ttd2')?.addEventListener('change', refreshExportLinks);
</script>
@endpush