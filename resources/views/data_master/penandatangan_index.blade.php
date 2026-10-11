@extends('layouts.app')

@section('title', 'Kelola Data Penandatangan - S-ALPUKAT')
@section('page_title', 'Kelola Data Penandatangan')

@push('styles')
<style>
    .form-input-focus:focus {
        box-shadow: 0 0 0 2px rgba(64, 145, 108, 0.2);
    }
    .modal-overlay { backdrop-filter: blur(4px); transition: opacity 0.3s ease, visibility 0.3s ease; }
    .modal-content { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease; }
    .modal-hidden { opacity: 0; visibility: hidden; }
    .modal-hidden .modal-content { transform: scale(0.95) translateY(10px); opacity: 0; }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    @if(session('success'))
    <div id="alert-success" class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-sm mb-4 flex justify-between items-center transition-opacity duration-500">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-check-circle"></i>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
        <button onclick="closeAlert('alert-success')" class="text-green-600 hover:text-green-800 focus:outline-none px-2">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div id="alert-error" class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-sm mb-4 flex justify-between items-center transition-opacity duration-500">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
        <button onclick="closeAlert('alert-error')" class="text-red-600 hover:text-red-800 focus:outline-none px-2">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <form action="{{ route('penandatangan.index') }}" method="GET" class="relative w-full sm:w-96">
            <button type="submit" class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 hover:text-brand-primary transition-colors">
                <i class="fa-solid fa-search"></i>
            </button>
            <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama atau NIP..."
                class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-300 focus:outline-none focus:border-brand-primary dark:focus:border-brand-light form-input-focus transition-colors text-sm shadow-sm">
        </form>

        <button onclick="openModal('add')" class="w-full sm:w-auto bg-brand-primary hover:bg-brand-dark text-white font-medium py-2.5 px-5 rounded-xl shadow-lg shadow-brand-primary/20 transform hover:-translate-y-0.5 transition-all duration-200 flex justify-center items-center gap-2 text-sm">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Penandatangan</span>
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 font-semibold border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 whitespace-nowrap w-24">No</th>
                        <th class="px-6 py-4 whitespace-nowrap">Nama (beserta gelar)</th>
                        <th class="px-6 py-4 whitespace-nowrap">NIP</th>
                        <th class="px-6 py-4 min-w-[250px]">Jabatan</th>
                        <th class="px-6 py-4 whitespace-nowrap text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($penandatangans as $index => $penandatangan)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors group">
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-md text-xs font-mono">
                                {{ $penandatangans->firstItem() + $index }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $penandatangan->nama }}</td>
                        <td class="px-6 py-4 font-mono">{{ $penandatangan->nip }}</td>
                        <td class="px-6 py-4">{{ $penandatangan->jabatan ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button onclick="openModal('edit', {{ $penandatangan }})" class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 flex items-center justify-center transition-colors" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('penandatangan.destroy', $penandatangan->id_penandatangan) }}" method="POST" class="m-0 p-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete(this)" class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 flex items-center justify-center transition-colors" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-folder-open text-4xl mb-3 opacity-50"></i>
                            <p>Belum ada data penandatangan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between text-xs text-gray-500 dark:text-gray-400 shrink-0">
            <span>
                Menampilkan {{ $penandatangans->firstItem() ?? 0 }} sampai {{ $penandatangans->lastItem() ?? 0 }} dari {{ $penandatangans->total() }} entri
            </span>
            <div class="w-full sm:w-auto">
                {{ $penandatangans->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Penandatangan -->
<div id="ttdModal" class="modal-hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-gray-900/60" onclick="closeModal()"></div>

    <div class="modal-content relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/50">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-signature text-brand-primary"></i>
                <span id="modalTitleText">Tambah Data Penandatangan</span>
            </h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="ttdForm" action="{{ route('penandatangan.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div id="method-container"></div>

            <div class="space-y-1.5">
                <label for="nama" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama (beserta gelar) <span class="text-red-500">*</span></label>
                <input type="text" id="nama" name="nama" required placeholder="Contoh: Ir. Un Achmad Nurdin, MM"
                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 focus:outline-none focus:border-brand-primary dark:focus:border-brand-light form-input-focus transition-colors text-sm">
                @error('nama')
                <p class="text-red-500 text-xs my-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label for="nip" class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIP <span class="text-red-500">*</span></label>
                <input type="text" id="nip" name="nip" required placeholder="Contoh: 197501012000031001"
                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 focus:outline-none focus:border-brand-primary dark:focus:border-brand-light form-input-focus transition-colors text-sm">
                @error('nip')
                <p class="text-red-500 text-xs my-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label for="jabatan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan <span class="text-gray-400 text-xs">(opsional)</span></label>
                <input type="text" id="jabatan" name="jabatan" placeholder="Jika kosong, dokumen memakai jabatan bawaan"
                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 focus:outline-none focus:border-brand-primary dark:focus:border-brand-light form-input-focus transition-colors text-sm">
                @error('jabatan')
                <p class="text-red-500 text-xs my-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 mt-2 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
                    Batal
                </button>
                <button type="submit" onclick="showLoading(this)" class="px-5 py-2.5 bg-brand-primary hover:bg-brand-dark text-white rounded-xl shadow-md shadow-brand-primary/20 transition-all text-sm font-medium flex items-center gap-2">
                    <i class="fa-solid fa-save"></i>
                    <span>Simpan Data</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const modal = document.getElementById('ttdModal');
    const form = document.getElementById('ttdForm');
    const modalTitleText = document.getElementById('modalTitleText');
    const methodContainer = document.getElementById('method-container');

    const storeUrl = "{{ route('penandatangan.store') }}";
    const updateUrlBase = "{{ url('penandatangan') }}";

    function closeAlert(elementId) {
        const el = document.getElementById(elementId);
        if (el) {
            el.style.opacity = '0';
            setTimeout(() => { el.style.display = 'none'; }, 500);
        }
    }

    if (document.getElementById('alert-success')) {
        setTimeout(() => closeAlert('alert-success'), 2000);
    }
    if (document.getElementById('alert-error')) {
        setTimeout(() => closeAlert('alert-error'), 2000);
    }

    function openModal(mode, data = null) {
        form.reset();
        methodContainer.innerHTML = '';

        if (mode === 'edit' && data) {
            modalTitleText.textContent = 'Edit Data Penandatangan';
            form.action = updateUrlBase + '/' + data.id_penandatangan;
            methodContainer.innerHTML = '@method("PUT")';

            document.getElementById('nama').value = data.nama;
            document.getElementById('nip').value = data.nip;
            document.getElementById('jabatan').value = data.jabatan || '';
        } else {
            modalTitleText.textContent = 'Tambah Data Penandatangan';
            form.action = storeUrl;
        }

        modal.classList.remove('modal-hidden');
        document.getElementById('nama').focus();
    }

    function closeModal() {
        modal.classList.add('modal-hidden');
        setTimeout(() => form.reset(), 300);
    }

    function showLoading(btn) {
        if (form.checkValidity()) {
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Menyimpan...</span>';
            btn.classList.add('opacity-80', 'cursor-not-allowed');
        }
    }

    function confirmDelete(button) {
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: "Data penandatangan yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fa-solid fa-trash mr-1"></i> Ya, Hapus!',
            cancelButtonText: '<i class="fa-solid fa-xmark mr-1"></i> Batal',
            background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#111827',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'px-5 py-2.5 rounded-xl font-medium tracking-wide',
                cancelButton: 'px-5 py-2.5 rounded-xl font-medium tracking-wide'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                button.closest('form').submit();
            }
        });
    }

    // Buka modal otomatis kalau ada error validasi
    const adaError = {{ $errors->any() ? 'true' : 'false' }};
    if (adaError) {
        document.addEventListener("DOMContentLoaded", function () {
            modal.classList.remove('modal-hidden');
        });
    }
</script>
@endpush