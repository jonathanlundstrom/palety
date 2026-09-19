<?php

use App\Enumerables\TransportStatus;
use App\Enumerables\TransportType;
use App\Livewire\Components\TableComponent;
use App\Models\Transport;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

new class extends TableComponent {

    #[On('items-updated')]
    public function onItemsUpdated(): void {
        unset($this->items);
    }

    #[Url(except: '')]
    public array $range = [];

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $recipient_id = '';

    #[Computed]
    public function items(): LengthAwarePaginator {
        return Transport::query()
            ->when($this->q, fn($query) => $query->whereAny(
                ['id', 'notes'], 'ILIKE', "%{$this->q}%")
            )
            ->when($this->type, fn($query) => $query->where('type', $this->type))
            ->when($this->status, fn($query) => $query->where('status', $this->status))
            ->when(!empty($this->range), fn($query) => $query
                ->whereDate('created_at', '>=', $this->range['start'])
                ->whereDate('created_at', '<=', $this->range['end'])
            )
            ->withCount(['pallets', 'parcels'])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render(): View {
        return view($this->getViewTemplate())
            ->title(__('pages.transports.title'));
    }
}

?>
<section wire:poll.60s>
    <x-table.filters :headline="__('pages.transports.headline')"
                     :subtitle="__('pages.transports.subtitle')"
                     :modal="$this->modalName">
        <flux:date-picker mode="range" wire:model.live="range" locale="{{ App::getLocale() }}" placeholder="{{ __('app.date_range') }}" with-today week-numbers clearable class="w-full md:flex-1" />

        <flux:select variant="listbox" wire:model.live="type" placeholder="{{ __('app.type') }}" clearable
                     class="w-full md:flex-1">
            @foreach (TransportType::cases() as $case)
                <flux:select.option value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="status" placeholder="{{ __('app.status') }}" clearable
                     class="md:flex-1 !w-auto grow">
            @foreach (TransportStatus::cases() as $case)
                <flux:select.option value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>
    </x-table.filters>

    <x-table.container :paginate="$this->items">
        <x-slot:columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection"
                               wire:click="sort('id')">{{ __('app.id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'type'" :direction="$sortDirection"
                               wire:click="sort('type')">{{ __('app.type') }}</flux:table.column>
            <flux:table.column>{{ trans_choice('app.pallet', 2) }}</flux:table.column>
            <flux:table.column>{{ trans_choice('app.parcel', 2) }}</flux:table.column>
            <flux:table.column>{{ __('app.weight.label') }}</flux:table.column>
            <flux:table.column>{{ __('app.status') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'notes'" :direction="$sortDirection"
                               wire:click="sort('notes')">{{ __('app.notes') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                               wire:click="sort('created_at')">{{ __('app.created_at') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'delivered_at'" :direction="$sortDirection"
                               wire:click="sort('delivered_at')">{{ __('app.delivered_at') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </x-slot:columns>

        @foreach ($this->items as $item)
            @include('pages.transports._transport-card')
            @include('pages.transports._transport-row')
        @endforeach
    </x-table.container>

    <x-modals.flyout name="{{ $this->modalName }}" position="{{ $this->modalPosition }}">
        <livewire:pages::transports.transport-form/>
    </x-modals.flyout>

    <livewire:modals.scanner-modal/>
    <livewire:modals.add-modal/>
</section>
