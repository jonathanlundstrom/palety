@props([
    'headline',
    'subtitle',
    'modal',
    'activeFilters' => 0,
])

<header class="mx-6 mt-6 lg:hidden">
    <flux:heading size="xl" level="1">{{ $headline }}</flux:heading>
    <flux:text class="mt-2 text-base">{{ $subtitle }}</flux:text>
</header>

<div x-data="{ open: $persist(false).as('table-filters-open') }" class="lg:sticky top-0 z-10 mb-6 lg:mb-0 p-6 bg-white dark:bg-zinc-800 flex flex-wrap gap-4 items-center border-b-1 border-zinc-800/10 dark:border-b-white/10 lg:dark:border-white/20">
    <flux:input wire:model.live.debounce.500ms="q" icon-trailing="magnifying-glass"
                placeholder="{{ __('app.search') }}" clearable class="flex-1"/>

    @if (! $slot->isEmpty())
        <div class="relative md:hidden">
            <flux:button icon="adjustments-horizontal" x-on:click="open = ! open" x-bind:aria-expanded="open"
                         x-bind:class="open && '!bg-zinc-800/10 dark:!bg-white/20 shadow-none'"
                         aria-label="{{ __('app.filters') }}"/>

            @if ($activeFilters > 0)
                <flux:badge size="sm" inset="top bottom" color="blue" variant="solid"
                            class="absolute -top-2 -right-2 pointer-events-none">{{ $activeFilters }}</flux:badge>
            @endif
        </div>

        {{-- Collapsible on mobile; on desktop the filters flow inline with the toolbar, overriding x-show and x-cloak. --}}
        <div x-show="open" x-cloak class="order-last w-full flex flex-col gap-4 md:!contents">
            {{ $slot }}
        </div>
    @endif

    <flux:modal.trigger name="{{ $modal }}">
        <flux:button variant="primary" icon="plus" class="flex-0">{{ __('app.add') }}</flux:button>
    </flux:modal.trigger>
</div>
