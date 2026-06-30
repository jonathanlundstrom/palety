<?php
    use App\Models\Content;
?>
<flux:table.row key="row-{{$item->id}}" class="hidden lg:table-row">
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
    <flux:table.cell>
        @if ($item->recipient)
            <flux:badge size="sm" inset="top bottom" color="zinc">
                {{ $item->recipient->name }}
            </flux:badge>
        @elseif ($item->pallet)
            <flux:badge size="sm" inset="top bottom" color="zinc" icon="square-3-stack-3d">
                {{ $item->pallet->recipient->name }}
            </flux:badge>
        @else
            --
        @endif
    </flux:table.cell>
    <flux:table.cell>
        @php $content = $item->content; @endphp
        <div class="flex flex-wrap items-center gap-1">
            @foreach ($content->take(2) as $type)
                <flux:badge size="sm" inset="top bottom" color="zinc">
                    {{ $type->{Content::label()} }}
                </flux:badge>
            @endforeach
            @if ($content->count() > 2)
                <flux:text size="sm" class="ml-1">+{{ $content->count() - 2 }}</flux:text>
            @endif
        </div>
    </flux:table.cell>
    <flux:table.cell>{{ $item->weight }} {{ __('app.weight.unit') }}</flux:table.cell>
    <flux:table.cell>{{ $item->notes ?: '--' }}</flux:table.cell>
    <flux:table.cell>{{ $item->created_at->format('Y-m-d, H:i') }}</flux:table.cell>
    <flux:table.cell>
        <x-item-actions :form="$this->modalName" :object="$item">
            <x-actions.print-button :object="$item"/>
        </x-item-actions>
    </flux:table.cell>
</flux:table.row>
