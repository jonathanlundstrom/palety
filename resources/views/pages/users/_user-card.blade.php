<flux:card class="mx-6 lg:hidden not-last:mb-4 p-0 rounded-lg overflow-hidden" key="card-{{ $item->id }}">
    <div class="flex px-2 py-2.5 bg-gray-50 dark:bg-white/10 justify-center border-b dark:border-b-0">
        <div class="flex flex-1 items-center">
            <div class="flex gap-0">
                <flux:badge size="sm" inset="top bottom" color="zinc" class="rounded-r-none">
                    {{ $item->id }}
                </flux:badge>

                @foreach ($item->roles() as $role)
                    <flux:badge size="sm" inset="top bottom" color="{{ $role->color() }}" class="rounded-l-none">
                        {{ $role->label() }}
                    </flux:badge>
                @endforeach
            </div>
        </div>

        <div class="flex-0 flex items-center gap-2">
            @include('pages.users._user-actions')
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
                {{ $item->created_at }}
            </flux:text>
        </li>
    </ul>
</flux:card>
