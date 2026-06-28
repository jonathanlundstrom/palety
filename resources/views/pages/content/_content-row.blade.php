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
        <x-item-actions :form="$this->modalName" :object="$item" :allowDelete="$item->usage_count === 0">
            @if ($item->usage_count > 0)
                <x-actions.merge-button :object="$item"/>
            @endif
        </x-item-actions>
    </flux:table.cell>
</flux:table.row>
