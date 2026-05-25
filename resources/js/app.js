import QrScanner from 'qr-scanner';

window.QrScanner = QrScanner;

Livewire.on('vibrate-success', () => {
    navigator.vibrate([100, 50, 100]);
});
