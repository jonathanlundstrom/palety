<?php

use App\Enumerables\Availability;
use App\Enumerables\ParcelType;
use App\Livewire\Components\TableComponent;
use App\Models\Parcel;
use App\Models\Content;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
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
    public string $type = '';

    #[Url(except: '')]
    public string $availability = '';

    #[Url(except: '')]
    public string $content_id = '';

    #[Url(except: '')]
    public string $author_id = '';

    /**
     * Mount the Livewire component.
     * Currently used to override parent sorting properties.
     * @return void
     */
    public function mount(): void {
        $this->sortBy = 'id';
        $this->sortDirection = 'desc';
    }

    #[Computed]
    public function items(): LengthAwarePaginator {
        return Parcel::query()
            ->when($this->q, fn($query) => $query->whereAny(
                ['id', 'weight', 'notes'], 'ILIKE', "%{$this->q}%")
            )
            ->when($this->type, fn($query) => $query->where('type', $this->type))
            ->when($this->content_id, fn($query) => $query->whereHas('content', fn($q) => $q->whereKey($this->content_id)))
            ->when($this->author_id, fn($query) => $query->where('user_id', $this->author_id))
            ->when(true, function ($query) {
                return match ($this->availability) {
                    Availability::ANY_STATUS->name => $query,
                    Availability::LOADED_ON_PALLET->name => $query->whereNotNull('pallet_id'),
                    Availability::LOADED_ON_TRANSPORT->name => $query->whereNotNull('transport_id'),
                    Availability::ALREADY_LOADED->name => $query->where(fn ($q) => $q->whereNotNull('pallet_id')->orWhereNotNull('transport_id')),
                    default => $query->whereNull('pallet_id')->whereNull('transport_id'),
                };
            })
            ->with(['content' => fn($query) => $query->orderBy(Content::label())])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate();
    }

    #[Computed]
    protected function content(): Collection {
        return Content::orderBy(Content::label())->get();
    }

    #[Computed]
    protected function users(): Collection {
        return User::list(['id', 'name'], 'name')->get();
    }

    public function render(): View {
        return view($this->getViewTemplate())
            ->title(__('pages.parcels.title'));
    }
}

?>
<section wire:poll.60s>
    <header class="mb-6">
        <flux:heading size="xl" level="1">{{ __('pages.parcels.headline') }}</flux:heading>
        <flux:text class="mb-6 mt-2 text-base">{{ __('pages.parcels.subtitle') }}</flux:text>
        <flux:separator variant="subtle"/>
    </header>

    <div class="flex flex-wrap gap-4 items-center mb-4">
        <flux:input wire:model.live.debounce.500ms="q" icon-trailing="magnifying-glass"
                    placeholder="{{__('app.search')}}" clearable class="w-full md:flex-1"/>

        <flux:select variant="listbox" wire:model.live="type" placeholder="{{ __('app.type') }}" clearable
                     class="w-full md:flex-1">
            @foreach (ParcelType::cases() as $case)
                <flux:select.option value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="availability" placeholder="{{ __('app.availability') }}" clearable
                     class="w-full md:flex-1">
            @foreach (Availability::parcelFilters() as $case)
                <flux:select.option value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="content_id" placeholder="{{ __('app.content.label') }}"
                     searchable clearable class="w-full md:flex-1">
            @foreach ($this->content as $content)
                <flux:select.option value="{{ $content->id }}">{{ $content->{Content::label()} }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="author_id" placeholder="{{ __('app.author') }}" clearable
                     class="flex-1">
            @foreach ($this->users as $user)
                <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:modal.trigger name="{{ $this->modalName }}">
            <flux:button variant="primary" icon="plus" class="flex-0">{{ __('app.add') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="mt-6 mb-6 lg:hidden">
        <flux:separator variant="subtle" text="{{ __('app.all_items') }}" />
    </div>

    <flux:table :paginate="$this->items">
        <flux:table.columns class="hidden lg:table-header-group">
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection"
                               wire:click="sort('id')">{{ __('app.id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'type'" :direction="$sortDirection"
                               wire:click="sort('type')">{{ __('app.type') }}</flux:table.column>
            <flux:table.column>{{ __('app.availability') }}</flux:table.column>
            <flux:table.column>{{ __('app.recipient') }}</flux:table.column>
            <flux:table.column>{{ __('app.content.label') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'weight'" :direction="$sortDirection"
                               wire:click="sort('weight')">{{ __('app.weight.label') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'notes'" :direction="$sortDirection"
                               wire:click="sort('weight')">{{ __('app.notes') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->items as $item)
                @include('pages.parcels._parcel-card')
                @include('pages.parcels._parcel-row')
            @empty
                <flux:table.row>
                    <flux:table.cell>{{ __('app.no_items') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <x-modals.flyout name="{{ $this->modalName }}" title="{{ __('pages.parcels.form.title') }}"
              subtitle="{{ __('pages.parcels.form.subtitle') }}" position="{{ $this->modalPosition }}">
        <livewire:pages::parcels.parcel-form/>
    </x-modals.flyout>
</section>
