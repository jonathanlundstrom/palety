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
        $months = $this->year === now()->year ? min(now()->month + 1, 12) : 12;
        $categoryKeys = array_keys(ImportCategory::chartCategories());

        // Initialise a zeroed structure: [month => [category => 0.0]]
        $data = collect(range(1, $months))->mapWithKeys(
            fn ($m) => [$m => array_fill_keys($categoryKeys, 0.0)]
        )->all();

        // MANUAL pallets: weight split evenly across distinct content categories via pivot
        Pallet::query()
            ->join('content_pallet as cp', 'cp.pallet_id', '=', 'pallets.id')
            ->join('contents as c', function ($join): void {
                $join->on('c.id', '=', 'cp.content_id')->whereNull('c.deleted_at');
            })
            ->joinSub(
                function ($query): void {
                    $query->from('content_pallet as cp2')
                        ->join('contents as c2', function ($join): void {
                            $join->on('c2.id', '=', 'cp2.content_id')->whereNull('c2.deleted_at');
                        })
                        ->selectRaw('cp2.pallet_id, COUNT(DISTINCT c2.category) AS cnt')
                        ->groupBy('cp2.pallet_id');
                },
                'cat_count',
                'cat_count.pallet_id',
                '=',
                'pallets.id'
            )
            ->whereYear('pallets.created_at', $this->year)
            ->where('pallets.type', PalletType::MANUAL_PALLET)
            ->selectRaw('EXTRACT(MONTH FROM pallets.created_at)::int AS month, c.category, SUM(pallets.weight / NULLIF(cat_count.cnt::float, 0)) AS total')
            ->groupByRaw('EXTRACT(MONTH FROM pallets.created_at), c.category')
            ->get()
            ->each(function ($row) use (&$data): void {
                if (isset($data[$row->month][$row->category])) {
                    $data[$row->month][$row->category] += (float) $row->total;
                }
            });

        // Loose parcels + parcels on CALCULATED pallets: weight split evenly across
        // the parcel's distinct content categories. Parcels with no content are excluded
        // since they cannot be attributed to a category.
        Parcel::query()
            ->join('content_parcel as cp', 'cp.parcel_id', '=', 'parcels.id')
            ->join('contents as c', function ($join): void {
                $join->on('c.id', '=', 'cp.content_id')->whereNull('c.deleted_at');
            })
            ->joinSub(
                function ($query): void {
                    $query->from('content_parcel as cp2')
                        ->join('contents as c2', function ($join): void {
                            $join->on('c2.id', '=', 'cp2.content_id')->whereNull('c2.deleted_at');
                        })
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
            ->skipUntil(fn ($categories) => array_sum($categories) > 0.0)
            ->map(fn ($categories, $m) => array_merge(
                ['month' => now()->setMonth($m)->format('M')],
                array_map(fn ($v) => round($v, 1), $categories),
            ))
            ->values()
            ->all();
    }

    #[Computed]
    public function categoryColors(): array {
        return ImportCategory::chartCategories();
    }

}
?>
<flux:card class="col-span-12 overflow-hidden">
    <div class="pt-4">
        <flux:chart :value="$this->monthlyData" class="h-128">
            <flux:chart.svg>
                <flux:chart.group stacked>
                    @foreach ($this->categoryColors as $name => $colors)
                        <flux:chart.bar
                            field="{{ $name }}"
                            class="{{ $colors['bar'] }}"
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
                @foreach (array_keys($this->categoryColors) as $name)
                    <flux:chart.tooltip.value
                        field="{{ $name }}"
                        :label="ImportCategory::from($name)->label()"
                        :format="['style' => 'unit', 'unit' => 'kilogram', 'unitDisplay' => 'short']"
                    />
                @endforeach
            </flux:chart.tooltip>
        </flux:chart>
    </div>
</flux:card>
