<div>
    <div id="barcode-scanner" style="width: 100%; height: 300px;"></div>
    <input type="hidden" name="barcode_result" id="barcode-result">
    
    <div class="mt-4">
        <h3 class="text-lg font-medium">Ambil Selfie</h3>
        <div id="camera-container" class="my-2" style="width: 320px; height: 240px; border: 1px solid #ccc; display: none; position: relative;">
            <video id="camera-view" width="100%" height="100%" autoplay></video>
            <div id="countdown" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 72px; color: white; text-shadow: 2px 2px 4px #000; display: none;">3</div>
            <canvas id="camera-canvas" style="display:none;"></canvas>
        </div>
        <div id="selfie-preview" class="my-2" style="width: 320px; height: 240px; border: 1px solid #ccc; display: none;">
            <img id="selfie-image" width="100%" height="100%">
        </div>
        <div class="mt-2">
            <button type="button" id="start-camera" class="px-4 py-2 bg-blue-500 text-white rounded" style="display: none;">Buka Kamera</button>
            <button type="button" id="capture-photo" class="px-4 py-2 bg-green-500 text-white rounded" style="display: none;">Ambil Foto</button>
            <button type="button" id="retake-photo" class="px-4 py-2 bg-yellow-500 text-white rounded" style="display: none;">Ambil Ulang</button>
            <div id="status-message" class="mt-2 text-gray-600"></div>
        </div>
    </div>
</div>

<style>
    /* General styling */
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f4f9;
        color: #333;
    }

    /* Barcode scanner container */
    #barcode-scanner {
        background-color: #000;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        margin-bottom: 20px;
    }

    /* Camera container */
    #camera-container {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        background-color: #000;
        position: relative;
    }

    /* Selfie preview container */
    #selfie-preview {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        background-color: #000;
    }

    /* Buttons styling */
    button {
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    #start-camera {
        background: linear-gradient(135deg, #6a11cb, #2575fc);
        color: white;
    }

    #capture-photo {
        background: linear-gradient(135deg, #4caf50, #81c784);
        color: white;
    }

    #retake-photo {
        background: linear-gradient(135deg, #ff9800, #ffc107);
        color: white;
    }

    button:hover {
        opacity: 0.9;
    }

    /* Countdown styling */
    #countdown {
        font-size: 72px;
        color: white;
        text-shadow: 2px 2px 4px #000;
        animation: pulse 1s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    /* Status message styling */
    #status-message {
        font-size: 14px;
        color: #666;
        margin-top: 10px;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        #camera-container, #selfie-preview {
            width: 100%;
            height: auto;
        }

        button {
            width: 100%;
            margin-bottom: 10px;
        }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Barcode scanner setup
        const scanner = new Html5QrcodeScanner("barcode-scanner", {
            fps: 10,
            qrbox: 250
        });

        let barcodeData = null;
        let selfieBlob = null;
        let stream = null;

        // Camera elements
        const cameraContainer = document.getElementById('camera-container');
        const cameraView = document.getElementById('camera-view');
        const cameraCanvas = document.getElementById('camera-canvas');
        const selfiePreview = document.getElementById('selfie-preview');
        const selfieImage = document.getElementById('selfie-image');
        const startCameraBtn = document.getElementById('start-camera');
        const capturePhotoBtn = document.getElementById('capture-photo');
        const retakePhotoBtn = document.getElementById('retake-photo');
        const countdownElement = document.getElementById('countdown');
        const statusMessage = document.getElementById('status-message');

        // Start camera function
        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: "user" } 
                });
                cameraView.srcObject = stream;
                cameraContainer.style.display = 'block';
                statusMessage.textContent = 'Kamera siap. Harap tunggu 3 detik untuk foto otomatis...';

                // Start countdown after camera is ready
                setTimeout(() => {
                    startCountdown();
                }, 1000);
            } catch (err) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Gagal mengakses kamera: ' + err,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                statusMessage.textContent = 'Gagal mengakses kamera. Silakan refresh halaman.';
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
                retakePhotoBtn.style.display = 'inline-block';
                statusMessage.textContent = 'Selfie diambil. Mengirim data...';
                
                // If both barcode and selfie are ready, submit data
                if (barcodeData && selfieBlob) {
                    submitData();
                }
            }, 'image/jpeg', 0.8);
        }

        // Barcode scan handler
        scanner.render((decodedText) => {
            document.getElementById('barcode-result').value = decodedText;
            scanner.clear();
            barcodeData = decodedText;
            
            // Show status message
            statusMessage.textContent = 'QR code terbaca. Menyiapkan kamera...';
            
            // Hide scanner after successful scan
            const scannerElement = document.getElementById('barcode-scanner');
            scannerElement.style.display = 'none';
            
            // Start camera automatically
            startCamera();
        });

        // Retake photo button handler
        retakePhotoBtn.addEventListener('click', () => {
            selfiePreview.style.display = 'none';
            statusMessage.textContent = 'Mengambil ulang selfie. Menyiapkan kamera...';
            startCamera();
        });

        // Function to submit data to server
        function submitData() {
            statusMessage.textContent = 'Mengirim data ke server...';

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
                // Show success message
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });

                // Reset UI
                selfiePreview.style.display = 'none';
                retakePhotoBtn.style.display = 'none';

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

                    // Show status message
                    statusMessage.textContent = 'QR code terbaca. Menyiapkan kamera...';

                    // Hide scanner after successful scan
                    scannerElement.style.display = 'none';

                    // Start camera automatically
                    startCamera();
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
    });
</script>