<div class="card">
    <div class="card-header">
        <h3 class="card-title">QR Code Scanner</h3>
    </div>
    <div class="scanner-container">
        <div id="qr-reader"></div>
        <div id="scanner-result"></div>
        
        <div style="margin-top: 2rem;">
            <h4>Manual QR Code Entry</h4>
            <form method="POST" action="" style="margin-top: 1rem;">
                <div class="form-group">
                    <input type="text" class="form-control" name="qr_data" placeholder="Enter QR code data manually" required>
                </div>
                <button type="submit" name="scan_qr" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize QR scanner only once
if (document.getElementById('qr-reader') && typeof Html5QrcodeScanner !== 'undefined') {
    let html5QrcodeScanner = new Html5QrcodeScanner(
        "qr-reader", 
        { 
            fps: 10, 
            qrbox: {width: 250, height: 250},
            aspectRatio: 1.0,
            showTorchButtonIfSupported: true
        },
        false
    );

    function onScanSuccess(decodedText, decodedResult) {
        // Handle successful scan
        document.getElementById('scanner-result').innerHTML = 
            '<div class="scanner-result success">✅ QR Code detected: ' + decodedText + '</div>';
        
        // Submit the form automatically
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'qr_data';
        input.value = decodedText;
        
        const submitInput = document.createElement('input');
        submitInput.type = 'hidden';
        submitInput.name = 'scan_qr';
        submitInput.value = '1';
        
        form.appendChild(input);
        form.appendChild(submitInput);
        document.body.appendChild(form);
        
        // Show processing message
        document.getElementById('scanner-result').innerHTML += 
            '<div class="scanner-result info">⏳ Processing...</div>';
        
        form.submit();
    }

    function onScanFailure(error) {
        // Handle scan failure silently
    }

    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
}
</script>
