@props([
    'name',
    'position' => 'right',
    'class' => 'max-w-sm',
])

<flux:modal
    :name="$name"
    flyout
    :position="$position"
    :class="$class"
    x-data="{ loading: false }"
    @edit-init.window="loading = true"
    @edit-hydrated.window="loading = false"
    x-on:close="$wire.dispatch('reset-modal'); loading = false">
    <div class="space-y-6" x-show="loading">
        <flux:skeleton.group animate="shimmer" class="space-y-2">
            <flux:skeleton.line class="h-6 w-1/2 rounded-lg"/>
            <flux:skeleton.line class="w-3/4"/>
        </flux:skeleton.group>

        <flux:skeleton.group animate="shimmer" class="space-y-2">
            <flux:skeleton.line class="mb-2 w-1/4"/>
            <flux:skeleton.line class="h-10 w-full rounded-lg"/>
        </flux:skeleton.group>

        <flux:skeleton.group animate="shimmer" class="space-y-2">
            <flux:skeleton.line class="mb-2 w-1/4"/>
            <flux:skeleton.line class="h-10 w-full rounded-lg"/>
        </flux:skeleton.group>

        <flux:skeleton.group animate="shimmer" class="space-y-2">
            <flux:skeleton.line class="mb-2 w-1/4"/>
            <flux:skeleton.line class="h-10 w-full rounded-lg"/>
        </flux:skeleton.group>
    </div>

    <div x-show="!loading">
        {{ $slot }}
    </div>
</flux:modal>
