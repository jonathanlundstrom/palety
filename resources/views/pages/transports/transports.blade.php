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


    /**
     * Mount the Livewire component.
     * Currently used to override parent sorting properties.
     * @return void
     */
    public function mount(): void {
        $this->sortBy = 'id';
        $this->sortDirection = 'desc';
    }

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
            ->paginate();
    }

    public function render(): View {
        return view($this->getViewTemplate())
            ->title(__('pages.transports.title'));
    }
}

?>
<section wire:poll.60s>
    <header class="mb-6">
        <flux:heading size="xl" level="1">{{ __('pages.transports.headline') }}</flux:heading>
        <flux:text class="mb-6 mt-2 text-base">{{ __('pages.transports.subtitle') }}</flux:text>
        <flux:separator variant="subtle"/>
    </header>

    <div class="flex flex-wrap gap-4 items-center mb-4">
        <flux:input wire:model.live.debounce.500ms="q" icon-trailing="magnifying-glass"
                    placeholder="{{__('app.search')}}" clearable class="w-full md:flex-1"/>

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

        <flux:modal.trigger name="{{ $this->modalName }}">
            <flux:button variant="primary" icon="plus" class="flex-0">{{ __('app.add') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="mt-6 mb-6 lg:hidden">
        <flux:separator variant="subtle" text="{{ __('app.all_items') }}"/>
    </div>

    <flux:table :paginate="$this->items">
        <flux:table.columns class="hidden lg:table-header-group">
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
            <flux:table.column>{{ __('app.created_at') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->items as $item)
                @include('pages.transports._transport-card')
                @include('pages.transports._transport-row')
            @empty
                <flux:table.row>
                    <flux:table.cell>{{ __('app.no_items') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <x-modals.flyout name="{{ $this->modalName }}" title="{{ __('pages.transports.form.title') }}"
              subtitle="{{ __('pages.transports.form.subtitle') }}" position="{{ $this->modalPosition }}">
        <livewire:pages::transports.transport-form/>
    </x-modals.flyout>

    <livewire:modals.scanner-modal/>
    <livewire:modals.add-modal/>
</section>
