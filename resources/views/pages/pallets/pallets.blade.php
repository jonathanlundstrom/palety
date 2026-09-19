<?php

use App\Enumerables\Availability;
use App\Livewire\Components\TableComponent;
use App\Models\Content;
use App\Models\Pallet;
use App\Models\Recipient;
use Illuminate\Database\Eloquent\Collection;
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
    public array $range = [];

    #[Url(except: '')]
    public string $availability = '';

    #[Url(except: '')]
    public string $recipient_id = '';

    #[Url(except: '')]
    public string $content_id = '';

    #[Computed]
    public function items(): LengthAwarePaginator {
        return Pallet::query()
            ->with(['content', 'recipient', 'parcels.content'])
            ->when($this->q, fn($query) => $query
                ->whereAny(['id', 'weight', 'notes'], 'ILIKE', "%{$this->q}%")
                ->orWhereHas('author', fn($q) => $q->where('name', 'ILIKE', "%{$this->q}%"))
            )
            ->when($this->recipient_id, fn($query) => $query->where('recipient_id', $this->recipient_id))
            ->when($this->content_id, fn($query) => $query->hasContent($this->content_id))
            ->when(!empty($this->range), fn($query) => $query
                ->whereDate('created_at', '>=', $this->range['start'])
                ->whereDate('created_at', '<=', $this->range['end'])
            )
            ->when(true, function ($query) {
                return match ($this->availability) {
                    Availability::ANY_STATUS->name => $query,
                    Availability::LOADED_ON_TRANSPORT->name => $query->whereNotNull('transport_id'),
                    default => $query->whereNull('transport_id'),
                };
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    #[Computed]
    protected function recipients(): Collection {
        return Recipient::list(['id', 'name'], 'name')->get();
    }

    #[Computed]
    protected function content(): Collection {
        return Content::list(['id', Content::label()], Content::label())->get();
    }

    public function render(): View {
        return view($this->getViewTemplate())
            ->title(__('pages.pallets.title'));
    }
}

?>
<section wire:poll.60s>
    <x-table.filters :headline="__('pages.pallets.headline')"
                     :subtitle="__('pages.pallets.subtitle')"
                     :modal="$this->modalName">
        <flux:date-picker mode="range" wire:model.live="range" locale="{{ App::getLocale() }}" placeholder="{{ __('app.date_range') }}" with-today week-numbers clearable class="w-full md:flex-1" />

        <flux:select variant="listbox" wire:model.live="availability" placeholder="{{ __('app.availability') }}"
                     clearable class="w-full md:flex-1">
            @foreach (Availability::palletFilters() as $case)
                <flux:select.option value="{{ $case->name }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="recipient_id" placeholder="{{ __('app.recipient') }}" searchable clearable
                     class="w-full md:flex-1">
            @foreach ($this->recipients as $recipient)
                <flux:select.option value="{{ $recipient->id }}">{{ $recipient->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select variant="listbox" wire:model.live="content_id" placeholder="{{ __('app.content.label') }}"
                     searchable clearable class="md:flex-1 !w-auto grow">
            @foreach ($this->content as $content)
                <flux:select.option value="{{ $content->id }}">{{ $content->{Content::label()} }}</flux:select.option>
            @endforeach
        </flux:select>
    </x-table.filters>

    <x-table.container :paginate="$this->items">
        <x-slot:columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection"
                               wire:click="sort('id')">{{ __('app.id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'type'" :direction="$sortDirection"
                               wire:click="sort('type')">{{ __('app.type') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                               wire:click="sort('status')">{{ __('app.status') }}</flux:table.column>
            <flux:table.column>{{ __('app.availability') }}</flux:table.column>
            <flux:table.column>{{ __('app.recipient') }}</flux:table.column>
            <flux:table.column>{{ __('app.content.label') }}</flux:table.column>
            <flux:table.column>{{ __('app.weight.label') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'notes'" :direction="$sortDirection"
                               wire:click="sort('notes')">{{ __('app.notes') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                               wire:click="sort('created_at')">{{ __('app.created_at') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </x-slot:columns>

        @foreach ($this->items as $item)
            @include('pages.pallets._pallet-card')
            @include('pages.pallets._pallet-row')
        @endforeach
    </x-table.container>

    <x-modals.flyout name="{{ $this->modalName }}" position="{{ $this->modalPosition }}">
        <livewire:pages::pallets.pallet-form/>
    </x-modals.flyout>

    <livewire:modals.scanner-modal/>
    <livewire:modals.add-modal/>
</section>
