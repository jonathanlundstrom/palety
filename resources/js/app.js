import QrScanner from 'qr-scanner';

window.QrScanner = QrScanner;

Livewire.on('vibrate-success', () => {
    if ('vibrate' in navigator) {
        navigator.vibrate([100, 50, 100]);
    }
});
