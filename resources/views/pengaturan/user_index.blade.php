@extends('layouts.app')

@section('title', 'Kelola Akun - S-ALPUKAT')
@section('page_title', 'Kelola Akun')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Alert Success -->
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

    <!-- Alert Error -->
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

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Data Akun Pengguna</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Kelola akun yang bisa login ke sistem.</p>
        </div>
        <button type="button" onclick="openAddModal()"
            class="bg-brand-primary hover:bg-brand-dark text-white font-medium py-2.5 px-4 rounded-xl shadow-lg shadow-brand-primary/20 transition-all duration-200 flex items-center gap-2 text-sm">
            <i class="fa-solid fa-user-plus"></i>
            <span>Tambah Akun</span>
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 font-semibold border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Nama</th>
                        <th class="px-5 py-4 font-semibold">Username</th>
                        <th class="px-5 py-4 font-semibold">Role</th>
                        <th class="px-5 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $user->nama }}</td>
                            <td class="px-5 py-3">{{ $user->username }}</td>
                            <td class="px-5 py-3">
                                @if($user->role === 'admin')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-brand-bg text-brand-dark dark:bg-brand-primary/20 dark:text-brand-light">
                                        <i class="fa-solid fa-user-shield"></i> Admin
                                    </span>
                                @elseif($user->role === 'kepala_dinas')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                        <i class="fa-solid fa-user-tie"></i> Kepala Dinas
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <i class="fa-solid fa-user"></i> Operator
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    
                                    <!-- Tombol Edit -->
                                    <button type="button" 
                                        onclick="openEditModal('{{ $user->id_user }}', '{{ addslashes($user->nama) }}', '{{ addslashes($user->username) }}', '{{ $user->role }}')" 
                                        class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 flex items-center justify-center transition-colors tooltip" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    <!-- Form Hapus -->
                                    <form action="{{ url('user/' . $user->id_user) }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete(this, '{{ addslashes($user->nama) }}')" 
                                            class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 flex items-center justify-center transition-colors tooltip" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada akun.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Akun -->
