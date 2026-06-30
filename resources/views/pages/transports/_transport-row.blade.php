<flux:table.row key="row-{{$item->id}}" class="hidden lg:table-row">
    <flux:table.cell>{{ $item->id }}</flux:table.cell>
    <flux:table.cell>
        <flux:badge size="sm" inset="top bottom" color="{{ $item->type->color() }}">
            {{ $item->type->label() }}
        </flux:badge>
    </flux:table.cell>
    <flux:table.cell>{{ $item->pallets_count }} {{ trans_choice('app.pieces', $item->pallets_count) }}</flux:table.cell>
    <flux:table.cell>{{ $item->parcels_count }} {{ trans_choice('app.pieces', $item->parcels_count) }}</flux:table.cell>
    <flux:table.cell>{{ $item->getWeight() }} {{ __('app.weight.unit') }}</flux:table.cell>
    <flux:table.cell>
        <flux:badge size="sm" inset="top bottom" color="{{ $item->status->color() }}">
            {{ $item->status->label() }}
        </flux:badge>
    </flux:table.cell>
    <flux:table.cell>{{ $item->notes ?: '––' }}</flux:table.cell>
    <flux:table.cell>{{ $item->created_at->format('Y-m-d, H:i') }}</flux:table.cell>
    <flux:table.cell>{{ $item->delivered_at?->format('Y-m-d, H:i') ?? '--' }}</flux:table.cell>
    <flux:table.cell>
        <x-item-actions :form="$this->modalName" :object="$item">
            <x-actions.packing-list-button :object="$item"/>
        </x-item-actions>
    </flux:table.cell>
</flux:table.row>
