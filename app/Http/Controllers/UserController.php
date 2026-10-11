<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Tampilkan daftar semua akun (admin & operator)
     */
    public function index()
    {
        $users = User::orderBy('role')->orderBy('nama')->get();

        return view('pengaturan.user_index', compact('users'));
    }

    /**
     * Simpan akun baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:100|unique:users,username',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => 'required|in:admin,operator,kepala_dinas',
        ], [
            'username.unique' => 'Username sudah dipakai, silakan pilih yang lain.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus berisi 8 karakter.',
            'password.mixed' => 'Password harus mengandung setidaknya satu huruf besar dan huruf kecil.',
            'password.numbers' => 'Password harus mengandung setidaknya satu angka.',
            'password.symbols' => 'Password harus mengandung setidaknya satu simbol khusus.',
        ]);

        User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password' => $request->password, // otomatis di-hash lewat cast 'hashed' di model User
            'role' => $request->role,
        ]);

        return back()->with('success', 'Akun berhasil ditambahkan.');
    }

    /**
     * Update akun (password bersifat opsional -- hanya berubah kalau diisi)
     */
    public function update(Request $request, $id_user)
    {
        $user = User::findOrFail($id_user);

        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:100|unique:users,username,' . $id_user . ',id_user',
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => 'required|in:admin,operator,kepala_dinas',
        ], [
            'username.unique' => 'Username sudah dipakai, silakan pilih yang lain.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal harus berisi 8 karakter.',
            'password.mixed' => 'Password harus mengandung setidaknya satu huruf besar dan huruf kecil.',
            'password.numbers' => 'Password harus mengandung setidaknya satu angka.',
            'password.symbols' => 'Password harus mengandung setidaknya satu simbol khusus.',
        ]);

        $data = [
            'nama' => $request->nama,
            'username' => $request->username,
            'role' => $request->role,
        ];

        // Password cuma diganti kalau field-nya diisi. Kalau dikosongkan saat edit, password lama tetap dipakai.
        if ($request->filled('password')) {
            $data['password'] = $request->password; // otomatis di-hash lewat cast di model
        }

        $user->update($data);

        return back()->with('success', 'Akun berhasil diperbarui.');
    }

    /**
     * Hapus akun
     */
    public function destroy($id_user)
    {
        $user = User::findOrFail($id_user);

        // Cegah user menghapus akunnya sendiri saat sedang login
        if (Auth::id() == $user->id_user) {
            return back()->with('error', 'Tidak bisa menghapus akun yang sedang kamu pakai untuk login.');
        }

        // Cegah admin terakhir terhapus, biar sistem nggak "terkunci" tanpa admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Tidak bisa menghapus admin terakhir.');
        }

        $user->delete();

        return back()->with('success', 'Akun berhasil dihapus.');
    }
}