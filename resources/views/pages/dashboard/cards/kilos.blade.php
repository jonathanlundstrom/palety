<?php

use App\Enumerables\PalletType;
use App\Models\Pallet;
use App\Models\Parcel;
use App\Models\Transport;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {

    #[Locked]
    public int $year;

    /**
     * Get the total packed weight within the selected year (regardless of sent status):
     * - Standalone parcels (no pallet)
     * - Parcels on CALCULATED pallets
     * - MANUAL pallets (own weight field)
     */
    #[Computed]
    public function packedWeight(): float {
        $directParcels = (float) Parcel::query()
            ->whereYear('created_at', $this->year)
            ->whereNull('pallet_id')
            ->sum('weight');

        $calculatedPalletParcels = (float) Parcel::query()
            ->whereYear('created_at', $this->year)
            ->whereHas('pallet', fn ($p) => $p->where('type', PalletType::CALCULATED))
            ->sum('weight');

        $manualPallets = (float) Pallet::query()
            ->whereYear('created_at', $this->year)
            ->where('type', PalletType::MANUAL_PALLET)
            ->sum('weight');

        return $directParcels + $calculatedPalletParcels + $manualPallets;
    }

    /**
     * Get the total sent weight within the selected year:
     * - Standalone parcels on a sent transport
     * - Parcels on CALCULATED pallets on a sent transport
     * - MANUAL pallets on a sent transport
     */
    #[Computed]
    public function sentWeight(): float {
        $sentTransportIds = Transport::query()
            ->whereYear('sent_at', $this->year)
            ->select('id');

        $directParcels = (float) Parcel::query()
            ->whereYear('created_at', $this->year)
            ->whereNull('pallet_id')
            ->whereIn('transport_id', $sentTransportIds)
            ->sum('weight');

        $calculatedPalletParcels = (float) Parcel::query()
            ->whereYear('created_at', $this->year)
            ->whereHas('pallet', fn ($p) => $p
                ->where('type', PalletType::CALCULATED)
                ->whereIn('transport_id', $sentTransportIds)
            )
            ->sum('weight');

        $manualPallets = (float) Pallet::query()
            ->whereYear('created_at', $this->year)
            ->where('type', PalletType::MANUAL_PALLET)
            ->whereIn('transport_id', $sentTransportIds)
            ->sum('weight');

        return $directParcels + $calculatedPalletParcels + $manualPallets;
    }

    /**
     * Get the monthly average packed weight within the selected year.
     */
    #[Computed]
    public function monthlyAverage(): float {
        $months = $this->year === now()->year ? now()->month : 12;

        if ($months === 0 || $this->packedWeight === 0.0) {
            return 0.0;
        }

        return round($this->packedWeight / $months, 1);
    }

}
?>
<flux:card class="col-span-12 overflow-hidden">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="tabular-nums">
                {{ number_format($this->packedWeight) }} {{ __('app.weight.unit') }}
            </flux:heading>

            @if ($this->packedWeight > 0)
                <flux:text class="tabular-nums">
                    {{ __('app.sent_count', [
                        'sent' => number_format($this->sentWeight),
                        'total' => number_format($this->packedWeight),
                    ]) }}
                    ({{ round($this->sentWeight / $this->packedWeight * 100) }}%)
                </flux:text>
            @endif
        </div>

        @if ($this->monthlyAverage > 0)
            <div class="flex items-center gap-1 font-medium text-md text-green-600 dark:text-green-400">
                ~ {{ number_format($this->monthlyAverage) }} {{ __('app.weight.unit') }}/{{ __('app.month_abbr') }}
            </div>
        @endif
    </div>
</flux:card>
