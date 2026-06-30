<?php

use App\Enumerables\DeliveryType;
use App\Enumerables\RecipientType;
use App\Livewire\Components\TableComponent;
use App\Models\Recipient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

new class extends TableComponent {

    #[On('items-updated')]
    public function onItemsUpdated(): void {
        unset($this->items, $this->cities);
    }

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: '')]
    public string $delivery_type = '';

    #[Url(except: '')]
    public string $city = '';

    /**
     * Mount the Livewire component.
     * Currently used to override parent sorting properties.
     * @return void
     */
    public function mount(): void {
        $this->sortBy = $this->sortBy ?? 'name';
        $this->sortDirection = $this->sortDirection ?? 'asc';
    }

    #[Computed]
    public function items(): LengthAwarePaginator {
        return Recipient::query()
            ->when($this->q, fn($query) => $query->whereAny(
                ['id', 'name', 'reference', 'email', 'phone_number', 'organisation_number', 'address', 'zipcode', 'city'], 'ILIKE', "%{$this->q}%")
            )
            ->when($this->type, fn($query) => $query->where('type', $this->type))
            ->when($this->delivery_type, fn($query) => $query->where('delivery_type', $this->delivery_type))
            ->when($this->city, fn($query) => $query->where('city', $this->city))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate();
    }

    #[Computed]
    protected function cities(): Collection {
        return Recipient::cities()->pluck('city');
    }

    public function render(): View {
        return view($this->getViewTemplate())
            ->title(__('pages.recipients.title'));
    }
}

?>
<section wire:poll.60s>
    <header class="mb-6">
        <flux:heading size="xl" level="1">{{ __('pages.recipients.headline') }}</flux:heading>
        <flux:text class="mb-6 mt-2 text-base">{{ __('pages.recipients.subtitle') }}</flux:text>
        <flux:separator variant="subtle" />
    </header>

    <div class="flex flex-wrap gap-4 items-center mb-4">
        <flux:input wire:model.live.debounce.500ms="q" icon-trailing="magnifying-glass" placeholder="{{__('app.search')}}" clearable class="w-full md:flex-1" />

        <flux:select variant="listbox" wire:model.live="type" placeholder="{{ __('app.type') }}" clearable class="w-full md:flex-1">
            @foreach (RecipientType::cases() as $case)
                <flux:select.option value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="delivery_type" placeholder="{{ __('app.delivery_type') }}" clearable class="w-full md:flex-1">
            @foreach (DeliveryType::cases() as $case)
                <flux:select.option  value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="city" placeholder="{{ __('app.city') }}" clearable class="md:flex-1 !w-auto grow">
            @foreach ($this->cities as $city)
                <flux:select.option>{{ $city }}</flux:select.option>
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
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('app.id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('app.name') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'type'" :direction="$sortDirection" wire:click="sort('type')">{{ __('app.type') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'phone_number'" :direction="$sortDirection" wire:click="sort('phone_number')">{{ __('app.phone_number') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'delivery_type'" :direction="$sortDirection" wire:click="sort('delivery_type')">{{ __('app.delivery_type') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'city'" :direction="$sortDirection" wire:click="sort('city')">{{ __('app.city') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->items as $item)
                @include('pages.recipients._recipient-card')
                @include('pages.recipients._recipient-row')
            @empty
                <flux:table.row>
                    <flux:table.cell>{{ __('app.no_items') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <x-modals.flyout name="{{ $this->modalName }}" title="{{ __('pages.recipients.form.title') }}" subtitle="{{ __('pages.recipients.form.subtitle') }}" position="{{ $this->modalPosition }}">
        <livewire:pages::recipients.recipient-form />
    </x-modals.flyout>
</section>
