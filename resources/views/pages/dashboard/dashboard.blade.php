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
<div class="flex w-full flex-1 flex-col gap-6 rounded-xl">
    <header>
        <div class="flex flex-wrap items-center justify-between pb-4">
            <flux:heading size="xl" level="1">{{ __('pages.dashboard.headline') }}</flux:heading>

            <div>
                <flux:select wire:model.live="year" variant="listbox" size="sm">
                    @foreach ($this->availableYears as $y)
                        <flux:select.option value="{{ $y }}">{{ $y }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <flux:separator variant="subtle"/>
    </header>

    <div class="grid lg:grid-cols-12 grid-cols-1 gap-6">
        <livewire:pages::dashboard.cards.parcels :year="$year" />
        <livewire:pages::dashboard.cards.pallets :year="$year" />
        <livewire:pages::dashboard.cards.transports :year="$year" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-full flex-1 items-start">
        <div class="col-span-12 lg:col-span-4 gap-6">
            <div class="flex flex-col gap-6">
                <livewire:pages::dashboard.cards.top-contents :year="$year" />
                <livewire:pages::dashboard.cards.top-recipients :year="$year" />
            </div>
        </div>

        <div class="flex flex-col col-span-12 lg:col-span-8 gap-6">
            <livewire:pages::dashboard.cards.weight-by-category :year="$year" />
        </div>
    </div>
</div>
