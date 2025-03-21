<div>
    <div id="barcode-scanner" style="width: 100%; height: 300px;"></div>
    <input type="hidden" name="barcode_result" id="barcode-result">
    <!-- Tambahkan tombol untuk menghentikan scanner -->
    <button id="stop-scanner" class="btn btn-danger mt-2">Hentikan Scanner</button>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let scannerInstance = null;
        
        // Inisialisasi scanner
        const scanner = new Html5QrcodeScanner("barcode-scanner", {
            fps: 10,
            qrbox: 250
        });

        // Render scanner
        scanner.render((decodedText) => {
            document.getElementById('barcode-result').value = decodedText;
            
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
                    id_jadwal: idJadwal,
                }),
            }).then(response => response.json())
              .then(data => {
                  alert(data.message);
              });
              
            // Simpan instance scanner
            scannerInstance = scanner;
        });

        // Tambahkan event listener untuk tombol stop
        document.getElementById('stop-scanner').addEventListener('click', function() {
            if (scannerInstance) {
                scannerInstance.clear(); // Menghentikan scanner
                document.getElementById('barcode-scanner').innerHTML = '<p>Scanner telah dihentikan</p>';
                this.disabled = true; // Menonaktifkan tombol setelah scanner dihentikan
            }
        });
    });
</script>