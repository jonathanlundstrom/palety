<?php

use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Component;

new class extends Component {
    /**
     * Supported label formats. Each must capture a `type` (morph alias, case-insensitive) and an `id`.
     * - url: "https://palety.test/qr/pallet/13". The host is ignored, so labels from other environments still scan.
     * - legacy: "App\Models\Pallet:13". The class basename maps to its morph alias.
     */
    private const array PATTERNS = [
        'url' => '#^https?://[^/\s]+/qr/(?<type>[a-z]+)/(?<id>\d+)/?$#i',
        'legacy' => '#^App\\\\Models\\\\(?<type>[A-Za-z]+):(?<id>\d+)$#',
    ];

    /**
     * Callback when scanning QR-codes. Parses data and emits for continued handling.
     */
    public function handleScan(string $data): void {
        $this->dispatch('scan-result', payload: $this->parse($data));
    }

    /**
     * Parse scanned data using the first matching label format, or null if unrecognized.
     *
     * @return array{class: string, id: int}|null
     */
    private function parse(string $data): ?array {
        foreach (self::PATTERNS as $pattern) {
            if (preg_match($pattern, trim($data), $matches)) {
                return $this->resolve($matches['type'], (int) $matches['id']);
            }
        }

        return null;
    }

    /**
     * Resolve a morph alias to its model class, or null if the alias is not mapped.
     *
     * @return array{class: class-string, id: int}|null
     */
    private function resolve(string $type, int $id): ?array {
        $class = Relation::getMorphedModel(strtolower($type));
        return $class ? ['class' => $class, 'id' => $id] : null;
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

            <flux:skeleton animate="shimmer" class="aspect-[16/9] size-full" x-show="!scanning && !failed"/>

            <flux:callout variant="danger" icon="video-camera-slash" x-show="failed"
                          :heading="__('app.scan.camera_error')"
                          :text="__('app.scan.camera_error_hint')"/>
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
        failed: false,
        hasFlash: false,
        flashOn: false,
        video: $el.querySelector('.camera_preview'),

        async startScanning() {
            this.result = '';
            this.failed = false;

            if (this.scanner === null) {
                this.scanner = new QrScanner(
                    this.video,
                    this.handleScan.bind(this),
                    {returnDetailedScanResult: true}
                );
            }

            try {
                await this.scanner.setCamera('environment');
                await this.scanner.start();
                this.scanning = true;
            } catch (err) {
                console.error('Scanner error:', err);
                this.failed = true;
            }
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
