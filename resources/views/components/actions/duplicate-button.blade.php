@props([
    'form',
    'object' => (object) [],
])

<flux:menu.item
    icon="document-duplicate"
    x-on:click="$flux.modal('{{ $form }}').show(); $dispatch('edit-init')"
    wire:click="duplicate({{ $object->id }}, '{{ addslashes($object::class) }}')">
    {{ __('app.duplicate') }}
</flux:menu.item>
