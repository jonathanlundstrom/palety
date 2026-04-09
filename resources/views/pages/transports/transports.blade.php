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
    public string $type = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $recipient_id = '';

    #[Computed]
    public function items(): LengthAwarePaginator {
        return Transport::query()
            ->when($this->q, fn($query) => $query->whereAny(
                ['notes'], 'ILIKE', "%{$this->q}%")
            )
            ->when($this->type, fn($query) => $query->where('type', $this->type))
            ->withCount(['pallets', 'parcels'])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate();
    }

    public function render(): View {
        return view($this->getViewTemplate())
            ->title(__('pages.transports.title'));
    }
}

?>
<section>
    <header class="mb-6">
        <flux:heading size="xl" level="1">{{ __('pages.transports.headline') }}</flux:heading>
        <flux:text class="mb-6 mt-2 text-base">{{ __('pages.transports.subtitle') }}</flux:text>
        <flux:separator variant="subtle"/>
    </header>

    <div class="flex flex-wrap gap-4 items-center mb-4">
        <flux:input wire:model.live.debounce.500ms="q" icon-trailing="magnifying-glass"
                    placeholder="{{__('app.search')}}" clearable class="w-full md:flex-1"/>

        <flux:select variant="listbox" wire:model.live="type" placeholder="{{ __('app.type') }}" clearable
                     class="w-full md:flex-1">
            @foreach (TransportType::cases() as $case)
                <flux:select.option value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="status" placeholder="{{ __('app.status') }}" clearable
                     class="flex-1">
            @foreach (TransportStatus::cases() as $case)
                <flux:select.option value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:modal.trigger name="{{ $this->modalName }}">
            <flux:button variant="primary" icon="plus" class="flex-0">{{ __('app.add') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:table :paginate="$this->items">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection"
                               wire:click="sort('id')">{{ __('app.id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'type'" :direction="$sortDirection"
                               wire:click="sort('type')">{{ __('app.type') }}</flux:table.column>
            <flux:table.column>{{ trans_choice('app.pallet', 2) }}</flux:table.column>
            <flux:table.column>{{ trans_choice('app.parcel', 2) }}</flux:table.column>
            <flux:table.column>{{ __('app.weight.label') }}</flux:table.column>
            <flux:table.column>{{ __('app.status') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'notes'" :direction="$sortDirection"
                               wire:click="sort('weight')">{{ __('app.notes') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->items as $item)
                <flux:table.row key="row-{{$item->id}}">
                    <flux:table.cell>{{ $item->id }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" inset="top bottom" color="{{ $item->type->color() }}">
                            {{ $item->type->label() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $item->pallets_count ?: '–' }} {{ trans_choice('app.pieces', $item->pallets_count) }}</flux:table.cell>
                    <flux:table.cell>{{ $item->parcels_count ?: '–' }} {{ trans_choice('app.pieces', $item->parcels_count) }}</flux:table.cell>
                    <flux:table.cell>{{ $item->getWeight() }} {{ __('app.weight.unit') }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" inset="top bottom" color="{{ $item->status->color() }}">
                            {{ $item->status->label() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $item->notes ?? '–' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                         inset="top bottom"></flux:button>
                            <flux:menu>
                                <x-edit-button form="{{ $this->modalName }}" :object="$item"/>
                                <x-delete-button :object="$item"/>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell>{{ __('app.no_items') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <x-flyout name="{{ $this->modalName }}" title="{{ __('pages.transports.form.title') }}"
              subtitle="{{ __('pages.transports.form.subtitle') }}" position="{{ $this->modalPosition }}">
        <livewire:pages::transports.transport-form/>
    </x-flyout>

    <livewire:scanner-modal/>
</section>
