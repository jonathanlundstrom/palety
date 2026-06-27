<?php

use Livewire\Component;

new class extends Component {
    /*
     * Callback when scanning QR-codes. Parses data and emits for continued handling.
     * @param string $data
     * @return void
     */
    public function handleScan(string $data): void {
        if (str_contains($data, ':')) {
            list($class, $id) = explode(':', $data);
            $this->dispatch('scan-result', payload: [
                'class' => $class,
                'id' => $id,
            ]);
        } else {
            $this->dispatch('scan-result', payload: null);
        }
    }
}

?>
<flux:modal name="scanner-modal" x-data="qrScanner" class="w-xs sm:w-10/12 md:w-128" x-on:scan.window="startScanning()" x-on:close="stopScanning()">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.scan.title')  }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.scan.subtitle')  }}</flux:text>
        </div>

        <div class="rounded-lg overflow-hidden w-full">
            <div x-show="scanning">
                <video class="camera_preview"></video>
            </div>

            <flux:skeleton animate="shimmer" class="aspect-[16/9] size-full" x-show="!scanning"/>
        </div>

        <flux:modal.close class="flex-1">
            <flux:button icon="check" class="w-full">{{ __('app.scan.finish') }}</flux:button>
        </flux:modal.close>
    </div>
</flux:modal>

@script
<script>
    Alpine.data('qrScanner', () => ({
        result: '',
        scanner: null,
        scanning: false,
        hasFlash: false,
        flashOn: false,
        video: $el.querySelector('.camera_preview'),

        async startScanning() {
            this.result = '';

            if (this.scanner === null) {
                this.scanner = new QrScanner(
                    this.video,
                    this.handleScan.bind(this),
                    {returnDetailedScanResult: true}
                );
            }

            await this.scanner.setCamera('environment');

            this.scanner.start()
                .then(async () => {
                    this.scanning = true;
                })
                .catch(err => {
                    console.error('Scanner error:', err);
                    this.$wire.error(result.data);
                });
        },

        handleScan(result) {
            if (result.data !== this.result) {
                this.result = result.data;
                this.$wire.handleScan(result.data);
            }
        },

        stopScanning() {
            this.scanner?.stop();
            this.scanner?.destroy();
            this.scanner = null;
            this.scanning = false;
        },

        destroy() {
            this.stopScanning();
        }
    }));
</script>
@endscript
