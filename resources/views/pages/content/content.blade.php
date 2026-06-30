<?php

use App\Enumerables\ImportCategory;
use App\Livewire\Components\TableComponent;
use App\Models\Content;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

new class extends TableComponent {

    #[On('items-updated')]
    public function onItemsUpdated(): void {
        unset($this->items);
    }

    #[Url(except: '')]
    public string $category = '';

    /**
     * Mount the Livewire component.
     * Currently used to override parent sorting properties.
     * @return void
     */
    public function mount(): void {
        $this->sortBy = $this->sortBy ?? 'label_en';
        $this->sortDirection = $this->sortDirection ?? 'asc';
    }

    #[Computed]
    public function items(): LengthAwarePaginator {
        return Content::query()
            ->withCount(['parcels', 'pallets'])
            ->when($this->q, fn($query) => $query->whereAny(
                ['id', 'label_en', 'label_ua'], 'ILIKE', "%{$this->q}%")
            )
            ->when($this->category, fn($query) => $query->where('category', $this->category))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate();
    }

    public function render(): View {
        return view($this->getViewTemplate())
            ->title(__('pages.content.title'));
    }
}

?>
<section wire:poll.60s>
    <header class="mb-6">
        <flux:heading size="xl" level="1">{{ __('pages.content.headline') }}</flux:heading>
        <flux:text class="mb-6 mt-2 text-base">{{ __('pages.content.subtitle') }}</flux:text>
        <flux:separator variant="subtle" />
    </header>

    <div class="flex flex-wrap gap-4 items-center mb-4">
        <flux:input wire:model.live.debounce.500ms="q" icon-trailing="magnifying-glass" placeholder="{{__('app.search')}}" clearable class="w-full md:flex-1" />

        <flux:select variant="listbox" wire:model.live="category" placeholder="{{ trans_choice('app.category.label', 1) }}" clearable class="md:flex-1 !w-auto grow">
            @foreach (ImportCategory::cases() as $case)
                <flux:select.option value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:modal.trigger name="{{ $this->modalName }}">
            <flux:button variant="primary" icon="plus" class="flex-0">{{ __('app.add') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="mt-6 mb-6 lg:hidden">
        <flux:separator variant="subtle" text="{{ __('app.all_items') }}"/>
    </div>

    <flux:table :paginate="$this->items">
        <flux:table.columns class="hidden lg:table-header-group">
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('app.id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'label_en'" :direction="$sortDirection" wire:click="sort('label_en')">{{ __('app.label_en') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'label_ua'" :direction="$sortDirection" wire:click="sort('label_ua')">{{ __('app.label_ua') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'category'" :direction="$sortDirection" wire:click="sort('category')">{{ trans_choice('app.category.label', 1) }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'parcels_count'" :direction="$sortDirection" wire:click="sort('parcels_count')">{{ __('app.usage.label') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->items as $item)
                @include('pages.content._content-card')
                @include('pages.content._content-row')
            @empty
                <flux:table.row>
                    <flux:table.cell>{{ __('app.no_items') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <x-modals.flyout name="{{ $this->modalName }}" title="{{ __('pages.content.form.title') }}" subtitle="{{ __('pages.content.form.subtitle') }}" position="{{ $this->modalPosition }}">
        <livewire:pages::content.content-form />
    </x-modals.flyout>
</section>
