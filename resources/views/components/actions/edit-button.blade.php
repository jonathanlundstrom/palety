@props([
    'form',
    'object' => (object) [],
])

<flux:menu.item
    icon="pencil-square"
    x-on:click="$flux.modal('{{ $form }}').show(); $dispatch('edit-init')"
    wire:click="edit({{ $object->id }}, '{{ addslashes($object::class) }}')">
    {{ __('app.edit') }}
</flux:menu.item>
