<?php

namespace App\Http\Controllers;

use App\Models\Penandatangan;
use Illuminate\Http\Request;

class PenandatanganController extends Controller
{
    public function index(Request $request)
    {
        $query = Penandatangan::query();

        if ($request->filled('cari')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->cari . '%')
                  ->orWhere('nip', 'like', '%' . $request->cari . '%');
            });
        }

        $penandatangans = $query->orderBy('nama', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('data_master.penandatangan_index', compact('penandatangans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'    => 'required|string|max:150',
            'nip'     => 'required|string|max:30',
            'jabatan' => 'nullable|string|max:150',
        ], [
            'nama.required' => 'Nama penandatangan wajib diisi.',
            'nip.required'  => 'NIP wajib diisi.',
        ]);

        if (Penandatangan::where('nip', $validated['nip'])->exists()) {
            return redirect()->back()
                ->with('error', 'NIP ini sudah terdaftar di sistem.');
        }

        Penandatangan::create($validated);

        return redirect()->route('penandatangan.index')
            ->with('success', 'Data penandatangan berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $penandatangan = Penandatangan::findOrFail($id);

        $validated = $request->validate([
            'nama'    => 'required|string|max:150',
            'nip'     => 'required|string|max:30',
            'jabatan' => 'nullable|string|max:150',
        ], [
            'nama.required' => 'Nama penandatangan wajib diisi.',
            'nip.required'  => 'NIP wajib diisi.',
        ]);

        if (Penandatangan::where('nip', $validated['nip'])
                ->where('id_penandatangan', '!=', $id)->exists()) {
            return redirect()->back()
                ->with('error', 'NIP ini sudah terdaftar di sistem.');
        }

        $penandatangan->update($validated);

        return redirect()->back()
            ->with('success', 'Data penandatangan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        Penandatangan::findOrFail($id)->delete();

        return redirect()->back()
            ->with('success', 'Data penandatangan berhasil dihapus!');
    }
}