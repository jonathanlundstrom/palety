<flux:card class="lg:hidden not-last:mb-4 p-0 rounded-lg overflow-hidden" key="card-{{ $item->id }}">
    <div class="flex px-3 py-2 bg-gray-50 dark:bg-white/10 justify-center border-b dark:border-b-0">
        <div class="flex flex-1 items-center">
            <div class="flex gap-0">
                <flux:badge size="sm" inset="top bottom" color="zinc" class="rounded-r-none">
                    {{ $item->id }}
                </flux:badge>

                <flux:badge size="sm" inset="top bottom" color="{{ $item->role->color() }}" class="rounded-l-none">
                    {{ $item->role->label() }}
                </flux:badge>
            </div>
        </div>

        <div class="flex-0 flex items-center gap-2">
            <x-item-actions :form="$this->modalName" :object="$item" class="relative top-1"/>
        </div>
    </div>

    <ul>
        <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
            <flux:icon.user class="flex-none size-4 mt-0.5 mr-2"/>
            <flux:text class="flex-auto text-sm">
                {{ $item->name }}
            </flux:text>
        </li>

        <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
            <flux:icon.at-symbol class="flex-none size-4 mt-0.5 mr-2"/>
            <flux:text class="flex-auto text-sm">
                {{ $item->email }}
            </flux:text>
        </li>

        <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
            <flux:icon.calendar class="flex-none size-4 mt-0.5 mr-2"/>
            <flux:text class="flex-auto text-sm">
                {{ $item->created_at->format('Y-m-d, H:i') }}
            </flux:text>
        </li>
    </ul>
</flux:card>
