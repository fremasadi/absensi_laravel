<!-- Satu div container untuk semua elemen (Livewire requirement) -->
<div id="scanner-container">
    <!-- Barcode Scanner -->
    <div id="barcode-scanner" style="width: 100%; height: 100vh;"></div>
    <input type="hidden" name="barcode_result" id="barcode-result">
    
    <!-- Camera dan Preview Container -->
    <div class="mt-4">
        <!-- Camera Container -->
        <div id="camera-container" class="my-2" style="width: 100%; height: 100vh; border: 1px solid #ccc; display: none; position: relative;">
            <video id="camera-view" width="100%" height="100%" autoplay></video>
            <div id="countdown" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 72px; color: white; text-shadow: 2px 2px 4px #000; display: none;">3</div>
            <canvas id="camera-canvas" style="display:none;"></canvas>
        </div>
        
        <!-- Selfie Preview -->
        <div id="selfie-preview" class="my-2" style="width: 100%; max-width: 500px; height: auto; border: 1px solid #ccc; margin: 0 auto; display: none;">
            <img id="selfie-image" width="100%" height="100%">
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Pastikan SweetAlert2 dan html5-qrcode sudah dimuat
            if (typeof Swal === 'undefined') {
                // Tambahkan SweetAlert2 jika belum dimuat
                const sweetAlertCss = document.createElement('link');
                sweetAlertCss.rel = 'stylesheet';
                sweetAlertCss.href = 'https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.css';
                document.head.appendChild(sweetAlertCss);
                
                const sweetAlertScript = document.createElement('script');
                sweetAlertScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.js';
                document.head.appendChild(sweetAlertScript);
            }
            
            if (typeof Html5QrcodeScanner === 'undefined') {
                // Tambahkan html5-qrcode jika belum dimuat
                const qrCodeScript = document.createElement('script');
                qrCodeScript.src = 'https://unpkg.com/html5-qrcode';
                qrCodeScript.onload = initScanner;
                document.head.appendChild(qrCodeScript);
            } else {
                initScanner();
            }
            
            function initScanner() {
                // Style untuk menyembunyikan teks scan image
                const style = document.createElement('style');
                style.textContent = `
                    #barcode-scanner section div:first-child span {
                        display: none !important;
                    }
                    #barcode-scanner section div button span {
                        display: none !important;
                    }
                    #barcode-scanner section div button::before {
                        content: "Scan QR Code";
                    }
                    #barcode-scanner section div:nth-child(2) {
                        display: none !important;
                    }
                    .status-info {
                        background: #f8f9fa;
                        border: 1px solid #dee2e6;
                        border-radius: 8px;
                        padding: 15px;
                        margin: 10px 0;
                        font-size: 14px;
                    }
                    .status-info.success {
                        background: #d4edda;
                        border-color: #c3e6cb;
                        color: #155724;
                    }
                    .status-info.warning {
                        background: #fff3cd;
                        border-color: #ffeaa7;
                        color: #856404;
                    }
                    .status-info.error {
                        background: #f8d7da;
                        border-color: #f5c6cb;
                        color: #721c24;
                    }
                `;
                document.head.appendChild(style);
                
                // Barcode scanner setup dengan lebar dan tinggi penuh
                const scanner = new Html5QrcodeScanner("barcode-scanner", {
                    fps: 10,
                    qrbox: { width: 300, height: 300 },
                    aspectRatio: window.innerWidth / window.innerHeight,
                    disableFlip: false,
                    rememberLastUsedCamera: true,
                    showTorchButtonIfSupported: true
                });

                // Mengganti teks tombol setelah scanner dirender
                setTimeout(() => {
                    const qrBoxElements = document.querySelectorAll('#barcode-scanner section div');
                    if (qrBoxElements.length > 0) {
                        // Sembunyikan teks yang tidak diinginkan
                        const fileSelectionContainer = document.querySelector('#barcode-scanner section div:nth-child(2)');
                        if (fileSelectionContainer) {
                            fileSelectionContainer.style.display = 'none';
                        }
                        
                        // Hapus atau ganti teks lainnya
                        const headerTexts = document.querySelectorAll('#barcode-scanner section div span');
                        headerTexts.forEach(span => {
                            if (span.textContent.includes('Scan')) {
                                span.style.display = 'none';
                            }
                        });
                    }
                }, 500);

                let barcodeData = null;
                let selfieBlob = null;
                let stream = null;
                let isProcessing = false; // Flag untuk mencegah multiple submission

                // Camera elements
                const cameraContainer = document.getElementById('camera-container');
                const cameraView = document.getElementById('camera-view');
                const cameraCanvas = document.getElementById('camera-canvas');
                const selfiePreview = document.getElementById('selfie-preview');
                const selfieImage = document.getElementById('selfie-image');
                const countdownElement = document.getElementById('countdown');

                // Fungsi untuk validasi format barcode
                function validateBarcodeFormat(barcodeText) {
                    const parts = barcodeText.split('|');
                    if (parts.length !== 3) {
                        return { valid: false, message: 'Format QR Code tidak valid. Format yang benar: userId|shiftId|scanTime' };
                    }
                    
                    const [userId, shiftId, scanTime] = parts;
                    
                    // Validasi userId dan shiftId adalah angka
                    if (!userId || isNaN(userId)) {
                        return { valid: false, message: 'User ID tidak valid dalam QR Code' };
                    }
                    
                    if (!shiftId || isNaN(shiftId)) {
                        return { valid: false, message: 'Shift ID tidak valid dalam QR Code' };
                    }
                    
                    // Validasi format waktu
                    if (!scanTime || !Date.parse(scanTime)) {
                        return { valid: false, message: 'Format waktu tidak valid dalam QR Code' };
                    }
                    
                    return { valid: true, userId, shiftId, scanTime };
                }

                // Fungsi untuk menampilkan informasi shift
                function displayShiftInfo(userId, shiftId, scanTime) {
                    const currentTime = new Date().toLocaleTimeString('id-ID', { 
                        timeZone: 'Asia/Jakarta',
                        hour12: false 
                    });
                    
                    return `
                        <div class="status-info">
                            <strong>Informasi Scan:</strong><br>
                            User ID: ${userId}<br>
                            Shift ID: ${shiftId}<br>
                            Waktu Scan: ${new Date(scanTime).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })}<br>
                            Waktu Sekarang: ${currentTime}
                        </div>
                    `;
                }

                // Barcode scan handler dengan validasi yang ditingkatkan
                scanner.render((decodedText) => {
                    if (isProcessing) return; // Mencegah scan ganda
                    
                    document.getElementById('barcode-result').value = decodedText;
                    
                    // Validasi format barcode
                    const validation = validateBarcodeFormat(decodedText);
                    if (!validation.valid) {
                        Swal.fire({
                            title: 'QR Code Tidak Valid',
                            text: validation.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                    
                    scanner.clear();
                    barcodeData = decodedText;
                    
                    // Tampilkan informasi shift dan konfirmasi
                    const shiftInfo = displayShiftInfo(validation.userId, validation.shiftId, validation.scanTime);
                    
                    Swal.fire({
                        title: 'QR Code Terdeteksi!',
                        html: shiftInfo + '<p>Lanjutkan untuk mengambil selfie?</p>',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan',
                        cancelButtonText: 'Batal',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Hide scanner after successful scan
                            const scannerElement = document.getElementById('barcode-scanner');
                            scannerElement.style.display = 'none';
                            
                            // Start camera automatically
                            startCamera();
                        } else {
                            // Reset jika dibatalkan
                            barcodeData = null;
                        }
                    });
                });

                // Start camera function dengan error handling yang lebih baik
                async function startCamera() {
                    try {
                        // Hentikan stream yang sudah ada jika ada
                        if (stream) {
                            stream.getTracks().forEach(track => track.stop());
                        }
                        
                        // Minta izin kamera dengan preferensi front camera
                        const constraints = {
                            video: { 
                                facingMode: "user",
                                width: { ideal: 640 },
                                height: { ideal: 480 }
                            }
                        };
                        
                        stream = await navigator.mediaDevices.getUserMedia(constraints);
                        cameraView.srcObject = stream;
                        cameraContainer.style.display = 'block';
                        
                        // Tunggu sampai video ready
                        cameraView.onloadedmetadata = () => {
                            Swal.fire({
                                title: 'Kamera Siap',
                                text: 'Posisikan wajah Anda dengan baik. Foto akan diambil otomatis dalam 3 detik...',
                                icon: 'info',
                                timer: 2000,
                                showConfirmButton: false,
                                timerProgressBar: true,
                                allowOutsideClick: false
                            }).then(() => {
                                startCountdown();
                            });
                        };
                        
                    } catch (err) {
                        console.error('Camera error:', err);
                        let errorMessage = 'Gagal mengakses kamera.';
                        
                        if (err.name === 'NotAllowedError') {
                            errorMessage = 'Akses kamera ditolak. Silakan izinkan akses kamera dan coba lagi.';
                        } else if (err.name === 'NotFoundError') {
                            errorMessage = 'Kamera tidak ditemukan pada perangkat ini.';
                        } else if (err.name === 'NotReadableError') {
                            errorMessage = 'Kamera sedang digunakan oleh aplikasi lain.';
                        }
                        
                        Swal.fire({
                            title: 'Error Kamera',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Kembali ke scanner
                            resetToScanner();
                        });
                    }
                }

                // Countdown function dengan visual yang lebih baik
                function startCountdown() {
                    countdownElement.style.display = 'block';
                    countdownElement.style.fontSize = '3rem';
                    countdownElement.style.fontWeight = 'bold';
                    countdownElement.style.color = '#007bff';
                    countdownElement.style.textAlign = 'center';
                    countdownElement.style.textShadow = '2px 2px 4px rgba(0,0,0,0.5)';
                    
                    let count = 3;
                    countdownElement.textContent = count;
                    
                    const countInterval = setInterval(() => {
                        count--;
                        if (count > 0) {
                            countdownElement.textContent = count;
                            // Efek animasi
                            countdownElement.style.transform = 'scale(1.2)';
                            setTimeout(() => {
                                countdownElement.style.transform = 'scale(1)';
                            }, 200);
                        } else {
                            countdownElement.textContent = 'FOTO!';
                            countdownElement.style.color = '#28a745';
                            setTimeout(() => {
                                clearInterval(countInterval);
                                countdownElement.style.display = 'none';
                                capturePhoto();
                            }, 500);
                        }
                    }, 1000);
                }

                // Capture photo function dengan kualitas yang ditingkatkan
                function capturePhoto() {
                    const context = cameraCanvas.getContext('2d');
                    
                    // Set canvas size berdasarkan video
                    cameraCanvas.width = cameraView.videoWidth || 640;
                    cameraCanvas.height = cameraView.videoHeight || 480;
                    
                    // Gambar video ke canvas
                    context.drawImage(cameraView, 0, 0, cameraCanvas.width, cameraCanvas.height);
                    
                    // Convert canvas to blob dengan kualitas tinggi
                    cameraCanvas.toBlob((blob) => {
                        if (!blob) {
                            Swal.fire({
                                title: 'Error',
                                text: 'Gagal mengambil foto. Silakan coba lagi.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                startCamera();
                            });
                            return;
                        }
                        
                        selfieBlob = blob;
                        const imageUrl = URL.createObjectURL(blob);
                        selfieImage.src = imageUrl;
                        
                        // Show preview and hide camera
                        selfiePreview.style.display = 'block';
                        cameraContainer.style.display = 'none';
                        
                        // Stop camera stream
                        if (stream) {
                            stream.getTracks().forEach(track => track.stop());
                        }
                        
                        // Tampilkan konfirmasi selfie dengan preview yang lebih besar
                        Swal.fire({
                            title: 'Konfirmasi Selfie',
                            text: 'Apakah foto ini sudah sesuai?',
                            imageUrl: imageUrl,
                            imageWidth: 300,
                            imageHeight: 225,
                            imageAlt: 'Preview Selfie',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Kirim Data',
                            cancelButtonText: 'Ambil Ulang',
                            allowOutsideClick: false,
                            customClass: {
                                confirmButton: 'btn btn-success',
                                cancelButton: 'btn btn-warning'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                submitData();
                            } else {
                                selfiePreview.style.display = 'none';
                                URL.revokeObjectURL(imageUrl); // Cleanup
                                startCamera();
                            }
                        });
                    }, 'image/jpeg', 0.9); // Kualitas tinggi
                }

                // Function to submit data dengan handling response yang lebih detail
                function submitData() {
                    if (isProcessing) return; // Mencegah double submission
                    isProcessing = true;
                    
                    Swal.fire({
                        title: 'Mengirim Data Absensi',
                        html: 'Mohon tunggu, sedang memproses...<br><div class="progress mt-2"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div></div>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    const formData = new FormData();
                    formData.append('barcode', barcodeData);
                    formData.append('selfie', selfieBlob, 'selfie.jpg');
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                    
                    // Parse data barcode untuk informasi tambahan
                    const [userId, shiftId, scanTime] = barcodeData.split('|');
                    formData.append('id_jadwal', shiftId);

                    // Submit data dengan timeout
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 detik timeout

                    fetch('/handle-scan', {
                        method: 'POST',
                        body: formData,
                        signal: controller.signal
                    })
                    .then(response => {
                        clearTimeout(timeoutId);
                        return response.json().then(data => ({ status: response.status, data }));
                    })
                    .then(({ status, data }) => {
                        isProcessing = false;
                        
                        if (status === 200) {
                            // Sukses - tampilkan detail berdasarkan response
                            let successMessage = data.message;
                            let successDetail = '';
                            
                            if (data.waktu_masuk) {
                                successDetail = `
                                    <div class="status-info success">
                                        <strong>Detail Absensi Masuk:</strong><br>
                                        Waktu Masuk: ${data.waktu_masuk}<br>
                                        Waktu Shift: ${data.waktu_shift}<br>
                                        Status: ${data.status || 'Hadir'}<br>
                                        ${data.keterangan ? `Keterangan: ${data.keterangan}<br>` : ''}
                                    </div>
                                `;
                            } else if (data.waktu_keluar) {
                                successDetail = `
                                    <div class="status-info success">
                                        <strong>Detail Absensi Keluar:</strong><br>
                                        Waktu Keluar: ${data.waktu_keluar}<br>
                                        Durasi Hadir: ${data.durasi_hadir}<br>
                                    </div>
                                `;
                            }
                            
                            Swal.fire({
                                title: 'Absensi Berhasil!',
                                html: successMessage + successDetail,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false
                            }).then(() => {
                                resetToScanner();
                            });
                            
                        } else {
                            // Error dari server
                            const errorClass = status >= 500 ? 'error' : 'warning';
                            Swal.fire({
                                title: status >= 500 ? 'Error Server' : 'Peringatan',
                                html: `<div class="status-info ${errorClass}">${data.message || 'Terjadi kesalahan tidak dikenal'}</div>`,
                                icon: status >= 500 ? 'error' : 'warning',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false
                            }).then(() => {
                                resetToScanner();
                            });
                        }
                    })
                    .catch(error => {
                        clearTimeout(timeoutId);
                        isProcessing = false;
                        
                        console.error('Submission error:', error);
                        
                        let errorMessage = 'Terjadi kesalahan saat mengirim data.';
                        if (error.name === 'AbortError') {
                            errorMessage = 'Koneksi timeout. Periksa koneksi internet Anda.';
                        } else if (error.message.includes('Failed to fetch')) {
                            errorMessage = 'Gagal terhubung ke server. Periksa koneksi internet Anda.';
                        }
                        
                        Swal.fire({
                            title: 'Error Koneksi',
                            html: `<div class="status-info error">${errorMessage}</div>`,
                            icon: 'error',
                            showCancelButton: true,
                            confirmButtonText: 'Coba Lagi',
                            cancelButtonText: 'Kembali',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                submitData(); // Coba lagi
                            } else {
                                resetToScanner();
                            }
                        });
                    });
                }

                // Fungsi untuk reset ke scanner
                function resetToScanner() {
                    // Reset semua data
                    barcodeData = null;
                    selfieBlob = null;
                    isProcessing = false;
                    
                    // Hide preview dan camera
                    selfiePreview.style.display = 'none';
                    cameraContainer.style.display = 'none';
                    
                    // Stop camera stream jika masih aktif
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                        stream = null;
                    }
                    
                    // Show scanner lagi
                    const scannerElement = document.getElementById('barcode-scanner');
                    scannerElement.style.display = 'block';
                    
                    // Restart scanner
                    setTimeout(() => {
                        scanner.render((decodedText) => {
                            if (isProcessing) return;
                            
                            document.getElementById('barcode-result').value = decodedText;
                            
                            const validation = validateBarcodeFormat(decodedText);
                            if (!validation.valid) {
                                Swal.fire({
                                    title: 'QR Code Tidak Valid',
                                    text: validation.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                                return;
                            }
                            
                            scanner.clear();
                            barcodeData = decodedText;
                            
                            const shiftInfo = displayShiftInfo(validation.userId, validation.shiftId, validation.scanTime);
                            
                            Swal.fire({
                                title: 'QR Code Terdeteksi!',
                                html: shiftInfo + '<p>Lanjutkan untuk mengambil selfie?</p>',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Lanjutkan',
                                cancelButtonText: 'Batal',
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    scannerElement.style.display = 'none';
                                    startCamera();
                                } else {
                                    barcodeData = null;
                                }
                            });
                        });
                        
                        // Hide unwanted elements
                        setTimeout(() => {
                            const fileSelectionContainer = document.querySelector('#barcode-scanner section div:nth-child(2)');
                            if (fileSelectionContainer) {
                                fileSelectionContainer.style.display = 'none';
                            }
                            
                            const headerTexts = document.querySelectorAll('#barcode-scanner section div span');
                            headerTexts.forEach(span => {
                                if (span.textContent.includes('Scan')) {
                                    span.style.display = 'none';
                                }
                            });
                        }, 500);
                    }, 100);
                }
            }
        });
    </script>
</div>