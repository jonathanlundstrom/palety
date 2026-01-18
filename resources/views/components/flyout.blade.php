@props([
    'name',
    'title' => '',
    'subtitle' => '',
    'position' => 'right'
])

<flux:modal :name="$name" flyout :position="$position" x-on:close="$wire.dispatch('reset-modal')">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $title }}</flux:heading>
            <flux:text class="mt-2">{{ $subtitle }}</flux:text>
        </div>

        {{ $slot }}
    </div>
</flux:modal>
