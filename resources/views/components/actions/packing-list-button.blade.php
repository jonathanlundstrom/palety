@props([
    'object' => (object) [],
])

<flux:menu.item icon="document-text" :href="route('transports.packing-list.pdf', $object)">
    {{ __('app.packing_list') }} (PDF)
</flux:menu.item>
<flux:menu.item icon="table-cells" :href="route('transports.packing-list.xlsx', $object)">
    {{ __('app.packing_list') }} (Excel)
</flux:menu.item>
