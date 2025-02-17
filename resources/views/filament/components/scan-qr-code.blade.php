@extends('filament::layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center min-h-screen">
    <h1 class="text-2xl font-bold mb-4">Scan Barcode untuk Absensi</h1>
    <input type="text" id="barcode-input" class="border p-2 rounded" placeholder="Scan barcode di sini" autofocus>
    <p id="status-message" class="mt-4"></p>
</div>

<script>
document.getElementById('barcode-input').addEventListener('change', function () {
    let barcode = this.value;
    fetch('{{ route("scanBarcode") }}', {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": '{{ csrf_token() }}'
        },
        body: JSON.stringify({ barcode: barcode })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('status-message').innerText = data.message;
    })
    .catch(error => {
        document.getElementById('status-message').innerText = "Terjadi kesalahan!";
    });

    this.value = "";
});
</script>
@endsection