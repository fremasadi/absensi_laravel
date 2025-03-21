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
            <button type="button" id="manual-capture" style="display: none;" class="btn btn-primary mt-2">Ambil Selfie Manual</button>
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
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 72px;
            color: white;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            text-align: center;
            line-height: 100px;
        }
    
        /* Styling tambahan untuk tampilan yang lebih rapi */
        #selfie-upload-section {
            position: relative;
            width: 100%; /* Full width */
            height: 300px; /* Sesuaikan dengan tinggi scanner barcode */
            margin: 0 auto;
            padding: 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    
        #selfie-upload-section h2 {
            color: #343a40;
            text-align: center;
            margin-bottom: 15px;
        }
    
        #selfie-camera {
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            object-fit: cover; /* Pastikan video mengisi area tanpa distorsi */
        }
    
        #manual-capture {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 0px;
        }
    
        #manual-capture:hover {
            background-color: #0069d9;
        }
    </style>
    <!-- SweetAlert2 library untuk dialog yang lebih menarik -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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

            // Tampilkan loading
            Swal.fire({
                title: 'Memproses',
                text: 'Sedang memproses data absensi...',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

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
                  Swal.close(); // Tutup loading

                  // Jika absensi masuk berhasil, tampilkan form selfie
                  if (data.absensi_id) {
                      document.getElementById('absensi-id').value = data.absensi_id;

                      Swal.fire({
                          title: 'Berhasil!',
                          text: 'Data Barcode Valid',
                          icon: 'success',
                          timer: 2000,
                          showConfirmButton: false
                      }).then(() => {
                          document.getElementById('selfie-upload-section').style.display = 'block';
                          startSelfieCamera();
                      });
                  } else {
                      Swal.fire({
                          title: 'Informasi',
                          text: data.message,
                          icon: 'info',
                          confirmButtonText: 'OK',
                          confirmButtonColor: '#3085d6'
                      }).then(() => {
                          // Reinisialisasi scanner
                          scanner.render();
                      });
                  }
              })
              .catch(error => {
                  console.error('Error:', error);
                  Swal.fire({
                      title: 'Error!',
                      text: 'Terjadi kesalahan saat memproses barcode',
                      icon: 'error',
                      confirmButtonText: 'Coba Lagi',
                      confirmButtonColor: '#3085d6'
                  }).then(() => {
                      // Reinisialisasi scanner
                      scanner.render();
                  });
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
                    Swal.fire({
                        title: 'Error!',
                        text: 'Gagal mengakses kamera. Pastikan kamera sudah diaktifkan dan izin diberikan.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6'
                    });
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

                // Tampilkan loading
                Swal.fire({
                    title: 'Mengupload',
                    text: 'Sedang mengupload selfie...',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

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
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            // Reinisialisasi scanner
                            scanner.render();
                        });
                    })
                    .catch(error => {
                        console.error('Error uploading selfie:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal mengunggah selfie. Silakan coba lagi.',
                            icon: 'error',
                            confirmButtonText: 'Coba Lagi',
                            confirmButtonColor: '#3085d6'
                        });
                        // Tampilkan tombol capture manual sebagai backup
                        document.getElementById('manual-capture').style.display = 'block';
                    });
                }, 'image/jpeg', 0.8); // Format JPEG dengan kualitas 80%
            } else {
                Swal.fire({
                    title: 'Perhatian',
                    text: 'Kamera belum siap. Silakan tunggu sebentar atau gunakan tombol manual.',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6'
                });
                document.getElementById('manual-capture').style.display = 'block';
            }
        }
    });
</script>
</div>