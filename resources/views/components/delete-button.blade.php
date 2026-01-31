@props([
    'object' => '',
])

<flux:menu.item icon="trash" variant="danger" wire:click="delete({{ $object->id }}, '{{ addslashes($object::class) }}')">{{ __('app.delete') }}</flux:menu.item>
