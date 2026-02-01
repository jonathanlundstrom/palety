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
    public function refreshList(): void {
        unset($this->items);
    }

    #[Url(except: '')]
    public string $category = '';

    #[Computed]
    public function items(): LengthAwarePaginator {
        return Content::query()
            ->when($this->q, fn($query) => $query->whereAny(
                ['label_en', 'label_ua'], 'ILIKE', "%{$this->q}%")
            )
            ->when($this->category, fn($query) => $query->where('category', $this->category))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(20);
    }

    public function render(): View {
        return view('pages::content.content')
            ->title(__('pages.content.title'));
    }
}

?>
<section>
    <header class="mb-6">
        <flux:heading size="xl" level="1">{{ __('pages.content.headline') }}</flux:heading>
        <flux:text class="mb-6 mt-2 text-base">{{ __('pages.content.subtitle') }}</flux:text>
        <flux:separator variant="subtle" />
    </header>

    <div class="flex flex-wrap gap-4 items-center mb-4">
        <flux:input wire:model.live.debounce.500ms="q" icon-trailing="magnifying-glass" placeholder="{{__('app.search')}}" clearable class="w-full md:flex-1" />

        <flux:select variant="listbox" wire:model.live="category" placeholder="{{ __('app.category.label') }}" clearable class="w-full md:flex-1">
            @foreach (ImportCategory::cases() as $case)
                <flux:select.option value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:modal.trigger name="{{ $this->modalName }}">
            <flux:button variant="primary" icon="plus" class="flex-0">{{ __('app.add') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:table :paginate="$this->items">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('app.id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'label_en'" :direction="$sortDirection" wire:click="sort('label_en')">{{ __('app.label_en') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'label_ua'" :direction="$sortDirection" wire:click="sort('label_ua')">{{ __('app.label_ua') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'category'" :direction="$sortDirection" wire:click="sort('category')">{{ __('app.category.label') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->items as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>{{ $item->id }}</flux:table.cell>
                    <flux:table.cell>{{ $item->label_en }}</flux:table.cell>
                    <flux:table.cell>{{ $item->label_ua }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" inset="top bottom" color="{{ $this->color($item->category) }}">
                            {{ $item->category->label() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>
                            <flux:menu>
                                <x-edit-button form="{{ $this->modalName }}" :object="$item" />
                                <x-delete-button :object="$item" />
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell>{{ __('app.no_items') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <x-flyout name="{{ $this->modalName }}" title="{{ __('pages.content.form.title') }}" subtitle="{{ __('pages.content.form.subtitle') }}" position="right">
        <livewire:pages::content.content-form />
    </x-flyout>
</section>
