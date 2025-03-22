<div id="scanner-container">
    <div id="barcode-scanner" style="width: 100%; height: 100vh;"></div>
    <input type="hidden" name="barcode_result" id="barcode-result">
    
    <div class="mt-4">
        <div id="camera-container" class="my-2" style="width: 100%; height: 100vh; border: 1px solid #ccc; display: none; position: relative;">
            <video id="camera-view" width="100%" height="100%" autoplay></video>
            <div id="countdown" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 72px; color: white; text-shadow: 2px 2px 4px #000; display: none;">3</div>
            <canvas id="camera-canvas" style="display:none;"></canvas>
        </div>
        <div id="selfie-preview" class="my-2" style="width: 100%; max-width: 500px; height: auto; border: 1px solid #ccc; margin: 0 auto; display: none;">
            <img id="selfie-image" width="100%" height="100%">
        </div>
    </div>
    <style>
        /* CSS untuk menyembunyikan tombol Scan an Image File */
        .scan-title, 
        a[href="#scan-image"],
        a:contains("Scan an Image File") {
            display: none; /* Menyembunyikan tombol dan teks "Request Camera Permissions" */
        }
    </style>

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
                    
                    // Tampilkan SweetAlert untuk memberi tahu pengguna
                    Swal.fire({
                        title: 'QR Code Terdeteksi!',
                        text: 'Menyiapkan kamera untuk selfie...',
                        icon: 'success',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        // Hide scanner after successful scan
                        const scannerElement = document.getElementById('barcode-scanner');
                        scannerElement.style.display = 'none';
                        
                        // Start camera automatically
                        startCamera();
                    });
                });

                // Start camera function
                async function startCamera() {
                    try {
                        // Langsung meminta izin kamera (tanpa perlu scan image)
                        stream = await navigator.mediaDevices.getUserMedia({ 
                            video: { facingMode: "user" } 
                        });
                        cameraView.srcObject = stream;
                        cameraContainer.style.display = 'block';
                        
                        // Tampilkan SweetAlert untuk instruksi
                        Swal.fire({
                            title: 'Kamera Siap',
                            text: 'Harap tunggu 3 detik untuk foto otomatis...',
                            icon: 'info',
                            timer: 1000,
                            showConfirmButton: false,
                            timerProgressBar: true
                        }).then(() => {
                            // Start countdown after camera is ready
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
                    
                    // Convert canvas to blob/file
                    cameraCanvas.toBlob((blob) => {
                        selfieBlob = blob;
                        const imageUrl = URL.createObjectURL(blob);
                        selfieImage.src = imageUrl;
                        
                        // Show preview and hide camera
                        selfiePreview.style.display = 'block';
                        cameraContainer.style.display = 'none';
                        
                        // Tampilkan SweetAlert untuk konfirmasi selfie
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
                                // If user confirmed, submit data
                                submitData();
                            } else {
                                // If user wants to retake
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
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                    
                    // Parse data barcode for additional info
                    const [userId, idJadwal, scanTime] = barcodeData.split('|');
                    formData.append('id_jadwal', idJadwal);

                    // Submit the data
                    fetch('/handle-scan', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Show success message with SweetAlert
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Reset UI
                            selfiePreview.style.display = 'none';
                            
                            // Stop camera stream
                            if (stream) {
                                stream.getTracks().forEach(track => track.stop());
                            }
                            
                            // Reset data
                            barcodeData = null;
                            selfieBlob = null;
                            
                            // Show scanner again
                            const scannerElement = document.getElementById('barcode-scanner');
                            scannerElement.style.display = 'block';
                            
                            // Restart scanner
                            scanner.render((decodedText) => {
                                document.getElementById('barcode-result').value = decodedText;
                                scanner.clear();
                                barcodeData = decodedText;
                                
                                // Tampilkan SweetAlert
                                Swal.fire({
                                    title: 'QR Code Terdeteksi!',
                                    text: 'Menyiapkan kamera untuk selfie...',
                                    icon: 'success',
                                    timer: 2000,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Hide scanner after successful scan
                                    scannerElement.style.display = 'none';
                                    
                                    // Start camera automatically
                                    startCamera();
                                });
                            });
                            
                            // Sembunyikan teks scan image lagi
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
                        });
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan: ' + error,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            }
        });
    </script>
</div>