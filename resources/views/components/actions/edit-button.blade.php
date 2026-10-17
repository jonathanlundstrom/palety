@props([
    'form',
    'object' => (object) [],
    'disabled' => false,
])

<flux:menu.item
    icon="pencil-square"
    :disabled="$disabled"
    x-on:click="$flux.modal('{{ $form }}').show(); $dispatch('edit-init')"
    wire:click="edit({{ $object->id }}, '{{ addslashes($object::class) }}')">
    {{ __('app.edit') }}
</flux:menu.item>
