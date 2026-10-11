@extends('layouts.app')

@section('title', 'Dashboard Kepala Dinas - S-ALPUKAT')
@section('page_title', 'Dashboard Kepala Dinas')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-gradient-to-r from-brand-primary to-brand-light rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10">
            <p class="text-sm font-semibold uppercase tracking-wider text-white/80">Ringkasan Pelayanan</p>
            <h2 class="text-2xl sm:text-3xl font-bold mt-2">Selamat datang, {{ Auth::user()->nama }}!</h2>
            <p class="mt-2 text-sm sm:text-base text-white/90">Pantau jumlah hewan yang diperiksa selama 12 bulan terakhir.</p>
        </div>
        <i class="fa-solid fa-paw absolute -bottom-8 -right-5 text-8xl text-white opacity-10 rotate-[-12deg]"></i>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-brand-primary dark:text-brand-light">Ringkasan Eksekutif</p>
            <h2 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">Ringkasan Pelayanan Kesehatan Hewan</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Ikhtisar bulan {{ now()->locale('id')->translatedFormat('F Y') }} dan tren 12 bulan terakhir.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <section class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-notes-medical"></i>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Pemeriksaan Bulan Ini</p>
            </div>
            <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($pemeriksaanBulanIni) }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $perbandinganPemeriksaan }}</p>
        </section>

        <section class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-paw"></i>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Hewan Unik Diperiksa</p>
            </div>
            <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($hewanUnikBulanIni) }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Bulan ini; satu hewan dihitung satu kali</p>
        </section>

        <section class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Retribusi Bulan Ini</p>
            </div>
            <p class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($retribusiBulanIni, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Berdasarkan tarif pelayanan tercatat</p>
        </section>

        <section class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-shield-virus"></i>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Kasus Terdeteksi</p>
            </div>
            <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($jumlahKasusBulanIni) }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Diagnosis selain “Sehat”, bulan ini</p>
        </section>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <section class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-2xl p-5 sm:p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Tren Pemeriksaan</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Jumlah rekam medis setiap bulan selama 12 bulan terakhir.</p>
            </div>
            <div class="relative h-72">
                <canvas id="pemeriksaanChart" role="img" aria-label="Grafik tren jumlah pemeriksaan selama 12 bulan terakhir"></canvas>
            </div>
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                Total {{ number_format($totalPemeriksaan) }} pemeriksaan selama 12 bulan terakhir.
            </p>
        </section>

        <section class="bg-white dark:bg-gray-800 rounded-2xl p-5 sm:p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Komposisi Jenis Hewan</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Jumlah pemeriksaan bulan ini.</p>
            </div>
            @if($jenisHewanBulanIni->isNotEmpty())
                <div class="relative h-64">
                    <canvas id="jenisHewanChart" role="img" aria-label="Grafik komposisi pemeriksaan berdasarkan jenis hewan bulan ini"></canvas>
                </div>
                <p class="mt-3 text-center text-sm text-gray-500 dark:text-gray-400">
                    Terbanyak: <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $jenisHewanBulanIni->first()->nama }}</span>
                    ({{ number_format($jenisHewanBulanIni->first()->jumlah) }} pemeriksaan)
                </p>
            @else
                <div class="h-64 flex items-center justify-center text-sm text-gray-500 dark:text-gray-400">
                    Belum ada data pemeriksaan bulan ini.
                </div>
            @endif
        </section>
    </div>

    <section class="bg-white dark:bg-gray-800 rounded-2xl p-5 sm:p-6 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="mb-4">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Sorotan Kasus Bulan Ini</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ringkasan diagnosis dan wilayah dari rekam medis non-Sehat.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-xl bg-gray-50 dark:bg-gray-900/60 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Diagnosis Terbanyak</p>
                @if($diagnosaTerbanyak)
                    <p class="mt-2 font-semibold text-gray-900 dark:text-white">{{ $diagnosaTerbanyak->nama }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ number_format($diagnosaTerbanyak->jumlah) }} pemeriksaan</p>
                @else
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada diagnosis non-Sehat bulan ini.</p>
                @endif
            </div>
            <div class="rounded-xl bg-gray-50 dark:bg-gray-900/60 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Wilayah Kasus Terbanyak</p>
                @if($wilayahKasusTerbanyak)
                    <p class="mt-2 font-semibold text-gray-900 dark:text-white line-clamp-2" title="{{ $wilayahKasusTerbanyak->nama }}">{{ $wilayahKasusTerbanyak->nama }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ number_format($wilayahKasusTerbanyak->jumlah) }} pemeriksaan</p>
                @else
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada data wilayah kasus bulan ini.</p>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    const pemeriksaanCanvas = document.getElementById('pemeriksaanChart');

    if (pemeriksaanCanvas) {
        new Chart(pemeriksaanCanvas, {
            type: 'bar',
            data: {
                labels: @json($labels),
                datasets: [{
                    label: 'Jumlah pemeriksaan',
                    data: @json($jumlahPemeriksaan),
                    backgroundColor: '#40916c',
                    borderRadius: 6,
                    maxBarThickness: 48,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                    },
                },
            },
        });
    }

    const jenisHewanCanvas = document.getElementById('jenisHewanChart');

    if (jenisHewanCanvas) {
        new Chart(jenisHewanCanvas, {
            type: 'doughnut',
            data: {
                labels: @json($labelJenisHewan),
                datasets: [{
                    data: @json($dataJenisHewan),
                    backgroundColor: ['#40916c', '#3b82f6', '#f97316', '#8b5cf6', '#eab308', '#94a3b8'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 16 },
                    },
                },
            },
        });
    }
</script>
@endpush
