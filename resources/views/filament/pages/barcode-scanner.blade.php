<div>
    <!-- Scanner -->
    <div id="barcode-scanner" style="width: 100%; height: 300px;"></div>
    <input type="hidden" name="barcode_result" id="barcode-result">

    <!-- Form untuk upload selfie (awalnya disembunyikan) -->
    <div id="selfie-upload-section" style="display: none;">
        <h2>Ambil Selfie</h2>
        <video id="selfie-camera" autoplay style="width: 100%; height: auto;"></video>
        <button id="capture-selfie">Ambil Foto</button>
        <canvas id="selfie-canvas" style="display: none;"></canvas>
        <form id="selfie-upload-form">
            <input type="hidden" name="absensi_id" id="absensi-id">
            <input type="hidden" name="selfie" id="selfie-image">
            <button type="submit">Upload Selfie</button>
        </form>
    </div>

    <!-- CSS untuk menyembunyikan "Scan an Image File" -->
    <style>
        /* Sembunyikan tombol "Scan an Image File" */
        #html5-qrcode-select-camera + label[for="html5-qrcode-button-file-selection"] {
            display: none;
        }

        /* Sembunyikan input file */
        #html5-qrcode-button-file-selection {
            display: none;
        }
    </style>

    <!-- Script untuk scanner dan selfie -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scanner = new Html5QrcodeScanner("barcode-scanner", {
                fps: 10,
                qrbox: 250,
                supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA] // Hanya izinkan scan dari kamera
            });

            scanner.render((decodedText) => {
                document.getElementById('barcode-result').value = decodedText;
                scanner.clear();

                // Parse data barcode
                const [userId, idJadwal, scanTime] = decodedText.split('|');

                // Kirim data ke backend
                fetch('/handle-scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        barcode: decodedText,
                        id_jadwal: idJadwal, // Kirim id_jadwal
                    }),
                }).then(response => response.json())
                  .then(data => {
                      alert(data.message);

                      // Jika absensi masuk berhasil, tampilkan form selfie
                      if (data.absensi_id) {
                          document.getElementById('absensi-id').value = data.absensi_id;
                          document.getElementById('selfie-upload-section').style.display = 'block';
                          startSelfieCamera();
                      }
                  });
            });

            // Fungsi untuk memulai kamera selfie
            function startSelfieCamera() {
                const video = document.getElementById('selfie-camera');
                const canvas = document.getElementById('selfie-canvas');
                const captureButton = document.getElementById('capture-selfie');
                const selfieImage = document.getElementById('selfie-image');

                navigator.mediaDevices.getUserMedia({ video: true })
                    .then((stream) => {
                        video.srcObject = stream;
                    })
                    .catch((err) => {
                        console.error('Error accessing camera:', err);
                    });

                // Tangkap foto dari kamera
                captureButton.addEventListener('click', () => {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

                    // Konversi canvas ke data URL (format base64)
                    const imageData = canvas.toDataURL('image/jpeg');
                    selfieImage.value = imageData;
                });

                // Kirim selfie ke backend
                document.getElementById('selfie-upload-form').addEventListener('submit', (e) => {
                    e.preventDefault();

                    const formData = new FormData(e.target);

                    fetch('/upload-selfie', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: formData,
                    }).then(response => response.json())
                      .then(data => {
                          alert(data.message);
                      });
                });
            }
        });
    </script>
</div>