<flux:table.row key="row-{{$item->id}}" class="hidden lg:table-row">
    <flux:table.cell>{{ $item->id }}</flux:table.cell>
    <flux:table.cell>
        @if ($item->color)
            <span class="flex items-center gap-2">
                <span class="block size-3 rounded-full" style="background-color: {{ $item->color }}"></span>
                {{ $item->name }}
            </span>
        @else
            {{ $item->name }}
        @endif
    </flux:table.cell>
    <flux:table.cell>
        <flux:badge size="sm" inset="top bottom" color="{{ $item->type->color() }}">
            {{ $item->type->label() }}
        </flux:badge>
    </flux:table.cell>
    <flux:table.cell>
        <a href="tel:{{ str_replace(' ', '', $item->phone_number) }}">{{ phone($item->phone_number)->formatInternational() }}</a>
    </flux:table.cell>
    <flux:table.cell>
        <flux:badge size="sm" inset="top bottom" color="{{ $item->delivery_type->color() }}">
            {{ $item->delivery_type->label() }}
        </flux:badge>
    </flux:table.cell>
    <flux:table.cell>{{ $item->city }}</flux:table.cell>
    <flux:table.cell>
        <x-item-actions :form="$this->modalName" :object="$item"/>
    </flux:table.cell>
</flux:table.row>
