<?php

use App\Livewire\Components\TableComponent;
use App\Models\Parcel;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Illuminate\View\View;

new class extends TableComponent {

    #[Computed]
    public function items(): LengthAwarePaginator {
        return Parcel::query()
            ->paginate(20);
    }

    /**
     * Render method.
     * @return View
     */
    public function render(): View {
        return view('pages::parcels.parcels')
            ->title(__('navigation.parcels'));
    }
}

?>
<div class="parcels">
    <flux:heading level="1" class="font-bold">{{ __('navigation.parcels') }}</flux:heading>

    <div class="flex flex-wrap gap-4 items-center mb-4">
        <flux:input wire:model.live.debounce.1000ms="q" class="flex-2" icon-trailing="magnifying-glass"
                    placeholder="{{__('app.search')}}" clearable/>
    </div>

    <flux:table :paginate="$this->items">
        <flux:table.columns>
            <flux:table.column>ID</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column>Weight</flux:table.column>
            <flux:table.column>Recipient</flux:table.column>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->items as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>{{ $item->id }}</flux:table.cell>
                    <flux:table.cell>{{ $item->type->name }}</flux:table.cell>
                    <flux:table.cell>{{ $item->weight }}</flux:table.cell>
                    <flux:table.cell></flux:table.cell>
                    <flux:table.cell>{{ $item->created_at }}</flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell>{{__("app.no_items")}}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
