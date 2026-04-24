<?php

use App\Enumerables\PalletType;
use App\Models\Pallet;
use App\Models\Parcel;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {

    #[Locked]
    public int $year;

    /**
     * Get the total number of pallets within the selected year.
     */
    #[Computed]
    public function totalCount(): int {
        return Pallet::query()
            ->whereYear('created_at', $this->year)
            ->count();
    }

    /**
     * Get the total weight of pallets within the selected year.
     * MANUAL pallets use their own weight; CALCULATED pallets sum their parcels.
     */
    #[Computed]
    public function totalWeight(): float {
        $manualWeight = (float) Pallet::query()
            ->whereYear('created_at', $this->year)
            ->where('type', PalletType::MANUAL_PALLET)
            ->sum('weight');

        $calculatedWeight = (float) Parcel::query()
            ->whereYear('created_at', $this->year)
            ->whereHas('pallet', fn ($p) => $p->where('type', PalletType::CALCULATED))
            ->sum('weight');

        return $manualWeight + $calculatedWeight;
    }

    /**
     * Get the total number of sent pallets within the selected year.
     */
    #[Computed]
    public function sentCount(): int {
        return Pallet::query()
            ->whereYear('created_at', $this->year)
            ->sent()
            ->count();
    }

    /**
     * Generate monthly trend for plotting the data.
     */
    #[Computed]
    public function monthlyTrend(): array {
        $counts = Pallet::query()
            ->whereYear('created_at', $this->year)
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int AS month, COUNT(*) AS count')
            ->groupByRaw('EXTRACT(MONTH FROM created_at)')
            ->pluck('count', 'month');

        $months = $this->year === now()->year ? min(now()->month + 1, 12) : 12;

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
                {{ number_format($this->totalCount) }} {{ mb_strtolower(trans_choice('app.pallet', $this->totalCount)) }}
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
