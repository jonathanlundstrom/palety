@props([
    'form',
    'object',
    'allowDelete' => true,
])

<flux:dropdown {{ $attributes }}>
    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>
    <flux:menu>
        <x-actions.edit-button :form="$form" :object="$object"/>
        {{ $slot }}
        @if ($allowDelete)
            <x-actions.delete-button :object="$object"/>
        @endif
    </flux:menu>
</flux:dropdown>
