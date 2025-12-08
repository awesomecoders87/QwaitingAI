

document.addEventListener('livewire:init', () => {
    console.log('laravel init 2');

    Livewire.on('print-qr-code', (response) => {
        var printContents = document.getElementById('qrCodeSection').innerHTML;

        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
        var printDocument = iframe.contentWindow.document;
    
        printDocument.write('<html><head><title>Print QR Code</title>');
        printDocument.write('<style>@media print { body * { visibility: hidden; } #qrCodeSection, #qrCodeSection * { visibility: visible; } #qrCodeSection { position: absolute; left: 0; top: 0; width: 100%; } }</style>');
        printDocument.write('</head><body>');
        printDocument.write('<div id="qrCodeSection">' + printContents + '</div>');
        printDocument.write('</body></html>');
        printDocument.close();
    
        iframe.contentWindow.print();
    
        setTimeout(function() {
            document.body.removeChild(iframe);
        }, 1000); 
    });
});


