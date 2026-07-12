@props([
    'object' => (object) [],
])

<flux:menu.item icon="table-cells" :href="route('transports.import-list.xlsx', $object)">
    {{ __('app.import_list.title') }} (Excel)
</flux:menu.item>
