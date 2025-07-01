<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner Absensi</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        #scanner-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        #camera-container {
            position: relative;
            background: #000;
        }
        #countdown {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 72px;
            color: white;
            text-shadow: 2px 2px 4px #000;
            font-weight: bold;
        }
        #selfie-preview {
            background: white;
            padding: 20px;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin: 5px 0;
        }
        .status-success {
            background-color: #d4edda;
            color: #155724;
        }
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="scanner-container">
            <div id="barcode-scanner" style="width: 100%; height: 100vh;"></div>
            <input type="hidden" name="barcode_result" id="barcode-result">
            
            <div class="mt-4">
                <div id="camera-container" class="my-2" style="width: 100%; height: 100vh; border: 1px solid #ccc; display: none; position: relative;">
                    <video id="camera-view" width="100%" height="100%" autoplay></video>
                    <div id="countdown" style="display: none;">3</div>
                    <canvas id="camera-canvas" style="display:none;"></canvas>
                </div>
                <div id="selfie-preview" class="my-2" style="width: 100%; max-width: 500px; height: auto; border: 1px solid #ccc; margin: 0 auto; display: none;">
                    <img id="selfie-image" width="100%" height="100%">
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            initScanner();
            
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
                `;
                document.head.appendChild(style);
                
                // Barcode scanner setup
                const scanner = new Html5QrcodeScanner("barcode-scanner", {
                    fps: 10,
                    qrbox: { width: 300, height: 300 },
                    aspectRatio: window.innerWidth / window.innerHeight,
                    disableFlip: false,
                    rememberLastUsedCamera: true,
                    showTorchButtonIfSupported: true
                });

                // Hide unwanted elements after scanner renders
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

                let barcodeData = null;
                let selfieBlob = null;
                let stream = null;

                // Camera elements
                const cameraContainer = document.getElementById('camera-container');
                const cameraView = document.getElementById('camera-view');
                const cameraCanvas = document.getElementById('camera-canvas');
                const selfiePreview = document.getElementById('selfie-preview');
                const selfieImage = document.getElementById('selfie-image');
                const countdownElement = document.getElementById('countdown');

                // Barcode scan handler
                scanner.render((decodedText) => {
                    document.getElementById('barcode-result').value = decodedText;
                    scanner.clear();
                    barcodeData = decodedText;
                    
                    Swal.fire({
                        title: 'QR Code Terdeteksi!',
                        text: 'Menyiapkan kamera untuk selfie...',
                        icon: 'success',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        const scannerElement = document.getElementById('barcode-scanner');
                        scannerElement.style.display = 'none';
                        startCamera();
                    });
                });

                // Start camera function
                async function startCamera() {
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({ 
                            video: { facingMode: "user" } 
                        });
                        cameraView.srcObject = stream;
                        cameraContainer.style.display = 'block';
                        
                        Swal.fire({
                            title: 'Kamera Siap',
                            text: 'Harap tunggu 3 detik untuk foto otomatis...',
                            icon: 'info',
                            timer: 1000,
                            showConfirmButton: false,
                            timerProgressBar: true
                        }).then(() => {
                            startCountdown();
                        });
                    } catch (err) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal mengakses kamera: ' + err,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                }

                // Countdown function
                function startCountdown() {
                    countdownElement.style.display = 'block';
                    let count = 3;
                    countdownElement.textContent = count;
                    
                    const countInterval = setInterval(() => {
                        count--;
                        countdownElement.textContent = count;
                        
                        if (count <= 0) {
                            clearInterval(countInterval);
                            countdownElement.style.display = 'none';
                            capturePhoto();
                        }
                    }, 1000);
                }

                // Capture photo function
                function capturePhoto() {
                    const context = cameraCanvas.getContext('2d');
                    cameraCanvas.width = cameraView.videoWidth;
                    cameraCanvas.height = cameraView.videoHeight;
                    context.drawImage(cameraView, 0, 0, cameraCanvas.width, cameraCanvas.height);
                    
                    cameraCanvas.toBlob((blob) => {
                        selfieBlob = blob;
                        const imageUrl = URL.createObjectURL(blob);
                        selfieImage.src = imageUrl;
                        
                        selfiePreview.style.display = 'block';
                        cameraContainer.style.display = 'none';
                        
                        Swal.fire({
                            title: 'Selfie Diambil',
                            text: 'Apakah Anda ingin menggunakan selfie ini?',
                            imageUrl: imageUrl,
                            imageWidth: 300,
                            imageHeight: 225,
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Kirim',
                            cancelButtonText: 'Ambil Ulang',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                submitData();
                            } else {
                                selfiePreview.style.display = 'none';
                                startCamera();
                            }
                        });
                    }, 'image/jpeg', 0.8);
                }

                // Function to submit data to server
                function submitData() {
                    Swal.fire({
                        title: 'Sedang Mengirim...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    const formData = new FormData();
                    formData.append('barcode', barcodeData);
                    formData.append('selfie', selfieBlob, 'selfie.jpg');
                    
                    // Add CSRF token if available
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) {
                        formData.append('_token', csrfToken.content);
                    }
                    
                    // Parse data barcode for additional info
                    const [userId, shiftId, scanTime] = barcodeData.split('|');
                    formData.append('id_jadwal', shiftId);

                    // Submit the data
                    fetch('/handle-scan', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Handle different response scenarios based on controller logic
                        if (data.message) {
                            let alertConfig = {
                                confirmButtonText: 'OK'
                            };

                            // Determine alert type and content based on response
                            if (data.waktu_masuk) {
                                // Absensi masuk berhasil
                                alertConfig.title = data.status === 'terlambat' ? 'Absensi Masuk (Terlambat)' : 'Absensi Masuk Berhasil';
                                alertConfig.icon = data.status === 'terlambat' ? 'warning' : 'success';
                                alertConfig.html = `
                                    <div class="status-badge ${data.status === 'terlambat' ? 'status-warning' : 'status-success'}">
                                        ${data.status.toUpperCase()}
                                    </div>
                                    <p><strong>Waktu Masuk:</strong> ${data.waktu_masuk}</p>
                                    <p><strong>Waktu Shift:</strong> ${data.waktu_shift}</p>
                                    <p><strong>Keterangan:</strong> ${data.keterangan}</p>
                                `;
                            } else if (data.waktu_keluar) {
                                // Absensi keluar berhasil
                                alertConfig.title = 'Absensi Keluar Berhasil';
                                alertConfig.icon = 'success';
                                alertConfig.html = `
                                    <div class="status-badge status-success">KELUAR</div>
                                    <p><strong>Waktu Keluar:</strong> ${data.waktu_keluar}</p>
                                    <p><strong>Durasi Hadir:</strong> ${data.durasi_hadir}</p>
                                `;
                            } else {
                                // Error messages from controller
                                alertConfig.title = 'Perhatian';
                                alertConfig.icon = 'error';
                                alertConfig.text = data.message;
                            }

                            Swal.fire(alertConfig).then(() => {
                                // Reset UI only if successful
                                if (data.waktu_masuk || data.waktu_keluar) {
                                    resetUI();
                                    restartScanner();
                                } else {
                                    // For errors, just reset to scanner
                                    resetUI();
                                    restartScanner();
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan koneksi: ' + error.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            resetUI();
                            restartScanner();
                        });
                    });
                }

                // Function to reset UI
                function resetUI() {
                    selfiePreview.style.display = 'none';
                    
                    // Stop camera stream
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                        stream = null;
                    }
                    
                    // Reset data
                    barcodeData = null;
                    selfieBlob = null;
                }

                // Function to restart scanner
                function restartScanner() {
                    const scannerElement = document.getElementById('barcode-scanner');
                    scannerElement.style.display = 'block';
                    
                    // Restart scanner
                    scanner.render((decodedText) => {
                        document.getElementById('barcode-result').value = decodedText;
                        scanner.clear();
                        barcodeData = decodedText;
                        
                        Swal.fire({
                            title: 'QR Code Terdeteksi!',
                            text: 'Menyiapkan kamera untuk selfie...',
                            icon: 'success',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            scannerElement.style.display = 'none';
                            startCamera();
                        });
                    });
                    
                    // Hide unwanted elements again
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
                }
            }
        });
    </script>
</body>
</html>