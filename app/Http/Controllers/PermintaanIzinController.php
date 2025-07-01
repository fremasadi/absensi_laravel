<?php

namespace App\Http\Controllers;

use App\Models\PermintaanIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'izin_' . Auth::id() . '_' . time() . '.' . $image->getClientOriginalExtension();
            
            // Simpan ke storage/app/public/izin-bukti
            $path = $image->storeAs('izin-bukti', $imageName, 'public');
            $data['image'] = 'izin-bukti/' . $imageName;
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
                Storage::disk('public')->delete($permintaanIzin->image);
            }

            $image = $request->file('image');
            $imageName = 'izin_' . Auth::id() . '_' . time() . '.' . $image->getClientOriginalExtension();
            
            // Simpan ke storage/app/public/izin-bukti
            $path = $image->storeAs('izin-bukti', $imageName, 'public');
            $data['image'] = 'izin-bukti/' . $imageName;
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

    /**
     * Upload bukti izin setelah tanggal mulai izin
     */
    public function uploadBukti(Request $request, PermintaanIzin $permintaanIzin)
    {
        // Validasi bahwa user hanya bisa upload bukti untuk izin mereka sendiri
        if ($permintaanIzin->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk upload bukti izin ini.');
        }

        // Validasi tanggal - hanya bisa upload pada atau setelah tanggal mulai izin
        $today = Carbon::today();
        $tanggalMulai = Carbon::parse($permintaanIzin->tanggal_mulai);
        $batasUpload = $tanggalMulai->copy()->addDays(3); // Batas upload 3 hari setelah tanggal mulai

        if ($today->lt($tanggalMulai)) {
            return redirect()->back()->with('error', 'Belum waktunya untuk upload bukti. Anda bisa upload mulai tanggal ' . $tanggalMulai->format('d/m/Y'));
        }

        if ($today->gt($batasUpload)) {
            return redirect()->back()->with('error', 'Waktu upload bukti sudah berakhir. Batas upload sampai tanggal ' . $batasUpload->format('d/m/Y'));
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ], [
            'image.required' => 'File bukti harus diupload.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format file harus JPG, JPEG, atau PNG.',
            'image.max' => 'Ukuran file maksimal 2MB.'
        ]);

        // Hapus gambar lama jika ada
        if ($permintaanIzin->image) {
            Storage::disk('public')->delete($permintaanIzin->image);
        }

        // Upload gambar baru
        $image = $request->file('image');
        $imageName = 'bukti_izin_' . $permintaanIzin->id . '_' . Auth::id() . '_' . time() . '.' . $image->getClientOriginalExtension();
        
        // Simpan ke storage/app/public/izin-bukti
        $path = $image->storeAs('izin-bukti', $imageName, 'public');
        
        // Update database
        $permintaanIzin->update([
            'image' => 'izin-bukti/' . $imageName,
            'bukti_uploaded_at' => now()
        ]);

        return redirect()->back()->with('success', 'Bukti izin berhasil diupload.');
    }

    /**
     * Hapus bukti izin
     */
    public function deleteBukti(PermintaanIzin $permintaanIzin)
    {
        // Validasi bahwa user hanya bisa hapus bukti untuk izin mereka sendiri
        if ($permintaanIzin->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus bukti izin ini.');
        }

        if ($permintaanIzin->image) {
            Storage::disk('public')->delete($permintaanIzin->image);
            
            $permintaanIzin->update([
                'image' => null,
                'bukti_uploaded_at' => null
            ]);

            return redirect()->back()->with('success', 'Bukti izin berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Tidak ada bukti yang bisa dihapus.');
    }
}