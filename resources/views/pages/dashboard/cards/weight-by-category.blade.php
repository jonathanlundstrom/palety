<?php

use App\Enumerables\ImportCategory;
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
     * Build monthly weight data broken down by ImportCategory.
     *
     * MANUAL pallets: weight comes from the pallet's own weight field.
     * CALCULATED pallets: weight comes from their parcels, category from parcel content.
     */
    #[Computed]
    public function monthlyData(): array {
        $months = $this->year === now()->year ? now()->month : 12;
        $categories = ImportCategory::cases();

        // Initialise a zeroed structure: [month => [category => 0.0]]
        $data = collect(range(1, $months))->mapWithKeys(
            fn ($m) => [$m => collect($categories)->mapWithKeys(fn ($c) => [$c->name => 0.0])->all()]
        )->all();

        // MANUAL pallets: direct weight + direct category
        Pallet::query()
            ->whereYear('created_at', $this->year)
            ->where('type', PalletType::MANUAL_PALLET)
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int AS month, category, SUM(weight) AS total')
            ->groupByRaw('EXTRACT(MONTH FROM created_at), category')
            ->get()
            ->each(function ($row) use (&$data): void {
                $data[$row->month][$row->category->name] = (float) $row->total;
            });

        // Loose parcels + parcels on CALCULATED pallets: weight split evenly across
        // the parcel's distinct content categories. Parcels with no content are excluded
        // since they cannot be attributed to a category.
        Parcel::query()
            ->join('content_parcel as cp', 'cp.parcel_id', '=', 'parcels.id')
            ->join('contents as c', 'c.id', '=', 'cp.content_id')
            ->joinSub(
                function ($query): void {
                    $query->from('content_parcel as cp2')
                        ->join('contents as c2', 'c2.id', '=', 'cp2.content_id')
                        ->selectRaw('cp2.parcel_id, COUNT(DISTINCT c2.category) AS cnt')
                        ->groupBy('cp2.parcel_id');
                },
                'cat_count',
                'cat_count.parcel_id',
                '=',
                'parcels.id'
            )
            ->leftJoin('pallets', 'pallets.id', '=', 'parcels.pallet_id')
            ->whereYear('parcels.created_at', $this->year)
            ->where(function ($q): void {
                $q->whereNull('parcels.pallet_id')
                    ->orWhere('pallets.type', PalletType::CALCULATED->name);
            })
            ->selectRaw('EXTRACT(MONTH FROM parcels.created_at)::int AS month, c.category, SUM(parcels.weight / NULLIF(cat_count.cnt::float, 0)) AS total')
            ->groupByRaw('EXTRACT(MONTH FROM parcels.created_at), c.category')
            ->get()
            ->each(function ($row) use (&$data): void {
                if (isset($data[$row->month][$row->category])) {
                    $data[$row->month][$row->category] += (float) $row->total;
                }
            });

        // Format for the chart: [['month' => 'Jan', 'FOOD' => 100.0, ...], ...]
        return collect($data)
            ->map(fn ($categories, $m) => array_merge(
                ['month' => now()->setMonth($m)->format('M')],
                array_map(fn ($v) => round($v, 1), $categories),
            ))
            ->values()
            ->all();
    }

    /**
     * Color classes for each ImportCategory, derived from the enum's color() method.
     *
     * @return array<string, array{bar: string, indicator: string}>
     */
    #[Computed]
    public function categoryColors(): array {
        return [
            'FOOD' => ['bar' => 'text-lime-500 dark:text-lime-400', 'indicator' => 'bg-lime-500 dark:bg-lime-400'],
            'SANITARY_HYGIENE' => ['bar' => 'text-cyan-500 dark:text-cyan-400', 'indicator' => 'bg-cyan-500 dark:bg-cyan-400'],
            'MEDICAL' => ['bar' => 'text-red-500 dark:text-red-400', 'indicator' => 'bg-red-500 dark:bg-red-400'],
            'CLOTHING' => ['bar' => 'text-emerald-500 dark:text-emerald-400', 'indicator' => 'bg-emerald-500 dark:bg-emerald-400'],
            'TECHNICAL' => ['bar' => 'text-purple-500 dark:text-purple-400', 'indicator' => 'bg-purple-500 dark:bg-purple-400'],
            'VEHICLES' => ['bar' => 'text-orange-500 dark:text-orange-400', 'indicator' => 'bg-orange-500 dark:bg-orange-400'],
            'FUEL' => ['bar' => 'text-yellow-500 dark:text-yellow-400', 'indicator' => 'bg-yellow-500 dark:bg-yellow-400'],
            'OTHER' => ['bar' => 'text-zinc-400 dark:text-zinc-500', 'indicator' => 'bg-zinc-400 dark:bg-zinc-500'],
        ];
    }

}
?>
<flux:card class="col-span-8 overflow-hidden">
    <div class="pt-4">
        <flux:chart :value="$this->monthlyData" class="h-128">
            <flux:chart.svg>
                <flux:chart.group stacked>
                    @foreach (ImportCategory::cases() as $category)
                        <flux:chart.bar
                            field="{{ $category->name }}"
                            class="{{ $this->categoryColors[$category->name]['bar'] }}"
                            radius="2"
                        />
                    @endforeach
                </flux:chart.group>

                <flux:chart.axis axis="x" field="month">
                    <flux:chart.axis.grid/>
                    <flux:chart.axis.tick/>
                </flux:chart.axis>

                <flux:chart.axis axis="y" :format="['style' => 'unit', 'unit' => 'kilogram', 'unitDisplay' => 'short']">
                    <flux:chart.axis.grid/>
                    <flux:chart.axis.tick/>
                </flux:chart.axis>

                <flux:chart.cursor/>
            </flux:chart.svg>

            <flux:chart.tooltip>
                <flux:chart.tooltip.heading field="month"/>
                @foreach (ImportCategory::cases() as $category)
                    <flux:chart.tooltip.value
                        field="{{ $category->name }}"
                        :label="$category->label()"
                        :format="['style' => 'unit', 'unit' => 'kilogram', 'unitDisplay' => 'short']"
                    />
                @endforeach
            </flux:chart.tooltip>
        </flux:chart>
    </div>
</flux:card>
