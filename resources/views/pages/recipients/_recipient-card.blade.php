@php use App\Enumerables\DeliveryType;use App\Enumerables\RecipientType; @endphp
<flux:card class="lg:hidden not-last:mb-4 p-0 rounded-lg overflow-hidden" key="card-{{ $item->id }}">
    <div class="flex px-3 py-2 bg-gray-50 dark:bg-white/10 justify-center border-b dark:border-b-0">
        <div class="flex flex-1 items-center">
            <div class="flex gap-1">
                <div class="flex gap-0">
                    <flux:badge size="sm" inset="top bottom" color="zinc" class="rounded-r-none">
                        {{ $item->id }}
                    </flux:badge>

                    <flux:badge size="sm" inset="top bottom" color="{{ $item->type->color() }}" class="rounded-l-none">
                        {{ $item->type->label() }}
                    </flux:badge>
                </div>

                <flux:badge size="sm" inset="top bottom" color="{{ $item->delivery_type->color() }}">
                    {{ $item->delivery_type->label() }}
                </flux:badge>
            </div>
        </div>

        <div class="flex-0 flex items-center gap-2">
            <x-item-actions :form="$this->modalName" :object="$item" class="relative top-1"/>
        </div>
    </div>

    <ul>
        @if ($item->type === RecipientType::ORGANISATION)
            <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
                <flux:icon.building-office-2 class="flex-none size-4 mt-0.5 mr-2"/>
                <flux:text class="flex-auto text-sm">
                    {{ $item->name }}
                </flux:text>
            </li>
        @else
            <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
                <flux:icon.user class="flex-none size-4 mt-0.5 mr-2"/>
                <flux:text class="flex-auto text-sm">
                    {{ $item->name }}
                    @if ($item->parent)
                        <span class="opacity-50">({{ $item->parent->name }})</span>
                    @endif
                </flux:text>
            </li>
        @endif

        @if ($item->type === RecipientType::ORGANISATION)
            <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
                <flux:icon.viewfinder-circle class="flex-none size-4 mt-0.5 mr-2"/>
                <flux:text class="flex-auto text-sm">
                    {{ $item->organisation_number }}
                </flux:text>
            </li>

            <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
                <flux:icon.user class="flex-none size-4 mt-0.5 mr-2"/>
                <flux:text class="flex-auto text-sm">
                    {{ $item->reference }}
                </flux:text>
            </li>
        @endif

        @if ($item->email)
            <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
                <flux:icon.at-symbol class="flex-none size-4 mt-0.5 mr-2"/>
                <flux:text class="flex-auto text-sm">
                    {{ $item->email }}
                </flux:text>
            </li>
        @endif

        <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
            <flux:icon.phone class="flex-none size-4 mt-0.5 mr-2"/>
            <flux:text class="flex-auto text-sm">
                <a href="tel:{{ str_replace(' ', '', $item->phone_number) }}">{{ phone($item->phone_number)->formatInternational() }}</a>
            </flux:text>
        </li>

        @if ($item->delivery_type === DeliveryType::NOVA_POSHTA_DELIVERY)
            <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
                <flux:icon.map-pin class="flex-none size-4 mt-0.5 mr-2"/>
                <flux:text class="flex-auto text-sm">
                    {{ __('app.nova_poshta') }} #{{ $item->nova_poshta_id }}, {{ $item->city }}
                </flux:text>
            </li>
        @elseif ($item->delivery_type === DeliveryType::ADDRESS_DELIVERY)
            <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
                <flux:icon.map-pin class="flex-none size-4 mt-0.5 mr-2"/>
                <flux:text class="flex-auto text-sm">
                    {{ $item->address }}, {{ $item->zipcode }} {{ $item->city }}
                </flux:text>
            </li>
        @elseif ($item->delivery_type === DeliveryType::SELF_PICKUP)
            <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
                <flux:icon.map-pin class="flex-none size-4 mt-0.5 mr-2"/>
                <flux:text class="flex-auto text-sm">
                    {{ $item->city }}
                </flux:text>
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
