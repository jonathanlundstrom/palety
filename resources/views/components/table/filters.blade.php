@props([
    'headline',
    'subtitle',
    'modal',
])

<header class="mb-6 lg:hidden">
    <flux:heading size="xl" level="1">{{ $headline }}</flux:heading>
    <flux:text class="mb-6 mt-2 text-base">{{ $subtitle }}</flux:text>
</header>

<div class="lg:sticky top-0 z-10 -mx-6 lg:-mx-8 -mt-6 lg:-mt-8 mb-6 lg:mb-0 p-6 lg:p-8 bg-white dark:bg-zinc-800 flex flex-wrap gap-4 items-center border-b-1 border-zinc-800/10 dark:border-b-white/10 lg:dark:border-white/20">
    <flux:input wire:model.live.debounce.500ms="q" icon-trailing="magnifying-glass"
                placeholder="{{ __('app.search') }}" clearable class="w-full md:flex-1"/>

    {{ $slot }}

    <flux:modal.trigger name="{{ $modal }}">
        <flux:button variant="primary" icon="plus" class="flex-0">{{ __('app.add') }}</flux:button>
    </flux:modal.trigger>
</div>
