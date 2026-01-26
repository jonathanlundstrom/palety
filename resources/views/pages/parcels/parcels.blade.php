<?php

use App\Enumerables\ParcelType;
use App\Livewire\Components\TableComponent;
use App\Models\Parcel;
use App\Models\Content;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

new class extends TableComponent {
    // TODO: Implement deletion...

    #[On('content-updated')]
    public function refreshList(): void {
        unset($this->items);
    }

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: '')]
    public string $content_id = '';

    #[Computed]
    public function items(): LengthAwarePaginator {
        return Parcel::query()
            ->when($this->q, fn($query) => $query->whereAny(
                ['weight', 'notes'], 'ILIKE', "%{$this->q}%")
            )
            ->when($this->type, fn($query) => $query->where('type', $this->type))
            ->when($this->content_id, fn($query) =>
                $query->whereHas('content', fn($q) => $q->whereKey($this->content_id))
            )
            ->with(['content' => fn($query) => $query->orderBy(Content::label())])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(20);
    }

    #[Computed]
    protected function content(): Collection {
        return Content::orderBy(Content::label())->get();
    }

    public function render(): View {
        return view('pages::parcels.parcels')
            ->title(__('navigation.content'));
    }
}

?>
<section>
    <header class="mb-6">
        <flux:heading size="xl" level="1">{{ __('pages.parcels.headline') }}</flux:heading>
        <flux:text class="mb-6 mt-2 text-base">{{ __('pages.parcels.subtitle') }}</flux:text>
        <flux:separator variant="subtle"/>
    </header>

    <div class="flex flex-wrap gap-4 items-center mb-4">
        <flux:input wire:model.live.debounce.500ms="q" icon-trailing="magnifying-glass" placeholder="{{__('app.search')}}" clearable class="w-full md:flex-1"/>

        <flux:select variant="listbox" wire:model.live="type" placeholder="{{ __('validation.attributes.type') }}" clearable class="w-full md:flex-1">
            @foreach (ParcelType::cases() as $case)
                <flux:select.option value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="content_id" placeholder="{{ __('validation.attributes.content') }}" clearable class="w-full md:flex-1">
            @foreach ($this->content as $content)
                <flux:select.option value="{{ $content->id }}">{{ $content->{Content::label()} }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:modal.trigger name="parcels-form">
            <flux:button variant="primary" icon="plus" class="flex-0">{{ __('app.add') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:table :paginate="$this->items">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection"
                               wire:click="sort('id')">{{ __('validation.attributes.id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'type'" :direction="$sortDirection"
                               wire:click="sort('type')">{{ __('validation.attributes.type') }}</flux:table.column>
            <flux:table.column>{{ __('validation.attributes.content') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'weight'" :direction="$sortDirection"
                               wire:click="sort('weight')">{{ __('validation.attributes.weight') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'notes'" :direction="$sortDirection"
                               wire:click="sort('weight')">{{ __('validation.attributes.notes') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->items as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell>{{ $item->id }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" inset="top bottom" color="{{ $this->color($item->type) }}">
                            {{ $item->type->label() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        @foreach ($item->content as $type)
                            <flux:badge size="sm" inset="top bottom" color="zinc">
                                {{ $type->{Content::label()} }}
                            </flux:badge>
                        @endforeach
                    </flux:table.cell>
                    <flux:table.cell>{{ $item->weight }} {{ __('app.weight.unit') }}</flux:table.cell>
                    <flux:table.cell>{{ $item->notes ?? 'N/A' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                         inset="top bottom"></flux:button>
                            <flux:menu>
                                <flux:modal.trigger name="parcels-form">
                                    <flux:menu.item icon="pencil-square" wire:click="edit({{ $item->id }})">{{ __('app.edit') }}</flux:menu.item>
                                </flux:modal.trigger>
                                <flux:menu.item icon="trash" variant="danger">{{ __('app.delete') }}</flux:menu.item>
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

    <x-flyout name="parcels-form" title="{{ __('pages.parcels.form.title') }}" subtitle="{{ __('pages.parcels.form.subtitle') }}" position="right">
        <livewire:pages::parcels.parcels-form/>
    </x-flyout>
</section>
