<flux:table.row key="row-{{$item->id}}" class="hidden lg:table-row">
    <flux:table.cell>{{ $item->id }}</flux:table.cell>
    <flux:table.cell>{{ $item->label_en }}</flux:table.cell>
    <flux:table.cell>{{ $item->label_ua }}</flux:table.cell>
    <flux:table.cell>
        <flux:badge size="sm" inset="top bottom" color="{{ $item->category->color() }}">
            {{ $item->category->label() }}
        </flux:badge>
    </flux:table.cell>
    <flux:table.cell>{{ $item->usage_count }} {{ trans_choice('app.usage.unit', $item->usage_count) }}</flux:table.cell>
    <flux:table.cell>
        <x-item-actions :form="$this->modalName" :object="$item" :deleteDisabled="$item->usage_count > 0"/>
    </flux:table.cell>
</flux:table.row>
