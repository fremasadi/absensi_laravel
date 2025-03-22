<div>
    <div id="barcode-scanner" class="w-full h-72"></div>
    <input type="hidden" name="barcode_result" id="barcode-result">
    
    <div class="mt-4">
        <div id="camera-container" class="my-2 w-80 h-60 border border-gray-300 hidden relative">
            <video id="camera-view" class="w-full h-full" autoplay></video>
            <div id="countdown" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-6xl text-white font-bold hidden">3</div>
            <canvas id="camera-canvas" class="hidden"></canvas>
        </div>
        <div id="selfie-preview" class="my-2 w-80 h-60 border border-gray-300 hidden">
            <img id="selfie-image" class="w-full h-full">
        </div>
        <div class="mt-2">
            <button type="button" id="start-camera" class="px-4 py-2 bg-blue-500 text-white rounded hidden">Buka Kamera</button>
            <button type="button" id="capture-photo" class="px-4 py-2 bg-green-500 text-white rounded hidden">Ambil Foto</button>
            <button type="button" id="retake-photo" class="px-4 py-2 bg-yellow-500 text-white rounded hidden">Ambil Ulang</button>
            <div id="status-message" class="mt-2 text-gray-600"></div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scanner = new Html5QrcodeScanner("barcode-scanner", { fps: 10, qrbox: 250 });
        let barcodeData = null;
        let selfieBlob = null;
        let stream = null;

        const cameraContainer = document.getElementById('camera-container');
        const cameraView = document.getElementById('camera-view');
        const cameraCanvas = document.getElementById('camera-canvas');
        const selfiePreview = document.getElementById('selfie-preview');
        const selfieImage = document.getElementById('selfie-image');
        const retakePhotoBtn = document.getElementById('retake-photo');
        const countdownElement = document.getElementById('countdown');
        const statusMessage = document.getElementById('status-message');

        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
                cameraView.srcObject = stream;
                cameraContainer.classList.remove('hidden');
                statusMessage.textContent = 'Kamera siap. Harap tunggu 3 detik untuk foto otomatis...';
                setTimeout(startCountdown, 1000);
            } catch (err) {
                Swal.fire('Error', 'Gagal mengakses kamera: ' + err, 'error');
            }
        }

        function startCountdown() {
            countdownElement.classList.remove('hidden');
            let count = 3;
            countdownElement.textContent = count;
            const countInterval = setInterval(() => {
                count--;
                countdownElement.textContent = count;
                if (count <= 0) {
                    clearInterval(countInterval);
                    countdownElement.classList.add('hidden');
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
                selfiePreview.classList.remove('hidden');
                cameraContainer.classList.add('hidden');
                retakePhotoBtn.classList.remove('hidden');
                statusMessage.textContent = 'Selfie diambil. Mengirim data...';
                if (barcodeData && selfieBlob) submitData();
            }, 'image/jpeg', 0.8);
        }

        scanner.render((decodedText) => {
            document.getElementById('barcode-result').value = decodedText;
            scanner.clear();
            barcodeData = decodedText;
            statusMessage.textContent = 'QR code terbaca. Menyiapkan kamera...';
            document.getElementById('barcode-scanner').classList.add('hidden');
            startCamera();
        });

        retakePhotoBtn.addEventListener('click', () => {
            selfiePreview.classList.add('hidden');
            statusMessage.textContent = 'Mengambil ulang selfie. Menyiapkan kamera...';
            startCamera();
        });

        function submitData() {
            statusMessage.textContent = 'Mengirim data ke server...';
            const formData = new FormData();
            formData.append('barcode', barcodeData);
            formData.append('selfie', selfieBlob, 'selfie.jpg');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            const [userId, idJadwal, scanTime] = barcodeData.split('|');
            formData.append('id_jadwal', idJadwal);

            fetch('/handle-scan', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                Swal.fire('Sukses', data.message, 'success');
                selfiePreview.classList.add('hidden');
                retakePhotoBtn.classList.add('hidden');
                if (stream) stream.getTracks().forEach(track => track.stop());
                barcodeData = null;
                selfieBlob = null;
                document.getElementById('barcode-scanner').classList.remove('hidden');
                scanner.render((decodedText) => {
                    document.getElementById('barcode-result').value = decodedText;
                    scanner.clear();
                    barcodeData = decodedText;
                    statusMessage.textContent = 'QR code terbaca. Menyiapkan kamera...';
                    document.getElementById('barcode-scanner').classList.add('hidden');
                    startCamera();
                });
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan: ' + error, 'error');
            });
        }
    });
</script>
