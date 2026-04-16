@props([
    'form',
    'object',
    'allowDelete' => true,
])

<flux:dropdown {{ $attributes }}>
    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>
    <flux:menu>
        <x-edit-button :form="$form" :object="$object"/>
        @if ($allowDelete)
            <x-delete-button :object="$object"/>
        @endif
    </flux:menu>
</flux:dropdown>
