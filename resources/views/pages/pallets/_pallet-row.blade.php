<?php
    use App\Enumerables\PalletType;
?>
<flux:table.row class="hidden lg:table-row" key="row-{{ $item->id }}">
    <flux:table.cell>{{ $item->id }}</flux:table.cell>
    <flux:table.cell>
        <flux:badge size="sm" inset="top bottom" color="{{ $item->type->color() }}">
            {{ $item->type->label() }}
        </flux:badge>
    </flux:table.cell>
    <flux:table.cell>
        <flux:badge size="sm" inset="top bottom" color="{{ $item->getAvailability()->color() }}">
            {{ $item->getAvailability()->label() }}
        </flux:badge>
    </flux:table.cell>
    <flux:table.cell>{{ $item->{$item::label()} ?? '–' }}</flux:table.cell>
    <flux:table.cell>
        <flux:badge size="sm" inset="top bottom" color="zinc">
            {{ $item->recipient->name }}
        </flux:badge>
    </flux:table.cell>
    <flux:table.cell>
        @foreach ($item->getCategories() as $category)
            <flux:badge size="sm" inset="top bottom" color="zinc">
                {{ $category->label() }}
            </flux:badge>
        @endforeach
    </flux:table.cell>
    <flux:table.cell>
        {{ $item->getWeight() }} {{ __('app.weight.unit') }}
        @if ($item->type === PalletType::CALCULATED)
            <flux:badge as="button" size="sm">
                {{ $item->parcels->count() }}
            </flux:badge>
        @endif
    </flux:table.cell>
    <flux:table.cell>
        <x-item-actions :form="$this->modalName" :object="$item"/>
    </flux:table.cell>
</flux:table.row>
