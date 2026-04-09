<?php

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
    public string $content_id = '';

    #[Url(except: '')]
    public string $author_id = '';

    #[Computed]
    public function items(): LengthAwarePaginator {
        return Parcel::query()
            ->when($this->q, fn($query) => $query->whereAny(
                ['weight', 'notes'], 'ILIKE', "%{$this->q}%")
            )
            ->when($this->type, fn($query) => $query->where('type', $this->type))
            ->when($this->content_id, fn($query) => $query->whereHas('content', fn($q) => $q->whereKey($this->content_id)))
            ->when($this->author_id, fn($query) => $query->where('user_id', $this->author_id))
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
<section>
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

        <flux:select variant="listbox" wire:model.live="content_id" placeholder="{{ __('app.content.label') }}"
                     clearable class="w-full md:flex-1">
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
                <flux:card class="lg:hidden not-last:mb-4 p-0 rounded-lg overflow-hidden">
                    <div class="flex px-3 py-2 bg-gray-50 dark:bg-white/10 justify-center border-b dark:border-b-0">
                        <div class="flex flex-1 items-center">
                            <div class="flex gap-2">
                                <div class="flex gap-0">
                                    <flux:badge size="sm" inset="top bottom" color="zinc" class="rounded-r-none">
                                        {{ $item->id }}
                                    </flux:badge>

                                    <flux:badge size="sm" inset="top bottom" color="{{ $item->type->color() }}" class="rounded-l-none">
                                        {{ $item->type->label() }}
                                    </flux:badge>
                                </div>

                                <flux:badge size="sm" inset="top bottom" color="{{ $item->getAvailability()->color() }}">
                                    {{ $item->getAvailability()->label() }}
                                </flux:badge>
                            </div>
                        </div>

                        <div class="flex-0 flex items-center gap-2">
                            <flux:text class="text-xs whitespace-nowrap flex">
                                <flux:icon.scale variant="micro" class="mr-1"/>
                                {{ $item->getWeight() }} {{ __('app.weight.unit') }}
                            </flux:text>

                            <flux:dropdown class="relative top-1">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>
                                <flux:menu>
                                    <x-edit-button form="{{ $this->modalName }}" :object="$item"/>
                                    <x-delete-button :object="$item"/>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>

                    <ul>
                        @if ($recipient = $item->recipient ?? $item->pallet?->recipient)
                            <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
                                <flux:icon.user class="flex-none size-4 mt-0.5 mr-2"/>
                                <flux:text class="flex-auto text-sm">
                                    {{ $recipient->name }}
                                    @if($item->pallet)
                                        (via pallet)
                                    @endif
                                </flux:text>
                            </li>
                        @endif

                        <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
                            <flux:icon.clipboard-document-list class="flex-none size-4 mt-1 mr-2"/>
                            <span class="flex flex-auto flex-row flex-wrap gap-1">
                                @foreach ($item->content as $type)
                                    <flux:badge size="sm" color="zinc">
                                        {{ $type->{Content::label()} }}
                                    </flux:badge>
                                @endforeach
                            </span>
                        </li>

                        @if ($notes = $item->notes)
                            <li class="px-3 py-3 flex flex-row flex-nowrap not-last:border-b-1 items-start border-b-gray-100 dark:border-b-white/5">
                                <flux:icon.pencil-square class="flex-none size-4 mt-0.5 mr-2"/>
                                <flux:text class="flex-auto text-sm">
                                    {{ $notes }}
                                </flux:text>
                            </li>
                        @endif
                    </ul>
                </flux:card>

                <!-- Table row for desktop devices -->
                <flux:table.row key="row-{{$item->id}}" class="hidden lg:table-row">
                    <flux:table.cell>{{ $item->id }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" inset="top bottom" color="{{ $item->type->color() }}">
                            {{ $item->type->label() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($item->recipient)
                            <flux:badge size="sm" inset="top bottom" color="lime">
                                {{ $item->recipient->name }}
                            </flux:badge>
                        @elseif ($item->pallet)
                            <flux:badge size="sm" inset="top bottom" color="zinc" icon="rectangle-group">
                                {{ $item->pallet->recipient->name }}
                            </flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @foreach ($item->content as $type)
                            <flux:badge size="sm" inset="top bottom" color="zinc">
                                {{ $type->{Content::label()} }}
                            </flux:badge>
                        @endforeach
                    </flux:table.cell>
                    <flux:table.cell>{{ $item->weight }} {{ __('app.weight.unit') }}</flux:table.cell>
                    <flux:table.cell>{{ $item->notes }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                         inset="top bottom"></flux:button>
                            <flux:menu>
                                <x-edit-button form="{{ $this->modalName }}" :object="$item"/>
                                <x-delete-button :object="$item"/>
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

    <x-flyout name="{{ $this->modalName }}" title="{{ __('pages.parcels.form.title') }}"
              subtitle="{{ __('pages.parcels.form.subtitle') }}" position="{{ $this->modalPosition }}">
        <livewire:pages::parcels.parcel-form/>
    </x-flyout>
</section>
