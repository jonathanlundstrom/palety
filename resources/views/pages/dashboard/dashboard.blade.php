<?php

use App\Models\Parcel;
use App\Models\Pallet;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {

    public int $year;

    public function mount(): void {
        $this->year = now()->year;
    }

    #[Computed]
    public function availableYears(): array {
        $parcelYears = Parcel::query()
            ->selectRaw('EXTRACT(YEAR FROM created_at)::int AS year')
            ->distinct()
            ->pluck('year');

        $palletYears = Pallet::query()
            ->selectRaw('EXTRACT(YEAR FROM created_at)::int AS year')
            ->distinct()
            ->pluck('year');

        return $parcelYears->merge($palletYears)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

}
?>
<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <header>
        <div class="flex flex-wrap items-center justify-between pb-4">
            <flux:heading size="xl" level="1">{{ __('pages.dashboard.headline') }}</flux:heading>

            <div>
                <flux:select wire:model.live="year" variant="listbox" size="sm" class="w-24">
                    @foreach ($this->availableYears as $y)
                        <flux:select.option value="{{ $y }}">{{ $y }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <flux:separator variant="subtle"/>
    </header>

    <div class="grid sm:grid-cols-12 grid-cols-1 gap-6">
        <livewire:pages::dashboard.cards.parcels :year="$year" />

        <livewire:pages::dashboard.cards.pallets :year="$year" />

        <livewire:pages::dashboard.cards.transports :year="$year" />
    </div>

    <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20"/>
    </div>
</div>
