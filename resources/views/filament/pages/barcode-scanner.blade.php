<div>
    <div id="barcode-scanner" style="width: 100%; height: 300px;"></div>
    <input type="hidden" name="barcode_result" id="barcode-result"> <!-- Ganti $getName() dengan nama field -->
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
              });
        });
    });
</script>