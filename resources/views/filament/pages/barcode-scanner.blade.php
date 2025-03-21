<div>
    <!-- Scanner -->
    <div id="barcode-scanner" style="width: 100%; height: 300px;"></div>
    <input type="hidden" name="barcode_result" id="barcode-result">

    <!-- Tombol "Mulai" -->
    <button id="start-scan-button" style="display: none;">Mulai Scan</button>

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

        /* Gaya untuk tombol "Mulai" */
        #start-scan-button {
            display: none; /* Awalnya disembunyikan */
            padding: 15px 30px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        #start-scan-button:hover {
            background-color: #2563eb;
        }

        /* Sembunyikan tombol "Scan an Image File" */
        #html5-qrcode-select-camera + label[for="html5-qrcode-button-file-selection"] {
            display: none;
        }
    </style>

    <!-- Script untuk scanner -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startButton = document.getElementById('start-scan-button');
            const scannerDiv = document.getElementById('barcode-scanner');
            let scanner = null;

            // Fungsi untuk memulai scan
            function startScan() {
                scanner = new Html5QrcodeScanner("barcode-scanner", {
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
                      });
                });
            }

            // Minta izin kamera
            Html5Qrcode.getCameras()
                .then(devices => {
                    if (devices && devices.length) {
                        // Izin kamera diberikan, tampilkan tombol "Mulai"
                        startButton.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error("Tidak dapat mengakses kamera:", err);
                });

            // Tambahkan event listener untuk tombol "Mulai"
            startButton.addEventListener('click', () => {
                startScan(); // Mulai proses scanning
                startButton.style.display = 'none'; // Sembunyikan tombol setelah diklik
            });
        });
    </script>
</div>