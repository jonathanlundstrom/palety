<flux:dropdown {{ $attributes }}>
    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>
    <flux:menu>
        {{ $slot }}
    </flux:menu>
</flux:dropdown>
