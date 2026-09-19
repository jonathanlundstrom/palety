@props([
    'paginate',
])

<div class="px-0 lg:[--flux-bleed:2rem]">
    <flux:table bleed :paginate="$paginate" pagination:scroll-to>
        <flux:table.columns class="hidden lg:table-header-group bg-zinc-50 dark:bg-white/5">
            {{ $columns }}
        </flux:table.columns>
        <flux:table.rows>
            {{ $slot }}

            @if ($paginate->isEmpty())
                <flux:table.row>
                    <flux:table.cell>{{ __('app.no_items') }}</flux:table.cell>
                </flux:table.row>
            @endif
        </flux:table.rows>
    </flux:table>
</div>
