<?php

namespace App\Http\Controllers;

use App\Models\PermintaanIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PermintaanIzinController extends Controller
{
    public function index()
    {
        // Get currently logged in user
        $user = Auth::user();
        
        // If user is admin, show all requests
        if ($user->role === 'admin') {
            $permintaanIzins = PermintaanIzin::with('user')->latest()->get();
        } else {
            // If regular user, only show their own requests
            $permintaanIzins = PermintaanIzin::with('user')
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }
        
        return view('permintaan-izin.index', compact('permintaanIzins'));
    }

    public function create()
    {
        return view('permintaan-izin.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis_izin' => 'required|string',
            'alasan' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            
            // Simpan ke storage/app/public
            $path = $image->storeAs($imageName);
            $data['image'] = $imageName;
        }

        PermintaanIzin::create($data);

        return redirect()->route('permintaan-izin.index')
            ->with('success', 'Permintaan izin berhasil dibuat.');
    }

    public function show(PermintaanIzin $permintaanIzin)
    {
        return view('permintaan-izin.show', compact('permintaanIzin'));
    }

    public function edit(PermintaanIzin $permintaanIzin)
    {
        return view('permintaan-izin.edit', compact('permintaanIzin'));
    }

    public function update(Request $request, PermintaanIzin $permintaanIzin)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis_izin' => 'required|string',
            'alasan' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($permintaanIzin->image) {
                Storage::disk('public')->delete( $permintaanIzin->image);
            }

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            
            // Simpan ke storage/app/public
            $path = $image->storeAs($imageName);
            $data['image'] = $imageName;
        }

        $permintaanIzin->update($data);

        return redirect()->route('permintaan-izin.index')
            ->with('success', 'Permintaan izin berhasil diperbarui.');
    }

    public function destroy(PermintaanIzin $permintaanIzin)
    {
        if ($permintaanIzin->image) {
            Storage::disk('public')->delete($permintaanIzin->image);
        }

        $permintaanIzin->delete();

        return redirect()->route('permintaan-izin.index')
            ->with('success', 'Permintaan izin berhasil dihapus.');
    }

    public function updateStatus(PermintaanIzin $permintaanIzin)
    {
        $permintaanIzin->update([
            'status' => !$permintaanIzin->status
        ]);

        return redirect()->back()
            ->with('success', 'Status permintaan izin berhasil diperbarui.');
    }
}