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
     * Get the total number of transports within the selected year.
     */
    #[Computed]
    public function totalCount(): int {
        return Transport::query()
            ->whereYear('created_at', $this->year)
            ->count();
    }

    /**
     * Get the total weight loaded across transports within the selected year.
     * Mirrors Transport::getWeight() but aggregated across all transports for the year.
     */
    #[Computed]
    public function totalWeight(): float {
        $transportIds = Transport::query()
            ->whereYear('created_at', $this->year)
            ->select('id');

        $directParcels = (float) Parcel::query()
            ->whereIn('transport_id', $transportIds)
            ->whereNull('pallet_id')
            ->sum('weight');

        $calculatedPalletParcels = (float) Parcel::query()
            ->whereHas('pallet', fn ($p) => $p
                ->where('type', PalletType::CALCULATED)
                ->whereIn('transport_id', $transportIds)
            )
            ->sum('weight');

        $manualPallets = (float) Pallet::query()
            ->where('type', PalletType::MANUAL_PALLET)
            ->whereIn('transport_id', $transportIds)
            ->sum('weight');

        return $directParcels + $calculatedPalletParcels + $manualPallets;
    }

    /**
     * Get the total number of sent transports within the selected year.
     */
    #[Computed]
    public function sentCount(): int {
        return Transport::query()
            ->whereYear('created_at', $this->year)
            ->whereNotNull('sent_at')
            ->count();
    }

    /**
     * Generate monthly trend for plotting the data.
     */
    #[Computed]
    public function monthlyTrend(): array {
        $counts = Transport::query()
            ->whereYear('created_at', $this->year)
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int AS month, COUNT(*) AS count')
            ->groupByRaw('EXTRACT(MONTH FROM created_at)')
            ->pluck('count', 'month');

        $months = $this->year === now()->year ? now()->month : 12;

        return collect(range(1, $months))
            ->map(fn ($m) => (int) $counts->get($m, 0))
            ->skipUntil(fn ($count) => $count > 0)
            ->values()
            ->all();
    }

}
?>
<flux:card class="col-span-4 overflow-hidden">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="tabular-nums">
                {{ number_format($this->totalCount) }} {{ mb_strtolower(trans_choice('app.transport', $this->totalCount)) }}
            </flux:heading>

            @if ($this->totalCount > 0)
                <flux:text class="tabular-nums">
                    {{
                        __('app.sent_count', [
                            'sent' => number_format($this->sentCount),
                            'total' => number_format($this->totalCount),
                        ])
                    }}
                    ({{ round($this->sentCount / $this->totalCount * 100) }}%)
                </flux:text>
            @endif
        </div>

        @if ($this->totalWeight > 0)
            <div class="flex items-center gap-1 font-medium text-md text-green-600 dark:text-green-400">
                <flux:icon icon="scale" variant="mini"/>
                {{ number_format($this->totalWeight) }} {{ __('app.weight.unit') }}
            </div>
        @endif
    </div>

    <flux:chart class="-mx-6 -mb-6 mt-6 h-14" :value="$this->monthlyTrend">
        <flux:chart.svg gutter="1 0 0 0">
            <flux:chart.line class="text-sky-200 dark:text-amber-400"/>
            <flux:chart.area class="text-sky-100 dark:text-amber-200"/>
        </flux:chart.svg>
    </flux:chart>
</flux:card>
