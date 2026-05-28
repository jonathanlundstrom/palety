@props([
    'form',
    'object' => (object) [],
])

<flux:modal.trigger name="{{ $form }}">
    <flux:menu.item icon="pencil-square" wire:click="edit({{ $object->id }}, '{{ addslashes($object::class) }}')">
        {{ __('app.edit') }}
    </flux:menu.item>
</flux:modal.trigger>
