@props([
    'object' => (object) [],
])

<flux:menu.item icon="printer" wire:click="print({{ $object->id }}, '{{ addslashes($object::class) }}')">
    {{ __('app.print_label') }}
</flux:menu.item>
