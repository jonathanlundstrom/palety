<flux:table.row key="row-{{$item->id}}" class="hidden lg:table-row">
    <flux:table.cell>{{ $item->id }}</flux:table.cell>
    <flux:table.cell>{{ $item->name }}</flux:table.cell>
    <flux:table.cell>{{ $item->email }}</flux:table.cell>
    <flux:table.cell>
        <flux:badge size="sm" color="{{ $item->role->color() }}">{{ $item->role->label() }}</flux:badge>
    </flux:table.cell>
    <flux:table.cell>{{ $item->created_at->format('Y-m-d, H:i') }}</flux:table.cell>
    <flux:table.cell>{{ $item->updated_at->format('Y-m-d, H:i') }}</flux:table.cell>
    <flux:table.cell>
        <x-item-actions :form="$this->modalName" :object="$item" :allow-delete="false"/>
    </flux:table.cell>
</flux:table.row>
