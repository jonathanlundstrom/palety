@props([
    'object' => (object) [],
    'disabled' => false,
])

<flux:modal.trigger name="delete-confirmation">
    <flux:menu.item icon="trash" variant="danger" :disabled="$disabled" wire:click="delete({{ $object->id }}, '{{ addslashes($object::class) }}')">{{ __('app.delete') }}</flux:menu.item>
</flux:modal.trigger>
