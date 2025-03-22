<div>
    <!-- Scanner -->
    <div id="barcode-scanner" style="width: 100%; height: 300px;"></div>
    <input type="hidden" name="barcode_result" id="barcode-result">

    <!-- Form untuk upload selfie -->
    <div id="selfie-upload-section" style="display: none;">
        <h2>Ambil Selfie</h2>
        <video id="selfie-camera" autoplay></video>
        <canvas id="selfie-canvas" style="display: none;"></canvas>
        
        <!-- Countdown -->
        <div id="countdown" style="display: none;"> <span id="countdown-timer">3</span> </div>
        
        <form id="selfie-upload-form">
            <input type="hidden" name="absensi_id" id="absensi-id">
            <input type="hidden" name="selfie" id="selfie-image">
            <button type="button" id="manual-capture" style="display: none;">Ambil Selfie Manual</button>
        </form>
    </div>

    <!-- Styles -->
    <style>
        #countdown {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 72px;
            color: white;
            background: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 50%;
            text-align: center;
        }
    </style>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scanner = new Html5QrcodeScanner("barcode-scanner", { fps: 10, qrbox: 250 });
            let mediaStream = null;

            scanner.render(decodedText => {
                document.getElementById('barcode-result').value = decodedText;
                scanner.clear();
                Swal.fire({ title: 'Memproses...', icon: 'info', allowOutsideClick: false, showConfirmButton: false, willOpen: () => Swal.showLoading() });
                
                fetch('/handle-scan', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ barcode: decodedText })
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.absensi_id) {
                        document.getElementById('absensi-id').value = data.absensi_id;
                        Swal.fire('Berhasil!', 'Data Barcode Valid', 'success').then(() => {
                            document.getElementById('selfie-upload-section').style.display = 'block';
                            startSelfieCamera();
                        });
                    } else {
                        Swal.fire('Informasi', data.message, 'info').then(() => scanner.render());
                    }
                })
                .catch(() => Swal.fire('Error!', 'Terjadi kesalahan saat memproses barcode', 'error').then(() => scanner.render()));
            });

            function startSelfieCamera() {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
                    .then(stream => {
                        mediaStream = stream;
                        document.getElementById('selfie-camera').srcObject = stream;
                        startCountdown();
                    })
                    .catch(() => Swal.fire('Error!', 'Gagal mengakses kamera.', 'error'));
            }

            function startCountdown() {
                const countdown = document.getElementById('countdown');
                let count = 3;
                countdown.style.display = 'block';
                countdown.innerHTML = count;

                const interval = setInterval(() => {
                    count--;
                    countdown.innerHTML = count;
                    if (count <= 0) {
                        clearInterval(interval);
                        countdown.style.display = 'none';
                        captureSelfie();
                    }
                }, 1000);
            }

            function captureSelfie() {
                const video = document.getElementById('selfie-camera');
                const canvas = document.getElementById('selfie-canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

                Swal.fire({ title: 'Mengupload...', icon: 'info', allowOutsideClick: false, showConfirmButton: false, willOpen: () => Swal.showLoading() });
                canvas.toBlob(blob => {
                    const formData = new FormData();
                    formData.append('absensi_id', document.getElementById('absensi-id').value);
                    formData.append('selfie', new File([blob], "selfie.jpg", { type: "image/jpeg" }));
                    
                    fetch('/upload-selfie', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(() => {
                        mediaStream.getTracks().forEach(track => track.stop());
                        document.getElementById('selfie-upload-section').style.display = 'none';
                        Swal.fire('Berhasil!', 'Selfie berhasil diunggah.', 'success').then(() => scanner.render());
                    })
                    .catch(() => Swal.fire('Error!', 'Gagal mengunggah selfie.', 'error'));
                }, 'image/jpeg', 0.8);
            }
        });
    </script>
</div>
