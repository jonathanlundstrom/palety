<flux:table.row key="row-{{$item->id}}" class="hidden lg:table-row">
    <flux:table.cell>{{ $item->id }}</flux:table.cell>
    <flux:table.cell>{{ $item->label_en }}</flux:table.cell>
    <flux:table.cell>{{ $item->label_ua }}</flux:table.cell>
    <flux:table.cell>
        <flux:badge size="sm" inset="top bottom" color="{{ $item->category->color() }}">
            {{ $item->category->label() }}
        </flux:badge>
    </flux:table.cell>
    <flux:table.cell>
        <x-item-actions :form="$this->modalName" :object="$item"/>
    </flux:table.cell>
</flux:table.row>
