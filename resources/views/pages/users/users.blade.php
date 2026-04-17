<?php

use App\Enumerables\ImportCategory;
use App\Enumerables\UserRole;
use App\Livewire\Components\TableComponent;
use App\Models\User;
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
    public string $role = '';

    /**
     * Mount the Livewire component.
     * Currently used to override parent sorting properties.
     * @return void
     */
    public function mount(): void {
        $this->sortBy = 'name';
    }

    #[Computed]
    public function items(): LengthAwarePaginator {
        return User::query()
            ->when($this->q, fn($query) => $query->whereAny(
                ['name', 'email'], 'ILIKE', "%{$this->q}%")
            )
            ->when($this->role, fn($query) => $query->where('role', $this->role))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate();
    }

    public function render(): View {
        return view($this->getViewTemplate())
            ->title(__('pages.users.title'));
    }
}

?>
<section>
    <header class="mb-6">
        <flux:heading size="xl" level="1">{{ __('pages.users.headline') }}</flux:heading>
        <flux:text class="mb-6 mt-2 text-base">{{ __('pages.users.subtitle') }}</flux:text>
        <flux:separator variant="subtle"/>
    </header>

    <div class="flex flex-wrap gap-4 items-center mb-4">
        <flux:input wire:model.live.debounce.500ms="q" icon-trailing="magnifying-glass"
                    placeholder="{{__('app.search')}}" clearable class="w-full md:flex-1"/>

        <flux:select variant="listbox" wire:model.live="role" placeholder="{{ trans_choice('app.role.label', 1) }}"
                     clearable class="flex-1">
            @foreach (UserRole::cases() as $case)
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
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection"
                               wire:click="sort('id')">{{ __('app.id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                               wire:click="sort('name')">{{ __('app.name') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'email'" :direction="$sortDirection"
                               wire:click="sort('email')">{{ __('app.email') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'role'" :direction="$sortDirection"
                               wire:click="sort('role')">{{ trans_choice('app.role.label', 1) }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                               wire:click="sort('created_at')">{{ __('app.created_at') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'updated_at'" :direction="$sortDirection"
                               wire:click="sort('updated_at')">{{ __('app.updated_at') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->items as $item)
                @include('pages.users._user-card')
                @include('pages.users._user-row')
            @empty
                <flux:table.row>
                    <flux:table.cell>{{ __('app.no_items') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <x-flyout name="{{ $this->modalName }}" title="{{ __('pages.users.form.title') }}"
              subtitle="{{ __('pages.users.form.subtitle') }}" position="{{ $this->modalPosition }}">
        <livewire:pages::users.user-form/>
    </x-flyout>
</section>
