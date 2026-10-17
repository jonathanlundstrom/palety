<flux:table.row key="row-{{$item->id}}" class="hidden lg:table-row">
    <flux:table.cell>{{ $item->id }}</flux:table.cell>
    <flux:table.cell>{{ $item->name }}</flux:table.cell>
    <flux:table.cell>{{ $item->email }}</flux:table.cell>
    <flux:table.cell>
        @foreach ($item->roles() as $role)
            <flux:badge size="sm" color="{{ $role->color() }}">{{ $role->label() }}</flux:badge>
        @endforeach
    </flux:table.cell>
    <flux:table.cell>{{ $item->created_at }}</flux:table.cell>
    <flux:table.cell>{{ $item->updated_at }}</flux:table.cell>
    <flux:table.cell>
        @include('pages.users._user-actions')
    </flux:table.cell>
</flux:table.row>
