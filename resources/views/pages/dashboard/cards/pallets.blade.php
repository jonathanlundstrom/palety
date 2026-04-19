<?php

use App\Models\Pallet;
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
     * Calculate the increase or decrease compared to the same period the year before.
     */
    #[Computed]
    public function changePercentage(): ?float {
        $previousTotal = Pallet::query()
            ->whereYear('created_at', $this->year - 1)
            ->count();

        if ($previousTotal === 0) {
            return null;
        }

        return round((($this->totalCount - $previousTotal) / $previousTotal) * 100, 1);
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

        $months = $this->year === now()->year ? now()->month : 12;

        return collect(range(1, $months))
            ->map(fn ($m) => (int) $counts->get($m, 0))
            ->all();
    }

}
?>
<flux:card class="col-span-4 overflow-hidden">
    <flux:subheading class="mb-1">{{ __('app.pallet_statistics') }}</flux:subheading>
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
                    <strong>({{ round($this->sentCount / $this->totalCount * 100) }}%)</strong>
                </flux:text>
            @endif
        </div>

        <div class="tabular-nums">
            @if ($this->changePercentage !== null)
                <div class="flex items-center gap-1 font-medium text-sm {{ $this->changePercentage >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    <flux:icon icon="{{ $this->changePercentage >= 0 ? 'arrow-trending-up' : 'arrow-trending-down' }}" variant="micro"/>
                    {{ $this->changePercentage >= 0 ? '+' : '' }}{{ $this->changePercentage }}%
                </div>
            @elseif ($this->totalCount > 0)
                <div class="flex items-center gap-1 font-medium text-sm text-green-600 dark:text-green-400">
                    <flux:icon icon="arrow-trending-up" variant="micro"/>
                    100%
                </div>
            @endif
        </div>
    </div>

    <flux:chart class="-mx-6 -mb-6 mt-4 h-10" :value="$this->monthlyTrend">
        <flux:chart.svg gutter="0">
            <flux:chart.line class="text-sky-200 dark:text-amber-400"/>
            <flux:chart.area class="text-sky-100 dark:text-amber-200"/>
        </flux:chart.svg>
    </flux:chart>
</flux:card>
