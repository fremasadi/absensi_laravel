<?php

namespace App\Http\Controllers;

use App\Models\PermintaanIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['bukti_uploaded_at'] = null; // Set null jika tidak ada bukti

        PermintaanIzin::create($data);

        return redirect()->route('permintaan-izin.index')
            ->with('success', 'Permintaan izin berhasil dibuat.');
    }

    public function show(PermintaanIzin $permintaanIzin)
    {
        return view('permintaan-izin.show', compact('permintaanIzin'));
    }

    // Method untuk upload bukti yang perlu ditambahkan ke controller
public function uploadBukti(Request $request, $id)
{
    $permintaanIzin = PermintaanIzin::findOrFail($id);
    $user = auth()->user();
    
    // Validasi: hanya pemilik atau admin yang bisa upload
    if ($user->role !== 'admin' && $user->id !== $permintaanIzin->user_id) {
        return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk upload bukti ini.');
    }
    
    // Validasi request
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
    ]);
    
    try {
        // Hapus gambar lama jika ada
        if ($permintaanIzin->image) {
            \Storage::disk('public')->delete($permintaanIzin->image);
        }
        
        // Upload gambar baru
        $imagePath = $request->file('image')->store('bukti-izin', 'public');
        
        // Update data di database
        $permintaanIzin->update([
            'image' => $imagePath,
            'bukti_uploaded_at' => now()
        ]);
        
        $message = 'Bukti berhasil diupload.';
        if ($user->role === 'admin' && $user->id !== $permintaanIzin->user_id) {
            $message = 'Bukti berhasil diupload oleh admin untuk ' . $permintaanIzin->user->name . '.';
        }
        
        return redirect()->route('permintaan-izin.index')
            ->with('success', $message);
            
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Terjadi kesalahan saat upload bukti.');
    }
}
   
}