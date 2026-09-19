@props([
    'title',
    'subtitle',
])

<div>
    <flux:heading size="lg">{{ $title }}</flux:heading>
    <flux:text class="mt-2">{{ $subtitle }}</flux:text>
</div>
