@props([
    'object' => (object) [],
])

<flux:menu.item icon="arrow-down-tray" :href="route('transports.packing-list.pdf', $object)">
    {{ __('app.packing_list') }}
</flux:menu.item>
