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
            ->paginate($this->perPage);
    }

    public function render(): View {
        return view($this->getViewTemplate())
            ->title(__('pages.content.title'));
    }
}

?>
<section wire:poll.60s>
    <x-table.filters :headline="__('pages.content.headline')"
                     :subtitle="__('pages.content.subtitle')"
                     :modal="$this->modalName">
        <flux:select variant="listbox" wire:model.live="category" placeholder="{{ trans_choice('app.category.label', 1) }}" clearable class="md:flex-1 !w-auto grow">
            @foreach (ImportCategory::cases() as $case)
                <flux:select.option value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>
    </x-table.filters>

    <x-table.container :paginate="$this->items">
        <x-slot:columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('app.id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'label_en'" :direction="$sortDirection" wire:click="sort('label_en')">{{ __('app.label_en') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'label_ua'" :direction="$sortDirection" wire:click="sort('label_ua')">{{ __('app.label_ua') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'category'" :direction="$sortDirection" wire:click="sort('category')">{{ trans_choice('app.category.label', 1) }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'parcels_count'" :direction="$sortDirection" wire:click="sort('parcels_count')">{{ __('app.usage.label') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </x-slot:columns>

        @foreach ($this->items as $item)
            @include('pages.content._content-card')
            @include('pages.content._content-row')
        @endforeach
    </x-table.container>

    <x-modals.flyout name="{{ $this->modalName }}" position="{{ $this->modalPosition }}">
        <livewire:pages::content.content-form />
    </x-modals.flyout>
</section>
