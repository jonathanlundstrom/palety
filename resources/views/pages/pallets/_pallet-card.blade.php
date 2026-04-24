<?php use App\Models\Content; ?>
<flux:card class="lg:hidden not-last:mb-4 p-0 rounded-lg overflow-hidden" key="card-{{ $item->id }}">
    <div class="flex px-3 py-2 bg-gray-50 dark:bg-white/10 justify-center border-b dark:border-b-0">
        <div class="flex flex-1 items-center">
            <div class="flex gap-1">
                <div class="flex gap-0">
                    <flux:badge size="sm" inset="top bottom" color="zinc" class="rounded-r-none">
                        {{ $item->id }}
                    </flux:badge>

                    <flux:badge size="sm" inset="top bottom" color="{{ $item->type->color() }}"
                                class="rounded-l-none">
                        {{ $item->type->label() }}
                    </flux:badge>
                </div>

                <flux:badge size="sm" inset="top bottom"
                            color="{{ $item->getAvailability()->color() }}">
                    {{ $item->getAvailability()->label() }}
                </flux:badge>
            </div>
        </div>

        <div class="flex-0 flex items-center gap-2">
            <flux:text class="text-xs whitespace-nowrap flex">
                <flux:icon.scale variant="micro" class="mr-1"/>
                {{ $item->getWeight() }} {{ __('app.weight.unit') }}
            </flux:text>

            <x-item-actions :form="$this->modalName" :object="$item" class="relative top-1"/>
        </div>
    </div>

    <ul>
        <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
            <flux:icon.check-circle class="flex-none size-4 mt-1 mr-2"/>
            <flux:badge size="sm" color="{{ $item->status->color() }}">
                {{ $item->status->label() }}
            </flux:badge>
        </li>

        @if ($recipient = $item->recipient ?? $item->pallet?->recipient)
            <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
                <flux:icon.user class="flex-none size-4 mt-0.5 mr-2"/>
                <flux:text class="flex-auto text-sm">
                    {{ $recipient->name }}
                    @if($item->pallet)
                        (via pallet)
                    @endif
                </flux:text>
            </li>
        @endif

        @if ($item->displayContent()->isNotEmpty())
            <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
                <flux:icon.clipboard-document-list class="flex-none size-4 mt-1 mr-2"/>
                <span class="flex flex-auto flex-row flex-wrap gap-1">
                    @foreach ($item->displayContent() as $type)
                        <flux:badge size="sm" color="zinc">
                            {{ $type->{Content::label()} }}
                        </flux:badge>
                    @endforeach
                </span>
            </li>
        @endif

        @if ($notes = $item->notes)
            <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
                <flux:icon.pencil-square class="flex-none size-4 mt-0.5 mr-2"/>
                <flux:text class="flex-auto text-sm">
                    {{ $notes }}
                </flux:text>
            </li>
        @endif
    </ul>
</flux:card>
