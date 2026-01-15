<?php

use App\Enumerables\DeliveryType;
use App\Enumerables\RecipientType;
use App\Livewire\Components\TableComponent;
use App\Models\Recipient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Illuminate\View\View;

new class extends TableComponent {

    #[Computed]
    public function items(): LengthAwarePaginator {
        return Recipient::query()
            ->paginate(20);
    }

    #[Computed]
    protected function cities(): Collection {
        return Recipient::all('city')
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
<div class="parcels">
    <div class="flex flex-wrap gap-4 items-center mb-4">
        <flux:input wire:model.live.debounce.1000ms="q" icon-trailing="magnifying-glass"
                    placeholder="{{__('app.search')}}" clearable class="flex-1"/>

        <flux:select variant="listbox" wire:model="type" placeholder="Type" clearable class="flex-1">
            @foreach (RecipientType::cases() as $type)
                <flux:select.option>{{ $type->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model="delivery_type" placeholder="Delivery type" clearable class="flex-1">
            @foreach (DeliveryType::cases() as $type)
                <flux:select.option>{{ $type->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model="city" placeholder="City" clearable class="flex-1">
            @foreach ($this->cities as $city)
                <flux:select.option>{{ $city }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:modal.trigger name="add-recipient">
            <flux:button variant="primary" icon="plus" class="flex-0">Add</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:table :paginate="$this->items">
        <flux:table.columns>
            <flux:table.column sortable>ID</flux:table.column>
            <flux:table.column sortable>Name</flux:table.column>
            <flux:table.column sortable>Type</flux:table.column>
            <flux:table.column sortable>Delivery</flux:table.column>
            <flux:table.column sortable>City</flux:table.column>
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
                        <flux:badge size="sm" inset="top bottom">
                            {{ $item->delivery_type->name }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $item->city }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                     inset="top bottom"></flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell>{{__("app.no_items")}}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <x-flyout name="add-recipient" title="Recipient Details" subtitle="Fill in the information below" position="right">
        <livewire:pages::recipients.recipient-form />
    </x-flyout>
</div>
