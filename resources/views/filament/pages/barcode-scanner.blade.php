<div>
    <div id="barcode-scanner" style="width: 100%; height: 300px;"></div>
    <input type="hidden" name="barcode_result" id="barcode-result">
    
    <div class="mt-4">
        <h3 class="text-lg font-medium">Ambil Selfie</h3>
        <div id="camera-container" class="my-2" style="width: 320px; height: 240px; border: 1px solid #ccc; display: none;">
            <video id="camera-view" width="100%" height="100%" autoplay></video>
            <canvas id="camera-canvas" style="display:none;"></canvas>
        </div>
        <div id="selfie-preview" class="my-2" style="width: 320px; height: 240px; border: 1px solid #ccc; display: none;">
            <img id="selfie-image" width="100%" height="100%">
        </div>
        <div class="mt-2">
            <button type="button" id="start-camera" class="px-4 py-2 bg-blue-500 text-white rounded">Buka Kamera</button>
            <button type="button" id="capture-photo" class="px-4 py-2 bg-green-500 text-white rounded" style="display: none;">Ambil Foto</button>
            <button type="button" id="retake-photo" class="px-4 py-2 bg-yellow-500 text-white rounded" style="display: none;">Ambil Ulang</button>
        </div>
    </div>
</div>

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

        // Barcode scan handler
        scanner.render((decodedText) => {
            document.getElementById('barcode-result').value = decodedText;
            scanner.clear();
            barcodeData = decodedText;
            
            // Show camera button after successful scan
            startCameraBtn.style.display = 'inline-block';
            alert('QR code terbaca. Silakan ambil selfie untuk melanjutkan.');
        });

        // Start camera button handler
        startCameraBtn.addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: "user" } 
                });
                cameraView.srcObject = stream;
                cameraContainer.style.display = 'block';
                startCameraBtn.style.display = 'none';
                capturePhotoBtn.style.display = 'inline-block';
            } catch (err) {
                alert('Error mengakses kamera: ' + err);
            }
        });

        // Capture photo button handler
        capturePhotoBtn.addEventListener('click', () => {
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
                capturePhotoBtn.style.display = 'none';
                retakePhotoBtn.style.display = 'inline-block';
                
                // If both barcode and selfie are ready, submit data
                if (barcodeData && selfieBlob) {
                    submitData();
                }
            }, 'image/jpeg', 0.8);
        });

        // Retake photo button handler
        retakePhotoBtn.addEventListener('click', () => {
            selfiePreview.style.display = 'none';
            cameraContainer.style.display = 'block';
            retakePhotoBtn.style.display = 'none';
            capturePhotoBtn.style.display = 'inline-block';
        });

        // Function to submit data to server
        function submitData() {
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
                alert(data.message);
                
                // Reset UI
                selfiePreview.style.display = 'none';
                retakePhotoBtn.style.display = 'none';
                startCameraBtn.style.display = 'inline-block';
                
                // Stop camera stream
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }
                
                // Reset data
                barcodeData = null;
                selfieBlob = null;
                
                // Restart scanner
                scanner.render((decodedText) => {
                    document.getElementById('barcode-result').value = decodedText;
                    scanner.clear();
                    barcodeData = decodedText;
                    startCameraBtn.style.display = 'inline-block';
                    alert('QR code terbaca. Silakan ambil selfie untuk melanjutkan.');
                });
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }
    });
</script>