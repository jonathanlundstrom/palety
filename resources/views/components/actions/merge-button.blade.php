@props([
    'object' => (object) [],
])

<flux:modal.trigger name="merge-content">
    <flux:menu.item icon="arrows-right-left" wire:click="merge({{ $object->id }})">
        {{ __('app.merge') }}
    </flux:menu.item>
</flux:modal.trigger>
