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
     * Generate per-month packed weight data for the chart, combining:
     * - Standalone parcels (not on a pallet)
     * - Parcels on CALCULATED pallets
     * - MANUAL_PALLET pallets (using their own weight field)
     */
    #[Computed]
    public function monthlyData(): array {
        $directByMonth = Parcel::query()
            ->whereYear('created_at', $this->year)
            ->whereNull('pallet_id')
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int AS month, SUM(weight) AS total')
            ->groupByRaw('EXTRACT(MONTH FROM created_at)')
            ->pluck('total', 'month');

        $calculatedByMonth = Parcel::query()
            ->whereYear('created_at', $this->year)
            ->whereHas('pallet', fn ($p) => $p->where('type', PalletType::CALCULATED))
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int AS month, SUM(weight) AS total')
            ->groupByRaw('EXTRACT(MONTH FROM created_at)')
            ->pluck('total', 'month');

        $manualByMonth = Pallet::query()
            ->whereYear('created_at', $this->year)
            ->where('type', PalletType::MANUAL_PALLET)
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int AS month, SUM(weight) AS total')
            ->groupByRaw('EXTRACT(MONTH FROM created_at)')
            ->pluck('total', 'month');

        $months = $this->year === now()->year ? now()->month : 12;
        return collect(range(1, $months))
            ->map(fn ($m) => [
                'month' => now()->setMonth($m)->format('M'),
                'weight' => round(
                    (float) $directByMonth->get($m, 0)
                    + (float) $calculatedByMonth->get($m, 0)
                    + (float) $manualByMonth->get($m, 0),
                    1
                ),
            ])
            ->values()
            ->all();
    }

}
?>
<flux:card class="col-span-8 overflow-hidden">
    <flux:subheading class="mb-1">{{ __('app.weight_per_month') }}</flux:subheading>

    <flux:chart :value="$this->monthlyData" class="-mx-8 -mb-9 mt-4 h-52" gutter="1 0 0 0">
        <flux:chart.svg>
            <flux:chart.line field="weight" class="text-sky-200 dark:text-amber-400"/>
            <flux:chart.area field="weight" class="text-sky-100 dark:text-amber-200"/>
            <flux:chart.point field="weight" class="text-sky-200 dark:text-amber-400" />

            <flux:chart.axis axis="y">
                <flux:chart.axis.grid/>
            </flux:chart.axis>

            <flux:chart.cursor/>
        </flux:chart.svg>

        <flux:chart.tooltip>
            <flux:chart.tooltip.heading field="month"/>
            <flux:chart.tooltip.value field="weight" :label="__('app.weight.label')" :format="['style' => 'unit', 'unit' => 'kilogram', 'unitDisplay' => 'short']"/>
        </flux:chart.tooltip>
    </flux:chart>
</flux:card>
