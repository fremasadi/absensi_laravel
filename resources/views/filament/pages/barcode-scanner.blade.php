<div>
    <!-- Scanner -->
    <div id="barcode-scanner" style="width: 100%; height: 300px;"></div>
    <input type="hidden" name="barcode_result" id="barcode-result">

    <!-- Form untuk upload selfie (awalnya disembunyikan) -->
    <div id="selfie-upload-section" style="display: none;">
        <h2>Ambil Selfie</h2>
        <video id="selfie-camera" autoplay style="width: 100%; height: auto;"></video>
        <canvas id="selfie-canvas" style="display: none;"></canvas>
        
        <!-- Tambahkan elemen countdown -->
        <div id="countdown" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 72px; color: white; background-color: rgba(0,0,0,0.5); padding: 20px; border-radius: 50%; width: 100px; height: 100px; text-align: center; line-height: 100px;">
            <span id="countdown-timer">3</span>
        </div>
        
        <form id="selfie-upload-form">
            <input type="hidden" name="absensi_id" id="absensi-id">
            <input type="hidden" name="selfie" id="selfie-image">
            <button type="button" id="manual-capture" style="display: none;">Ambil Selfie Manual</button>
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
        
        /* Styling untuk countdown */
        #countdown {
            z-index: 1000;
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

                // Kirim data ke backend
                fetch('/handle-scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        barcode: decodedText
                    }),
                }).then(response => response.json())
                  .then(data => {
                      // Jika absensi masuk berhasil, tampilkan form selfie
                      if (data.absensi_id) {
                          document.getElementById('absensi-id').value = data.absensi_id;
                          document.getElementById('selfie-upload-section').style.display = 'block';
                          startSelfieCamera();
                      } else {
                          alert(data.message);
                      }
                  })
                  .catch(error => {
                      console.error('Error:', error);
                      alert('Terjadi kesalahan saat memproses barcode');
                  });
            });

            // Variabel untuk menyimpan stream kamera
            let mediaStream = null;

            // Fungsi untuk memulai kamera selfie dan auto capture setelah 3 detik
            function startSelfieCamera() {
                const video = document.getElementById('selfie-camera');
                const canvas = document.getElementById('selfie-canvas');
                const selfieImage = document.getElementById('selfie-image');
                const countdownElement = document.getElementById('countdown');
                const countdownTimer = document.getElementById('countdown-timer');
                const manualCaptureBtn = document.getElementById('manual-capture');

                navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
                    .then((stream) => {
                        mediaStream = stream;
                        video.srcObject = stream;
                        
                        // Tunggu video diload
                        video.onloadedmetadata = function() {
                            countdownElement.style.display = 'block'; // Tampilkan countdown

                            let count = 3;
                            countdownTimer.textContent = count;
                            
                            const countdownInterval = setInterval(() => {
                                count--;
                                countdownTimer.textContent = count;

                                if (count <= 0) {
                                    clearInterval(countdownInterval);
                                    countdownElement.style.display = 'none'; // Sembunyikan countdown

                                    // Ambil gambar setelah countdown selesai
                                    captureSelfie();
                                }
                            }, 1000); // Update countdown setiap 1 detik
                        };
                    })
                    .catch((err) => {
                        console.error('Error accessing camera:', err);
                        alert('Gagal mengakses kamera. Pastikan kamera sudah diaktifkan dan izin diberikan.');
                    });

                // Tombol untuk capture manual (cadangan jika auto tidak berfungsi)
                manualCaptureBtn.addEventListener('click', () => {
                    captureSelfie();
                });
            }

            // Fungsi untuk mengambil selfie dan upload
            function captureSelfie() {
                const video = document.getElementById('selfie-camera');
                const canvas = document.getElementById('selfie-canvas');
                const selfieImage = document.getElementById('selfie-image');
                
                // Pastikan video sudah siap
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    // Set ukuran canvas sama dengan video
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    
                    // Gambar dari video ke canvas
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    
                    // Konversi canvas ke blob (untuk upload file)
                    canvas.toBlob(function(blob) {
                        // Buat file dari blob
                        const selfieFile = new File([blob], "selfie.jpg", { type: "image/jpeg" });
                        
                        // Buat FormData untuk upload
                        const formData = new FormData();
                        formData.append('absensi_id', document.getElementById('absensi-id').value);
                        formData.append('selfie', selfieFile);
                        
                        // Upload selfie ke server
                        fetch('/upload-selfie', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: formData,
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Hentikan kamera setelah selesai
                            if (mediaStream) {
                                mediaStream.getTracks().forEach(track => track.stop());
                            }
                            
                            // Sembunyikan bagian selfie
                            document.getElementById('selfie-upload-section').style.display = 'none';
                            
                            // Tampilkan pesan sukses
                            alert(data.message);
                            
                            // Reinisialisasi scanner
                            scanner.render();
                        })
                        .catch(error => {
                            console.error('Error uploading selfie:', error);
                            alert('Gagal mengunggah selfie. Silakan coba lagi.');
                            // Tampilkan tombol capture manual sebagai backup
                            document.getElementById('manual-capture').style.display = 'block';
                        });
                    }, 'image/jpeg', 0.8); // Format JPEG dengan kualitas 80%
                } else {
                    alert('Kamera belum siap. Silakan tunggu sebentar atau gunakan tombol manual.');
                    document.getElementById('manual-capture').style.display = 'block';
                }
            }
        });
    </script>
</div>