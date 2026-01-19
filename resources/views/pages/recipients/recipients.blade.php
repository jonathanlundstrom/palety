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
    /**
     * Edit an existing recipient based on ID.
     * @param int $recipient_id
     * @return void
     */
    public function edit(int $recipient_id): void {
        $this->dispatch('edit-recipient', id: $recipient_id);
    }

    #[On('recipients-updated')]
    public function refreshList(): void {
        unset($this->items, $this->cities);
    }

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: '')]
    public string $delivery_type = '';

    #[Url(except: '')]
    public string $city = '';

    #[Computed]
    public function items(): LengthAwarePaginator {
        return Recipient::query()
            ->when($this->q, fn($query) => $query->whereAny(
                ['name', 'phone_number', 'email', 'city'], 'ILIKE', "%{$this->q}%")
            )
            ->when($this->type, fn($query) => $query->where('type', $this->type))
            ->when($this->delivery_type, fn($query) => $query->where('delivery_type', $this->delivery_type))
            ->when($this->city, fn($query) => $query->where('city', $this->city))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(20);
    }

    #[Computed]
    protected function cities(): Collection {
        return Recipient::all('city')
            ->unique('city')
            ->sortBy('city')
            ->pluck('city');
    }

    public function render(): View {
        return view('pages::recipients.recipients')
            ->title(__('navigation.recipients'));
    }
}

?>
<section>
    <header class="mb-6">
        <flux:heading size="xl" level="1">{{ __('pages.recipients.headline') }}</flux:heading>
        <flux:text class="mb-6 mt-2 text-base">{{ __('pages.recipients.subtitle') }}</flux:text>
        <flux:separator variant="subtle" />
    </header>

    <div class="flex flex-wrap gap-4 items-center mb-4">
        <flux:input wire:model.live.debounce.500ms="q" icon-trailing="magnifying-glass" placeholder="{{__('app.search')}}" clearable class="w-full md:flex-1" />

        <flux:select variant="listbox" wire:model.live="type" placeholder="{{ __('validation.attributes.type') }}" clearable class="w-full md:flex-1">
            @foreach (RecipientType::cases() as $case)
                <flux:select.option value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="delivery_type" placeholder="{{ __('validation.attributes.delivery_type') }}" clearable class="w-full md:flex-1">
            @foreach (DeliveryType::cases() as $case)
                <flux:select.option  value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="city" placeholder="{{ __('validation.attributes.city') }}" clearable class="flex-1">
            @foreach ($this->cities as $city)
                <flux:select.option>{{ $city }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:modal.trigger name="recipient-form">
            <flux:button variant="primary" icon="plus" class="flex-0">{{ __('app.add') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:table :paginate="$this->items">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('validation.attributes.id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('validation.attributes.name') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'type'" :direction="$sortDirection" wire:click="sort('type')">{{ __('validation.attributes.type') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'phone_number'" :direction="$sortDirection" wire:click="sort('phone_number')">{{ __('validation.attributes.phone_number') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'delivery_type'" :direction="$sortDirection" wire:click="sort('delivery_type')">{{ __('validation.attributes.delivery_type') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'city'" :direction="$sortDirection" wire:click="sort('city')">{{ __('validation.attributes.city') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->items as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>{{ $item->id }}</flux:table.cell>
                    <flux:table.cell>{{ $item->name }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" inset="top bottom" color="{{ $this->color($item->type) }}">
                            {{ $item->type->label() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <a href="tel:{{ $item->phone_number }}">{{ phone($item->phone_number)->formatInternational() }}</a>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" inset="top bottom" color="{{ $this->color($item->delivery_type) }}">
                            {{ $item->delivery_type->label() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $item->city }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>
                            <flux:menu>
                                <flux:modal.trigger name="recipient-form">
                                    <flux:menu.item icon="pencil-square" wire:click="edit({{ $item->id }})">{{ __('app.edit') }}</flux:menu.item>
                                </flux:modal.trigger>
                                <flux:menu.item icon="trash" variant="danger">{{ __('app.delete') }}</flux:menu.item>
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

    <x-flyout name="recipient-form" title="{{ __('pages.recipients.form.title') }}" subtitle="{{ __('pages.recipients.form.subtitle') }}" position="right">
        <livewire:pages::recipients.recipient-form />
    </x-flyout>
</section>
