<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="your-csrf-token-here">
    <title>Absensi Scanner</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .scanner-overlay {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .scanner-border {
            border: 3px solid #4CAF50;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(76, 175, 80, 0.5);
        }
        
        .countdown-animation {
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0% { transform: translate(-50%, -50%) scale(1); }
            50% { transform: translate(-50%, -50%) scale(1.1); }
            100% { transform: translate(-50%, -50%) scale(1); }
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .status-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .camera-frame {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .scanner-corner {
            position: absolute;
            width: 30px;
            height: 30px;
            border: 3px solid #4CAF50;
        }
        
        .corner-tl { top: 10px; left: 10px; border-right: none; border-bottom: none; }
        .corner-tr { top: 10px; right: 10px; border-left: none; border-bottom: none; }
        .corner-bl { bottom: 10px; left: 10px; border-right: none; border-top: none; }
        .corner-br { bottom: 10px; right: 10px; border-left: none; border-top: none; }
    </style>
</head>
<body class="min-h-screen scanner-overlay">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="glass rounded-2xl p-6 mb-6">
                <h1 class="text-3xl font-bold text-white mb-2">
                    <i class="fas fa-qrcode mr-3"></i>
                    Sistem Absensi
                </h1>
                <p class="text-white/80">Scan QR Code untuk melakukan absensi</p>
            </div>
        </div>

        <!-- Status Card -->
        <div id="status-card" class="glass rounded-2xl p-6 mb-6 text-center" style="display: none;">
            <div class="text-white">
                <i class="fas fa-clock text-4xl mb-3"></i>
                <h3 class="text-xl font-semibold mb-2" id="status-title">Menunggu Scan...</h3>
                <p id="status-message">Arahkan kamera ke QR Code</p>
            </div>
        </div>

        <!-- Scanner Container -->
        <div id="scanner-container" class="relative">
            <div class="glass rounded-2xl p-4 mb-6">
                <div id="barcode-scanner" class="relative rounded-xl overflow-hidden scanner-border">
                    <!-- Scanner corners -->
                    <div class="scanner-corner corner-tl"></div>
                    <div class="scanner-corner corner-tr"></div>
                    <div class="scanner-corner corner-bl"></div>
                    <div class="scanner-corner corner-br"></div>
                </div>
            </div>
            
            <input type="hidden" name="barcode_result" id="barcode-result">
        </div>

        <!-- Camera Container -->
        <div id="camera-container" class="glass rounded-2xl p-4 mb-6" style="display: none;">
            <div class="text-center mb-4">
                <h3 class="text-xl font-bold text-white mb-2">
                    <i class="fas fa-camera mr-2"></i>
                    Ambil Foto Selfie
                </h3>
                <p class="text-white/80">Pastikan wajah Anda terlihat jelas</p>
            </div>
            
            <div class="camera-frame mx-auto" style="max-width: 400px;">
                <video id="camera-view" width="100%" height="100%" autoplay class="rounded-xl"></video>
                
                <!-- Countdown Overlay -->
                <div id="countdown" class="absolute inset-0 flex items-center justify-center" style="display: none;">
                    <div class="countdown-animation bg-red-500 text-white rounded-full w-24 h-24 flex items-center justify-center text-4xl font-bold shadow-2xl">
                        3
                    </div>
                </div>
                
                <!-- Camera overlay guide -->
                <div class="absolute inset-0 pointer-events-none">
                    <div class="w-full h-full border-4 border-white/30 rounded-xl flex items-center justify-center">
                        <div class="w-48 h-60 border-2 border-white/60 rounded-xl relative">
                            <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 text-white text-sm">
                                Posisi Wajah
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <canvas id="camera-canvas" style="display:none;"></canvas>
            
            <div class="text-center mt-4">
                <button id="retake-btn" class="btn-primary text-white px-6 py-3 rounded-xl font-semibold" style="display: none;">
                    <i class="fas fa-redo mr-2"></i>
                    Ambil Ulang
                </button>
            </div>
        </div>

        <!-- Selfie Preview -->
        <div id="selfie-preview" class="glass rounded-2xl p-4 mb-6" style="display: none;">
            <div class="text-center mb-4">
                <h3 class="text-xl font-bold text-white mb-2">
                    <i class="fas fa-check-circle mr-2"></i>
                    Preview Selfie
                </h3>
                <p class="text-white/80">Periksa foto sebelum mengirim</p>
            </div>
            
            <div class="max-w-md mx-auto">
                <img id="selfie-image" class="w-full rounded-xl shadow-lg">
            </div>
            
            <div class="flex gap-4 justify-center mt-6">
                <button id="confirm-selfie" class="btn-primary text-white px-8 py-3 rounded-xl font-semibold">
                    <i class="fas fa-check mr-2"></i>
                    Kirim Absensi
                </button>
                <button id="retake-selfie" class="bg-gray-500 text-white px-8 py-3 rounded-xl font-semibold hover:bg-gray-600 transition-all">
                    <i class="fas fa-camera mr-2"></i>
                    Foto Ulang
                </button>
            </div>
        </div>

        <!-- Instructions -->
        <div class="glass rounded-2xl p-6 text-center">
            <h3 class="text-lg font-bold text-white mb-4">
                <i class="fas fa-info-circle mr-2"></i>
                Petunjuk Penggunaan
            </h3>
            <div class="grid md:grid-cols-3 gap-4 text-white/80">
                <div class="flex flex-col items-center">
                    <i class="fas fa-qrcode text-3xl mb-3"></i>
                    <p class="text-sm">1. Scan QR Code absensi</p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-camera text-3xl mb-3"></i>
                    <p class="text-sm">2. Ambil foto selfie</p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-check text-3xl mb-3"></i>
                    <p class="text-sm">3. Konfirmasi absensi</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let scanner;
            let barcodeData = null;
            let selfieBlob = null;
            let stream = null;

            // Elements
            const statusCard = document.getElementById('status-card');
            const statusTitle = document.getElementById('status-title');
            const statusMessage = document.getElementById('status-message');
            const cameraContainer = document.getElementById('camera-container');
            const cameraView = document.getElementById('camera-view');
            const cameraCanvas = document.getElementById('camera-canvas');
            const selfiePreview = document.getElementById('selfie-preview');
            const selfieImage = document.getElementById('selfie-image');
            const countdownElement = document.getElementById('countdown');
            const confirmSelfieBtn = document.getElementById('confirm-selfie');
            const retakeSelfieBtn = document.getElementById('retake-selfie');

            // Initialize scanner
            function initScanner() {
                statusCard.style.display = 'block';
                
                scanner = new Html5QrcodeScanner("barcode-scanner", {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0,
                    disableFlip: false,
                    rememberLastUsedCamera: true,
                    showTorchButtonIfSupported: true
                });

                scanner.render(onScanSuccess, onScanFailure);
                
                // Hide file selection option
                setTimeout(() => {
                    const fileSelectionContainer = document.querySelector('#barcode-scanner section div:nth-child(2)');
                    if (fileSelectionContainer) {
                        fileSelectionContainer.style.display = 'none';
                    }
                }, 1000);
            }

            function onScanSuccess(decodedText) {
                document.getElementById('barcode-result').value = decodedText;
                scanner.clear();
                barcodeData = decodedText;
                
                updateStatus('QR Code Terdeteksi!', 'Menyiapkan kamera untuk selfie...', 'success');
                
                setTimeout(() => {
                    document.getElementById('scanner-container').style.display = 'none';
                    startCamera();
                }, 2000);
            }

            function onScanFailure(error) {
                // Handle scan failure silently
            }

            function updateStatus(title, message, type = 'info') {
                statusTitle.textContent = title;
                statusMessage.textContent = message;
                
                const icon = statusCard.querySelector('i');
                icon.className = `fas text-4xl mb-3 ${
                    type === 'success' ? 'fa-check-circle' : 
                    type === 'error' ? 'fa-times-circle' : 'fa-clock'
                }`;
            }

            async function startCamera() {
                try {
                    updateStatus('Mengakses Kamera...', 'Mohon izinkan akses kamera');
                    
                    stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { 
                            facingMode: "user",
                            width: { ideal: 640 },
                            height: { ideal: 480 }
                        } 
                    });
                    
                    cameraView.srcObject = stream;
                    cameraContainer.style.display = 'block';
                    
                    updateStatus('Kamera Siap', 'Bersiap untuk mengambil foto...', 'success');
                    
                    setTimeout(() => {
                        startCountdown();
                    }, 2000);
                    
                } catch (err) {
                    updateStatus('Error Kamera', 'Gagal mengakses kamera', 'error');
                    Swal.fire({
                        title: 'Error!',
                        text: 'Gagal mengakses kamera. Pastikan izin kamera sudah diberikan.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }

            function startCountdown() {
                updateStatus('Foto Otomatis', 'Bersiap dalam 3 detik...');
                countdownElement.style.display = 'flex';
                let count = 3;
                
                const countInterval = setInterval(() => {
                    count--;
                    countdownElement.querySelector('div').textContent = count;
                    
                    if (count <= 0) {
                        clearInterval(countInterval);
                        countdownElement.style.display = 'none';
                        capturePhoto();
                    }
                }, 1000);
            }

            function capturePhoto() {
                const context = cameraCanvas.getContext('2d');
                cameraCanvas.width = cameraView.videoWidth;
                cameraCanvas.height = cameraView.videoHeight;
                context.drawImage(cameraView, 0, 0, cameraCanvas.width, cameraCanvas.height);
                
                cameraCanvas.toBlob((blob) => {
                    selfieBlob = blob;
                    const imageUrl = URL.createObjectURL(blob);
                    selfieImage.src = imageUrl;
                    
                    // Show preview
                    cameraContainer.style.display = 'none';
                    selfiePreview.style.display = 'block';
                    
                    updateStatus('Foto Berhasil', 'Periksa dan konfirmasi foto Anda', 'success');
                    
                }, 'image/jpeg', 0.8);
            }

            function submitData() {
                updateStatus('Mengirim Data...', 'Mohon tunggu sebentar');
                
                Swal.fire({
                    title: 'Mengirim Absensi...',
                    html: '<div class="spinner-border text-primary" role="status"></div>',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                const formData = new FormData();
                formData.append('barcode', barcodeData);
                formData.append('selfie', selfieBlob, 'selfie.jpg');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                fetch('/handle-scan', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: data.status === 'terlambat' ? 'Absensi Terlambat!' : 'Berhasil!',
                        text: data.message,
                        icon: data.status === 'terlambat' ? 'warning' : 'success',
                        confirmButtonText: 'OK',
                        timer: 5000,
                        timerProgressBar: true
                    }).then(() => {
                        resetUI();
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan: ' + error.message,
                        icon: 'error',
                        confirmButtonText: 'Coba Lagi'
                    });
                    updateStatus('Error', 'Gagal mengirim data', 'error');
                });
            }

            function resetUI() {
                // Stop camera
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
                
                // Reset UI
                selfiePreview.style.display = 'none';
                cameraContainer.style.display = 'none';
                document.getElementById('scanner-container').style.display = 'block';
                
                // Reset data
                barcodeData = null;
                selfieBlob = null;
                
                // Restart scanner
                updateStatus('Menunggu Scan...', 'Arahkan kamera ke QR Code');
                initScanner();
            }

            // Event listeners
            confirmSelfieBtn.addEventListener('click', submitData);
            retakeSelfieBtn.addEventListener('click', () => {
                selfiePreview.style.display = 'none';
                startCamera();
            });

            // Initialize
            initScanner();
        });
    </script>
</body>
</html>