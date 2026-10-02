@props([
    'paginate',
])

<div class="px-6 pb-3">
    <flux:table bleed :paginate="$paginate->isEmpty() ? null : $paginate" pagination:scroll-to>
        <flux:table.columns class="hidden lg:table-header-group bg-zinc-50 dark:bg-white/5">
            {{ $columns }}
        </flux:table.columns>
        <flux:table.rows>
            {{ $slot }}
        </flux:table.rows>
    </flux:table>

    @if ($paginate->isEmpty())
        <div class="mb-6 lg:my-6 flex flex-col items-center gap-3 py-12 rounded-lg border border-dashed border-zinc-800/15 dark:border-white/20">
            <div class="p-3 rounded-full bg-zinc-100 dark:bg-white/10">
                <flux:icon name="inbox" class="text-zinc-400 dark:text-zinc-300"/>
            </div>
            <flux:text>{{ __('app.no_items') }}</flux:text>
        </div>
    @endif
</div>
