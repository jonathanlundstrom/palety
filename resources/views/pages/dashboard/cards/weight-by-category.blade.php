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
        $categoryKeys = array_keys(ImportCategory::chartCategories());

        // Initialise a zeroed structure: [month => [category => 0.0]]
        $data = collect(range(1, 12))->mapWithKeys(
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
                    ->orWhere('pallets.type', PalletType::CALCULATED);
            })
            ->selectRaw('EXTRACT(MONTH FROM parcels.created_at)::int AS month, c.category, SUM(parcels.weight / NULLIF(cat_count.cnt::float, 0)) AS total')
            ->groupByRaw('EXTRACT(MONTH FROM parcels.created_at), c.category')
            ->get()
            ->each(function ($row) use (&$data): void {
                if (isset($data[$row->month][$row->category])) {
                    $data[$row->month][$row->category] += (float) $row->total;
                }
            });

        return collect($data)
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
<flux:card class="col-span-12">
    <div class="flex flex-col gap-5">
        @foreach ($this->monthlyData as $row)
            @php
                $total = array_sum(array_filter($row, fn ($key) => $key !== 'month', ARRAY_FILTER_USE_KEY));
            @endphp
            <div class="flex items-center gap-4">
                <flux:text class="w-auto shrink-0 text-left text-zinc-500 dark:text-zinc-400">{{ $row['month'] }}</flux:text>

                <flux:tooltip position="top" class="flex-1" :disabled="$total === 0.0">
                    <div class="flex h-2 min-w-0 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                        @foreach ($this->categoryColors as $name => $color)
                            @if (($row[$name] ?? 0) > 0)
                                <div
                                    class="{{ $color }}"
                                    style="width: {{ round($row[$name] / $total * 100, 1) }}%"
                                ></div>
                            @endif
                        @endforeach
                    </div>
                    <flux:tooltip.content class="space-y-1">
                        @foreach ($this->categoryColors as $name => $color)
                            @if (($row[$name] ?? 0) > 0)
                                <div class="flex items-center gap-2">
                                    <div class="size-2 shrink-0 rounded-full {{ $color }}"></div>
                                    <span>{{ ImportCategory::from($name)->label() }}: {{ number_format($row[$name]) }} {{ __('app.weight.unit') }} ({{ round($row[$name] / $total * 100) }}%)</span>
                                </div>
                            @endif
                        @endforeach
                    </flux:tooltip.content>
                </flux:tooltip>

                @if ($total)
                    <flux:text class="w-auto shrink-0 text-right tabular-nums text-zinc-500 dark:text-zinc-400">
                        {{ number_format($total).' '.__('app.weight.unit') }}
                    </flux:text>
                @endif
            </div>
        @endforeach

        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
            @foreach ($this->categoryColors as $name => $color)
                <div class="flex items-center gap-1.5">
                    <div class="size-2.5 shrink-0 rounded-full {{ $color }}"></div>
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ ImportCategory::from($name)->label() }}</flux:text>
                </div>
            @endforeach
        </div>
    </div>
</flux:card>
