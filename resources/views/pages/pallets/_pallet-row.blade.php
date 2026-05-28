<?php
    use App\Enumerables\PalletType;
    use App\Models\Content;
?>
<flux:table.row class="hidden lg:table-row" key="row-{{ $item->id }}">
    <flux:table.cell>{{ $item->id }}</flux:table.cell>
    <flux:table.cell>
        <flux:badge size="sm" inset="top bottom" color="{{ $item->type->color() }}">
            {{ $item->type->label() }}
        </flux:badge>
    </flux:table.cell>
    <flux:table.cell>
        <flux:badge size="sm" inset="top bottom" color="{{ $item->status->color() }}">
            {{ $item->status->label() }}
        </flux:badge>
    </flux:table.cell>
    <flux:table.cell>
        <flux:badge size="sm" inset="top bottom" color="{{ $item->getAvailability()->color() }}">
            {{ $item->getAvailability()->label() }}
        </flux:badge>
    </flux:table.cell>
    <flux:table.cell>
        <flux:badge size="sm" inset="top bottom" color="zinc">
            {{ $item->recipient->name }}
        </flux:badge>
    </flux:table.cell>
    <flux:table.cell>
        @php $content = $item->displayContent(); @endphp
        <div class="flex flex-wrap items-center gap-1">
            @foreach ($content->take(3) as $type)
                <flux:badge size="sm" inset="top bottom" color="zinc">
                    {{ $type->{Content::label()} }}
                </flux:badge>
            @endforeach
            @if ($content->count() > 3)
                <flux:text size="sm" class="ml-1">+{{ $content->count() - 3 }}</flux:text>
            @endif
        </div>
    </flux:table.cell>
    <flux:table.cell>
        {{ $item->getWeight() }} {{ __('app.weight.unit') }}
        @if ($item->type === PalletType::CALCULATED)
            <flux:badge inset="top bottom" size="sm" color="zinc">
                {{ $item->parcels->count() }}
            </flux:badge>
        @endif
    </flux:table.cell>
    <flux:table.cell>
        <x-item-actions :form="$this->modalName" :object="$item">
            <x-actions.print-button :object="$item"/>
        </x-item-actions>
    </flux:table.cell>
</flux:table.row>
