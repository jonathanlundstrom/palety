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
        dump($this->type);
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

    /**
     * Render method.
     * @return View
     */
    public function render(): View {
        return view('pages::recipients.recipients')
            ->title(__('navigation.recipients'));
    }
}

?>
<section>
    <header class="mb-6">
        <flux:heading size="xl" level="1">All recipients</flux:heading>
        <flux:text class="mb-6 mt-2 text-base">Add, edit and delete the list of recipients.</flux:text>
        <flux:separator variant="subtle" />
    </header>

    <div class="flex flex-wrap gap-4 items-center mb-4">
        <flux:input wire:model.live.debounce.500ms="q" icon-trailing="magnifying-glass" placeholder="{{__('app.search')}}" clearable class="flex-1"/>

        <flux:select variant="listbox" wire:model.live="type" placeholder="Type" clearable class="flex-1">
            @foreach (RecipientType::cases() as $type)
                <flux:select.option>{{ $type->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="delivery_type" placeholder="Delivery type" clearable class="flex-1">
            @foreach (DeliveryType::cases() as $type)
                <flux:select.option>{{ $type->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="city" placeholder="City" clearable class="flex-1">
            @foreach ($this->cities as $city)
                <flux:select.option>{{ $city }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:modal.trigger name="recipient-form">
            <flux:button variant="primary" icon="plus" class="flex-0">Add</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:table :paginate="$this->items">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">ID</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">Name</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'type'" :direction="$sortDirection" wire:click="sort('type')">Type</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'phone_number'" :direction="$sortDirection" wire:click="sort('phone_number')">Phone number</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'delivery_type'" :direction="$sortDirection" wire:click="sort('delivery_type')">Delivery type</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'city'" :direction="$sortDirection" wire:click="sort('city')">City</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->items as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>{{ $item->id }}</flux:table.cell>
                    <flux:table.cell>{{ $item->name }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" inset="top bottom">
                            {{ $item->type->name }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <a href="tel:{{ $item->phone_number }}">{{ $item->phone_number }}</a>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" inset="top bottom">
                            {{ $item->delivery_type->name }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $item->city }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>
                            <flux:menu>
                                <flux:modal.trigger name="recipient-form">
                                    <flux:menu.item icon="pencil-square" wire:click="edit({{ $item->id }})">Edit</flux:menu.item>
                                </flux:modal.trigger>
                                <flux:menu.item icon="trash" variant="danger">Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell>{{__("app.no_items")}}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <x-flyout name="recipient-form" title="Recipient Details" subtitle="Fill in the information below" position="right">
        <livewire:pages::recipients.recipient-form />
    </x-flyout>
</section>
