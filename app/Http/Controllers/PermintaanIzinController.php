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
    // Aktifkan query logging untuk debugging
    \DB::enableQueryLog();
    
    // Debug: Log request details
    Log::info('Upload bukti request via POST', [
        'user_id' => Auth::id(),
        'permission_id' => $permintaanIzin->id,
        'has_file' => $request->hasFile('image'),
        'request_method' => $request->method(),
        'all_request_data' => $request->all(),
        'file_details' => $request->hasFile('image') ? [
            'original_name' => $request->file('image')->getClientOriginalName(),
            'size' => $request->file('image')->getSize(),
            'mime_type' => $request->file('image')->getMimeType()
        ] : null
    ]);

    // Validasi bahwa user hanya bisa upload bukti untuk izin mereka sendiri
    if ($permintaanIzin->user_id !== Auth::id()) {
        return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk upload bukti izin ini.');
    }

    // Validasi tanggal
    $today = Carbon::today();
    $tanggalMulai = Carbon::parse($permintaanIzin->tanggal_mulai);
    $batasUpload = $tanggalMulai->copy()->addDays(3);

    if ($today->lt($tanggalMulai)) {
        return redirect()->back()->with('error', 'Belum waktunya untuk upload bukti. Anda bisa upload mulai tanggal ' . $tanggalMulai->format('d/m/Y'));
    }

    if ($today->gt($batasUpload)) {
        return redirect()->back()->with('error', 'Waktu upload bukti sudah berakhir. Batas upload sampai tanggal ' . $batasUpload->format('d/m/Y'));
    }

    // Validasi file
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
    ], [
        'image.required' => 'File bukti harus diupload.',
        'image.image' => 'File harus berupa gambar.',
        'image.mimes' => 'Format file harus JPG, JPEG, atau PNG.',
        'image.max' => 'Ukuran file maksimal 2MB.'
    ]);

    try {
        // Pastikan folder ada
        if (!Storage::disk('public')->exists('izin-bukti')) {
            Storage::disk('public')->makeDirectory('izin-bukti');
        }

        // Hapus gambar lama jika ada
        if ($permintaanIzin->image) {
            Storage::disk('public')->delete($permintaanIzin->image);
        }

        // Upload gambar baru
        $image = $request->file('image');
        $imageName = 'bukti_izin_' . $permintaanIzin->id . '_' . Auth::id() . '_' . time() . '.' . $image->getClientOriginalExtension();
        
        // Simpan file
        $path = $image->storeAs('izin-bukti', $imageName, 'public');
        
        // Path yang akan disimpan di database
        $imagePath = 'izin-bukti/' . $imageName;
        
        Log::info('File uploaded successfully', [
            'path' => $path,
            'image_path' => $imagePath,
            'file_exists' => Storage::disk('public')->exists($imagePath)
        ]);

        // Debug: Cek data sebelum update
        Log::info('Data sebelum update', [
            'current_image' => $permintaanIzin->image,
            'current_bukti_uploaded_at' => $permintaanIzin->bukti_uploaded_at,
            'fillable_fields' => $permintaanIzin->getFillable()
        ]);

        // METHOD 1: Menggunakan update() - Lebih eksplisit
        $updateData = [
            'image' => $imagePath,
            'bukti_uploaded_at' => now()
        ];
        
        $updateResult = $permintaanIzin->update($updateData);
        
        // METHOD 2: Alternatif menggunakan DB::table() jika method 1 gagal
        // $updateResult = DB::table('permintaan_izins')
        //     ->where('id', $permintaanIzin->id)
        //     ->update($updateData);

        // Debug: Log update result
        Log::info('Database update result', [
            'success' => $updateResult,
            'updated_data' => $updateData,
            'sql_queries' => DB::getQueryLog()
        ]);

        // Refresh model untuk mendapatkan data terbaru
        $permintaanIzin->refresh();
        
        // Verifikasi data tersimpan
        Log::info('Data setelah update', [
            'updated_image' => $permintaanIzin->image,
            'updated_bukti_uploaded_at' => $permintaanIzin->bukti_uploaded_at,
            'expected_image' => $imagePath
        ]);

        if ($updateResult && $permintaanIzin->image === $imagePath) {
            return redirect()->back()->with('success', 'Bukti izin berhasil diupload.');
        } else {
            // Jika update gagal, coba method alternatif
            Log::warning('Update gagal, mencoba method alternatif');
            
            // Method alternatif menggunakan raw query
            $rawUpdateResult = DB::table('permintaan_izins')
                ->where('id', $permintaanIzin->id)
                ->update([
                    'image' => $imagePath,
                    'bukti_uploaded_at' => now(),
                    'updated_at' => now()
                ]);
            
            Log::info('Raw update result', ['result' => $rawUpdateResult]);
            
            if ($rawUpdateResult) {
                return redirect()->back()->with('success', 'Bukti izin berhasil diupload.');
            } else {
                return redirect()->back()->with('error', 'Gagal menyimpan data ke database.');
            }
        }

    } catch (\Exception $e) {
        // Log error
        Log::error('Upload bukti error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return redirect()->back()->with('error', 'Terjadi kesalahan saat upload: ' . $e->getMessage());
    }
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