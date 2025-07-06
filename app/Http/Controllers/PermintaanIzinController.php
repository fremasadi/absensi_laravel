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
 * Upload bukti izin menggunakan PUT method
 */
public function uploadBukti(Request $request, PermintaanIzin $permintaanIzin)
{
    // Enable query logging
    \DB::enableQueryLog();
    
    Log::info('Upload bukti request via PUT', [
        'user_id' => Auth::id(),
        'permission_id' => $permintaanIzin->id,
        'has_file' => $request->hasFile('image'),
        'request_method' => $request->method(),
        'content_type' => $request->header('content-type'),
        'file_details' => $request->hasFile('image') ? [
            'original_name' => $request->file('image')->getClientOriginalName(),
            'size' => $request->file('image')->getSize(),
            'mime_type' => $request->file('image')->getMimeType()
        ] : null
    ]);

    // Validasi ownership
    if ($permintaanIzin->user_id !== Auth::id()) {
        Log::warning('Unauthorized upload attempt', [
            'user_id' => Auth::id(),
            'permission_owner' => $permintaanIzin->user_id,
            'permission_id' => $permintaanIzin->id
        ]);
        return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk upload bukti izin ini.');
    }

    // Validasi tanggal
    $today = Carbon::today();
    $tanggalMulai = Carbon::parse($permintaanIzin->tanggal_mulai);
    $batasUpload = $tanggalMulai->copy()->addDays(3);

    Log::info('Date validation', [
        'today' => $today->format('Y-m-d'),
        'tanggal_mulai' => $tanggalMulai->format('Y-m-d'),
        'batas_upload' => $batasUpload->format('Y-m-d')
    ]);

    if ($today->lt($tanggalMulai)) {
        return redirect()->back()->with('error', 'Belum waktunya untuk upload bukti. Anda bisa upload mulai tanggal ' . $tanggalMulai->format('d/m/Y'));
    }

    if ($today->gt($batasUpload)) {
        return redirect()->back()->with('error', 'Waktu upload bukti sudah berakhir. Batas upload sampai tanggal ' . $batasUpload->format('d/m/Y'));
    }

    // Validasi file
    try {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ], [
            'image.required' => 'File bukti harus diupload.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format file harus JPG, JPEG, atau PNG.',
            'image.max' => 'Ukuran file maksimal 2MB.'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors()]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validasi gagal: ' . $validator->errors()->first());
        }

    } catch (\Exception $e) {
        Log::error('Validation exception', ['error' => $e->getMessage()]);
        return redirect()->back()->with('error', 'Error validasi file: ' . $e->getMessage());
    }

    // Start transaction
    DB::beginTransaction();
    
    try {
        // Pastikan folder ada
        $folderPath = 'izin-bukti';
        if (!Storage::disk('public')->exists($folderPath)) {
            Storage::disk('public')->makeDirectory($folderPath);
            Log::info('Created directory', ['path' => $folderPath]);
        }

        // Hapus gambar lama jika ada
        if ($permintaanIzin->image && Storage::disk('public')->exists($permintaanIzin->image)) {
            Storage::disk('public')->delete($permintaanIzin->image);
            Log::info('Deleted old image', ['path' => $permintaanIzin->image]);
        }

        // Upload gambar baru
        $image = $request->file('image');
        $imageName = 'bukti_izin_' . $permintaanIzin->id . '_' . Auth::id() . '_' . time() . '.' . $image->getClientOriginalExtension();
        
        // Simpan file
        $path = $image->storeAs($folderPath, $imageName, 'public');
        $imagePath = $folderPath . '/' . $imageName;
        
        Log::info('File uploaded to storage', [
            'path' => $path,
            'image_path' => $imagePath,
            'file_exists' => Storage::disk('public')->exists($imagePath),
            'file_size' => Storage::disk('public')->size($imagePath)
        ]);

        // Cek data sebelum update
        $beforeUpdate = [
            'id' => $permintaanIzin->id,
            'current_image' => $permintaanIzin->image,
            'current_bukti_uploaded_at' => $permintaanIzin->bukti_uploaded_at,
            'user_id' => $permintaanIzin->user_id
        ];
        Log::info('Before update', $beforeUpdate);

        // Update database menggunakan method yang lebih eksplisit
        $updateData = [
            'image' => $imagePath,
            'bukti_uploaded_at' => now()
        ];

        // Method 1: Menggunakan model update
        $permintaanIzin->fill($updateData);
        $saveResult = $permintaanIzin->save();
        
        Log::info('Model save result', [
            'result' => $saveResult,
            'dirty_fields' => $permintaanIzin->getDirty(),
            'changes' => $permintaanIzin->getChanges()
        ]);

        // Method 2: Jika method 1 gagal, gunakan query builder
        if (!$saveResult) {
            Log::warning('Model save failed, trying query builder');
            $updateResult = DB::table('permintaan_izins')
                ->where('id', $permintaanIzin->id)
                ->update(array_merge($updateData, ['updated_at' => now()]));
            
            Log::info('Query builder result', ['result' => $updateResult]);
            
            if (!$updateResult) {
                throw new \Exception('Database update failed');
            }
        }

        // Refresh model untuk mendapatkan data terbaru
        $permintaanIzin->refresh();
        
        // Verifikasi data tersimpan
        $afterUpdate = [
            'id' => $permintaanIzin->id,
            'image' => $permintaanIzin->image,
            'bukti_uploaded_at' => $permintaanIzin->bukti_uploaded_at,
            'expected_image' => $imagePath
        ];
        Log::info('After update', $afterUpdate);

        // Verifikasi final
        if ($permintaanIzin->image !== $imagePath) {
            throw new \Exception('Data verification failed - image path mismatch');
        }

        // Log query history
        Log::info('Database queries executed', ['queries' => DB::getQueryLog()]);

        // Commit transaction
        DB::commit();
        
        Log::info('Upload bukti completed successfully', [
            'permission_id' => $permintaanIzin->id,
            'user_id' => Auth::id(),
            'image_path' => $imagePath
        ]);

        return redirect()->back()->with('success', 'Bukti izin berhasil diupload dan tersimpan.');

    } catch (\Exception $e) {
        // Rollback transaction
        DB::rollBack();
        
        // Hapus file yang sudah diupload jika ada error
        if (isset($imagePath) && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
            Log::info('Cleaned up uploaded file after error', ['path' => $imagePath]);
        }
        
        Log::error('Upload bukti error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'permission_id' => $permintaanIzin->id,
            'user_id' => Auth::id()
        ]);

        return redirect()->back()->with('error', 'Terjadi kesalahan saat upload bukti: ' . $e->getMessage());
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