<?php

use App\Models\Parcel;
use App\Models\Pallet;
use App\Models\Recipient;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {

    #[Locked]
    public int $year;

    /**
     * Get the top 8 recipients by total parcel count within the selected year.
     * Parcels are attributed to a recipient either directly (parcel.recipient_id)
     * or via the pallet they are on (pallet.recipient_id).
     * Manual pallets are counted separately.
     *
     * @return array<int, array{name: string, parcels: int, pallets: int}>
     */
    #[Computed]
    public function topRecipients(): array {
        $directParcels = Parcel::query()
            ->whereYear('parcels.created_at', $this->year)
            ->whereNotNull('parcels.recipient_id')
            ->selectRaw('parcels.recipient_id, COUNT(*) AS total')
            ->groupBy('parcels.recipient_id')
            ->pluck('total', 'recipient_id');

        $palletParcels = Parcel::query()
            ->whereYear('parcels.created_at', $this->year)
            ->whereNull('parcels.recipient_id')
            ->whereNotNull('parcels.pallet_id')
            ->join('pallets', 'pallets.id', '=', 'parcels.pallet_id')
            ->whereNotNull('pallets.recipient_id')
            ->selectRaw('pallets.recipient_id, COUNT(*) AS total')
            ->groupBy('pallets.recipient_id')
            ->pluck('total', 'recipient_id');

        $palletCountsRaw = Pallet::query()
            ->whereYear('pallets.created_at', $this->year)
            ->whereNotNull('pallets.recipient_id')
            ->selectRaw('pallets.recipient_id, COUNT(*) AS total')
            ->groupBy('pallets.recipient_id')
            ->pluck('total', 'recipient_id');

        $allRecipientIds = $directParcels->keys()
            ->merge($palletParcels->keys())
            ->merge($palletCountsRaw->keys())
            ->unique();

        if ($allRecipientIds->isEmpty()) {
            return [];
        }

        $parcelCounts = $allRecipientIds->mapWithKeys(fn ($id) => [
            $id => (int) $directParcels->get($id, 0) + (int) $palletParcels->get($id, 0),
        ]);

        $palletCounts = $allRecipientIds->mapWithKeys(fn ($id) => [
            $id => (int) $palletCountsRaw->get($id, 0),
        ]);

        $names = Recipient::query()
            ->whereIn('id', $allRecipientIds)
            ->pluck('name', 'id');

        return $allRecipientIds
            ->map(fn ($id) => [
                'name' => $names->get($id, '#'.$id),
                'parcels' => $parcelCounts->get($id, 0),
                'pallets' => $palletCounts->get($id, 0),
            ])
            ->sortByDesc('parcels')
            ->take(5)
            ->values()
            ->all();
    }

    /**
     * Get the maximum parcel count among top recipients for scaling bars.
     */
    #[Computed]
    public function maxParcels(): int {
        return (int) collect($this->topRecipients)->max('parcels') ?: 1;
    }

}
?>
<flux:card class="col-span-12 lg:col-span-6 overflow-hidden">
    <flux:heading size="l" class="mb-4">
        {{ __('app.top_recipients') }}
    </flux:heading>

    @if (count($this->topRecipients) === 0)
        <flux:text>{{ __('app.no_items') }}</flux:text>
    @else
        <div class="flex flex-col gap-3">
            @foreach ($this->topRecipients as $item)
                <div class="flex items-center gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="mb-1.5 flex items-center justify-between gap-2">
                            <flux:text class="truncate font-medium">{{ $item['name'] }}</flux:text>
                            <div class="flex items-center gap-2 shrink-0">
                                <flux:text class="tabular-nums text-sky-500 dark:text-sky-400 text-xs">
                                    {{ $item['parcels'] }} {{ mb_strtolower(trans_choice('app.parcel', $item['parcels'])) }}
                                </flux:text>
                                @if ($item['pallets'] > 0)
                                    <flux:text class="tabular-nums text-emerald-500 dark:text-emerald-400 text-xs">
                                        {{ $item['pallets'] }} {{ mb_strtolower(trans_choice('app.pallet', $item['pallets'])) }}
                                    </flux:text>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                                <div
                                    class="h-full rounded-full bg-sky-400 dark:bg-sky-500"
                                    style="width: {{ round($item['parcels'] / $this->maxParcels * 100) }}%"
                                ></div>
                            </div>
                            @if ($item['pallets'] > 0)
                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                                    <div
                                        class="h-full rounded-full bg-emerald-400 dark:bg-emerald-500"
                                        style="width: {{ round($item['pallets'] / $this->maxParcels * 100) }}%"
                                    ></div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="mt-1 flex items-center gap-3">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5">
                        <div class="h-2 w-2 rounded-full bg-sky-400 dark:bg-sky-500"></div>
                        <flux:text class="text-xs">{{ trans_choice('app.parcel', 2) }}</flux:text>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="h-2 w-2 rounded-full bg-emerald-400 dark:bg-emerald-500"></div>
                        <flux:text class="text-xs">{{ trans_choice('app.pallet', 2) }}</flux:text>
                    </div>
                </div>
            </div>
        </div>
    @endif
</flux:card>