<div id="userModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 py-6">
    <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <form id="userForm" method="POST">
            @csrf
            <input type="hidden" id="form_method" name="_method" value="POST">
            <input type="hidden" id="input_id_user" name="id_user" value="">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 id="modalTitle" class="text-base font-semibold text-gray-900 dark:text-white">Tambah Akun</h3>
                <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="px-6 py-6 space-y-4">
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                    <input type="text" name="nama" id="input_nama" required placeholder="Masukkan nama lengkap"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 focus:outline-none focus:border-brand-primary dark:focus:border-brand-light text-sm">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                    <input type="text" name="username" id="input_username" required placeholder="Username untuk login"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 focus:outline-none focus:border-brand-primary dark:focus:border-brand-light text-sm">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                    <select name="role" id="input_role" required
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 focus:outline-none focus:border-brand-primary dark:focus:border-brand-light text-sm appearance-none cursor-pointer">
                        <option value="operator">Operator</option>
                        <option value="admin">Admin</option>
                        <option value="kepala_dinas">Kepala Dinas</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password
                        <span id="passwordHint" class="text-xs text-gray-400 font-normal">(min. 8 karakter, wajib huruf besar, angka & simbol)</span>
                    </label>
                    <div class="relative group">
                        <!-- Perhatikan pr-10 agar teks tidak menabrak ikon mata -->
                        <input type="password" name="password" id="input_password" placeholder="Masukkan password"
                            class="w-full px-4 py-2.5 pr-10 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 focus:outline-none focus:border-brand-primary dark:focus:border-brand-light text-sm transition-colors">
                        <button type="button" onclick="togglePasswordVisibility('input_password', 'eyeIcon1')" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-brand-primary transition-colors focus:outline-none">
                            <i class="fa-regular fa-eye" id="eyeIcon1"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password</label>
                    <div class="relative group">
                        <input type="password" name="password_confirmation" id="input_password_confirmation" placeholder="Ulangi password"
                            class="w-full px-4 py-2.5 pr-10 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 focus:outline-none focus:border-brand-primary dark:focus:border-brand-light text-sm transition-colors">
                        <button type="button" onclick="togglePasswordVisibility('input_password_confirmation', 'eyeIcon2')" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-brand-primary transition-colors focus:outline-none">
                            <i class="fa-regular fa-eye" id="eyeIcon2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeModal()"
                    class="w-full sm:w-auto bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-medium py-2.5 px-5 rounded-xl transition-all duration-200 hover:bg-gray-200 dark:hover:bg-gray-600">
                    Batal
                </button>
                <button type="submit"
                    class="w-full sm:w-auto bg-brand-primary hover:bg-brand-dark text-white font-medium py-2.5 px-5 rounded-xl transition-all duration-200">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // --- FITUR AUTO-HIDE ALERT ---
    function closeAlert(elementId) {
        const alertElement = document.getElementById(elementId);
        if (alertElement) {
            alertElement.style.opacity = '0';
            setTimeout(() => {
                alertElement.style.display = 'none';
            }, 500);
        }
    }

    if (document.getElementById('alert-success')) {
        setTimeout(() => { closeAlert('alert-success'); }, 2000); 
    }

    if (document.getElementById('alert-error')) {
        setTimeout(() => { closeAlert('alert-error'); }, 2000); 
    }

    // --- LOGICA MODAL ---
    const userModal = document.getElementById('userModal');
    const userForm = document.getElementById('userForm');
    const modalTitle = document.getElementById('modalTitle');
    const formMethod = document.getElementById('form_method');
    const passwordHint = document.getElementById('passwordHint');
    const inputPassword = document.getElementById('input_password');
    const inputPasswordConfirm = document.getElementById('input_password_confirmation');

    // Fungsi untuk Toggle Ikon Mata
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Mengembalikan ikon mata ke default saat modal ditutup/dibuka
    function resetPasswordVisibility() {
        inputPassword.type = 'password';
        inputPasswordConfirm.type = 'password';
        document.getElementById('eyeIcon1').className = 'fa-regular fa-eye';
        document.getElementById('eyeIcon2').className = 'fa-regular fa-eye';
    }

    function openAddModal() {
        modalTitle.textContent = 'Tambah Akun';
        userForm.action = "{{ route('user.store') }}";
        formMethod.value = 'POST';

        userForm.reset();
        resetPasswordVisibility();
        document.getElementById('input_role').value = 'operator';

        inputPassword.required = true;
        inputPasswordConfirm.required = true;
        passwordHint.textContent = '(min. 8 karakter, wajib huruf besar, angka & simbol)';

        userModal.classList.remove('hidden');
        userModal.classList.add('flex');
    }

    function openEditModal(id, nama, username, role) {
        modalTitle.textContent = 'Edit Akun';
        userForm.action = "{{ url('user') }}/" + id;
        formMethod.value = 'PUT';

        userForm.reset();
        resetPasswordVisibility();
        document.getElementById('input_id_user').value = id;
        document.getElementById('input_nama').value = nama;
        document.getElementById('input_username').value = username;
        document.getElementById('input_role').value = role;

        // Saat edit, password opsional -- dikosongkan berarti password lama tetap dipakai
        inputPassword.required = false;
        inputPasswordConfirm.required = false;
        passwordHint.textContent = '(kosongkan jika tidak ingin mengganti password)';

        userModal.classList.remove('hidden');
        userModal.classList.add('flex');
    }

    function closeModal() {
        userModal.classList.add('hidden');
        userModal.classList.remove('flex');
        setTimeout(resetPasswordVisibility, 300); // Reset setelah modal perlahan tertutup
    }

    userModal.addEventListener('click', (e) => {
        if (e.target === userModal) closeModal();
    });

    function confirmDelete(button, nama) {
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: `Akun "${nama}" akan dihapus permanen!`,
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

    @if($errors->any())
        // Buka kembali modal yang sesuai kalau validasi gagal, biar data yang sudah diketik tidak hilang
        @if(old('_method') === 'PUT')
            openEditModal('{{ old('id_user') }}', @json(old('nama')), @json(old('username')), '{{ old('role') }}');
            userForm.action = "{{ url('user') }}/{{ old('id_user') }}";
            formMethod.value = 'PUT';
        @else
            openAddModal();
            document.getElementById('input_nama').value = @json(old('nama'));
            document.getElementById('input_username').value = @json(old('username'));
            document.getElementById('input_role').value = "{{ old('role', 'operator') }}";
        @endif

        Swal.fire({
            icon: 'error',
            title: 'Periksa kembali isian',
            html: `{!! implode('<br>', $errors->all()) !!}`
        });
    @endif
</script>
@endpush
@endsection