<?php

use App\Enumerables\Availability;
use App\Livewire\Components\TableComponent;
use App\Models\Pallet;
use App\Models\Recipient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

new class extends TableComponent {

    #[On('items-updated')]
    public function refreshList(): void {
        unset($this->items);
    }

    #[Url(except: '')]
    public array $range = [];

    #[Url(except: '')]
    public string $availability = '';

    #[Url(except: '')]
    public string $recipient_id = '';

    #[Computed]
    public function items(): LengthAwarePaginator {
        return Pallet::query()
            ->with(['content', 'recipient', 'parcels.content'])
            ->when($this->q, fn($query) => $query
                ->whereAny(['id', 'weight', 'notes'], 'ILIKE', "%{$this->q}%")
                ->orWhereHas('author', fn($q) => $q->where('name', 'ILIKE', "%{$this->q}%"))
            )
->when($this->recipient_id, fn($query) => $query->where('recipient_id', $this->recipient_id))
            ->when(!empty($this->range), fn($query) => $query
                ->whereDate('created_at', '>=', $this->range['start'])
                ->whereDate('created_at', '<=', $this->range['end'])
            )
            ->when(true, function ($query) {
                return match ($this->availability) {
                    Availability::ANY_STATUS->name => $query,
                    Availability::LOADED_ON_TRANSPORT->name => $query->whereNotNull('transport_id'),
                    default => $query->whereNull('transport_id'),
                };
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate();
    }

    #[Computed]
    protected function recipients(): Collection {
        return Recipient::list(['id', 'name'], 'name')->get();
    }

    public function render(): View {
        return view($this->getViewTemplate())
            ->title(__('pages.pallets.title'));
    }
}

?>
<section wire:poll.60s>
    <header class="mb-6">
        <flux:heading size="xl" level="1">{{ __('pages.pallets.headline') }}</flux:heading>
        <flux:text class="mb-6 mt-2 text-base">{{ __('pages.pallets.subtitle') }}</flux:text>
        <flux:separator variant="subtle"/>
    </header>

    <div class="flex flex-wrap gap-4 items-center mb-4">
        <flux:input wire:model.live.debounce.500ms="q" icon-trailing="magnifying-glass"
                    placeholder="{{__('app.search')}}" clearable class="w-full md:flex-1"/>

        <flux:date-picker mode="range" wire:model.live="range" locale="{{ App::getLocale() }}" placeholder="{{ __('app.date_range') }}" with-today week-numbers clearable class="w-full md:flex-1" />

        <flux:select variant="listbox" wire:model.live="availability" placeholder="{{ __('app.availability') }}"
                     clearable class="w-full md:flex-1">
            @foreach (Availability::palletFilters() as $case)
                <flux:select.option value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="recipient_id" placeholder="{{ __('app.recipient') }}" clearable
                     class="md:flex-1 !w-auto grow">
            @foreach ($this->recipients as $recipient)
                <flux:select.option value="{{ $recipient->id }}">{{ $recipient->name }}</flux:select.option>
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
            <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                               wire:click="sort('status')">{{ __('app.status') }}</flux:table.column>
            <flux:table.column>{{ __('app.availability') }}</flux:table.column>
            <flux:table.column>{{ __('app.recipient') }}</flux:table.column>
            <flux:table.column>{{ __('app.content.label') }}</flux:table.column>
            <flux:table.column>{{ __('app.weight.label') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'notes'" :direction="$sortDirection"
                               wire:click="sort('notes')">{{ __('app.notes') }}</flux:table.column>
            <flux:table.column>{{ __('app.created_at') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->items as $item)
                @include('pages.pallets._pallet-card')
                @include('pages.pallets._pallet-row')
            @empty
                <flux:table.row>
                    <flux:table.cell>{{ __('app.no_items') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <x-modals.flyout name="{{ $this->modalName }}" title="{{ __('pages.pallets.form.title') }}"
              subtitle="{{ __('pages.pallets.form.subtitle') }}" position="{{ $this->modalPosition }}">
        <livewire:pages::pallets.pallet-form/>
    </x-modals.flyout>

    <livewire:modals.scanner-modal/>
    <livewire:modals.add-modal/>
</section>
