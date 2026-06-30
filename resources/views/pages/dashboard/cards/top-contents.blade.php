<?php

use App\Models\Content;
use App\Models\Pallet;
use App\Models\Parcel;
use App\Enumerables\PalletType;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {

    #[Locked]
    public int $year;

    /**
     * Get the top 10 content items by total weight packed within the selected year.
     * Weight is attributed from:
     * - Standalone parcels (no pallet)
     * - Parcels on CALCULATED pallets
     * - MANUAL pallets (weight split evenly across distinct content categories)
     *
     * @return array<int, array{label: string, weight: float, percentage: float}>
     */
    #[Computed]
    public function topContents(): array {
        // Parcel-based content weight (standalone + calculated pallet parcels)
        $parcelWeights = Parcel::query()
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
                        ->selectRaw('cp2.parcel_id, COUNT(DISTINCT c2.id) AS cnt')
                        ->groupBy('cp2.parcel_id');
                },
                'content_count',
                'content_count.parcel_id',
                '=',
                'parcels.id'
            )
            ->leftJoin('pallets', 'pallets.id', '=', 'parcels.pallet_id')
            ->whereYear('parcels.created_at', $this->year)
            ->where(function ($q): void {
                $q->whereNull('parcels.pallet_id')
                    ->orWhere('pallets.type', PalletType::CALCULATED->name);
            })
            ->selectRaw('c.id, SUM(parcels.weight / NULLIF(content_count.cnt::float, 0)) AS total')
            ->groupBy('c.id')
            ->pluck('total', 'id');

        // Manual pallet content weight (split evenly across distinct content items)
        $palletWeights = Pallet::query()
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
                        ->selectRaw('cp2.pallet_id, COUNT(DISTINCT c2.id) AS cnt')
                        ->groupBy('cp2.pallet_id');
                },
                'content_count',
                'content_count.pallet_id',
                '=',
                'pallets.id'
            )
            ->whereYear('pallets.created_at', $this->year)
            ->where('pallets.type', PalletType::MANUAL_PALLET)
            ->selectRaw('c.id, SUM(pallets.weight / NULLIF(content_count.cnt::float, 0)) AS total')
            ->groupBy('c.id')
            ->pluck('total', 'id');

        $combined = $parcelWeights->keys()
            ->merge($palletWeights->keys())
            ->unique()
            ->mapWithKeys(fn ($id) => [
                $id => (float) $parcelWeights->get($id, 0) + (float) $palletWeights->get($id, 0),
            ])
            ->sortDesc()
            ->take(5);

        if ($combined->isEmpty()) {
            return [];
        }

        $totalWeight = $combined->sum();

        $contentLabels = Content::query()
            ->whereIn('id', $combined->keys())
            ->pluck(Content::label(), 'id');

        return $combined->map(fn ($weight, $id) => [
            'label' => $contentLabels->get($id, '#'.$id),
            'weight' => round($weight, 1),
            'percentage' => $totalWeight > 0 ? round($weight / $totalWeight * 100) : 0,
        ])->values()->all();
    }

}
?>
<flux:card class="col-span-12 lg:col-span-6 overflow-hidden">
    <flux:heading size="l" class="mb-4">
        {{ __('app.top_contents') }}
    </flux:heading>

    @if (count($this->topContents) === 0)
        <flux:text variant="strong">{{ __('app.no_items') }}</flux:text>
    @else
        <div class="flex flex-col gap-3">
            @foreach ($this->topContents as $item)
                <div class="flex items-center gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <flux:text class="truncate font-medium">{{ $item['label'] }}</flux:text>
                            <div class="flex items-center gap-1">
                                <flux:text class="tabular-nums text-zinc-500 dark:text-zinc-400 shrink-0">
                                    {{ number_format($item['weight']) }} {{ __('app.weight.unit') }}
                                </flux:text>

                                <flux:text class="tabular-nums text-zinc-400 dark:text-zinc-500 text-xs">
                                    ({{ $item['percentage'] }}%)
                                </flux:text>
                            </div>
                        </div>

                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                            <div
                                class="h-full rounded-full bg-sky-400 dark:bg-amber-400"
                                style="width: {{ $item['percentage'] }}%"
                            ></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</flux:card>
