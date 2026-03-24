<?php

use App\Models\Parcel;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {

    #[Locked]
    public Parcel $item;

    #[Locked]
    public string $modalName;

}

?>
<flux:card class="lg:hidden not-last:mb-4 p-0 rounded-lg overflow-hidden">
    <div class="flex px-3 py-2 bg-gray-50 dark:bg-white/10 justify-center border-b dark:border-b-0">
        <div class="flex flex-1 items-center">
            <div class="flex gap-2">
                <div class="flex gap-0">
                    <flux:badge size="sm" inset="top bottom" color="zinc" class="rounded-r-none">
                        {{ $item->id }}
                    </flux:badge>

                    <flux:badge size="sm" inset="top bottom" color="{{ $item->type->color() }}" class="rounded-l-none">
                        {{ $item->type->label() }}
                    </flux:badge>
                </div>

                @if ($item->recipient)
                    <flux:badge size="sm" inset="top bottom" color="lime">
                        {{ $item->recipient->name }}
                    </flux:badge>
                @elseif ($item->pallet)
                    <flux:badge size="sm" inset="top bottom" color="lime" icon="rectangle-group">
                        {{ $item->pallet->recipient->name }}
                    </flux:badge>
                @endif
            </div>
        </div>

        <div class="flex-0 flex items-center gap-2">
            <flux:text class="text-xs whitespace-nowrap">{{ $item->weight }} {{ __('app.weight.unit') }}</flux:text>
            {{ $slot }}
        </div>
    </div>

    <div class="flex flex-row flex-wrap gap-2 min-h-fit px-3 py-3">
        <flux:text class="text-sm">{{ $item->contentList() }}</flux:text>
    </div>

    @if ($item->notes)
        <flux:separator variant="subtle"/>
        <div class="flex flex-row flex-wrap gap-2 min-h-fit px-3 py-3">
            <flux:text class="text-sm" variant="subtle">
                <strong class="font-semibold">Notes:</strong> {{ $item->notes }}
            </flux:text>
        </div>
    @endif
</flux:card>
