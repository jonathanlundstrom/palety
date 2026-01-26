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

        // Flux::modal('scanner-modal')->close();
    }
}

?>
<flux:modal name="scanner-modal" x-data="qrScanner" class="md:w-128" x-on:scan.window="startScanning()" x-on:close="stopScanning()">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.scan.title')  }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.scan.subtitle')  }}</flux:text>
        </div>

        <div x-show="scanning">
            <div class="rounded-lg overflow-hidden">
                <video class="camera_preview w-full"></video>
            </div>
        </div>

        <flux:skeleton animate="shimmer" class="aspect-[16/9] size-full rounded-lg" x-show="!scanning"/>

        <div class="flex gap-6">
            <flux:button x-on:click="toggleFlash()" x-show="hasFlash" icon="bolt" class="flex-1">{{ __('app.scan.toggle_flash') }}</flux:button>

            <flux:modal.close class="flex-1">
                <flux:button icon="check" class="w-full">Finish scanning</flux:button>
            </flux:modal.close>
        </div>
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
                    this.hasFlash = await this.scanner.hasFlash();
                    if (this.hasFlash) {
                        await this.scanner.turnFlashOff();
                        this.flashOn = false;
                    }
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

        async toggleFlash() {
            if (this.hasFlash) {
                await this.scanner.toggleFlash();
                this.flashOn = this.scanner.isFlashOn();
            }
        },

        destroy() {
            this.stopScanning();
        }
    }));
</script>
@endscript
