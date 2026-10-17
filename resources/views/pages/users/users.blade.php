<?php

use App\Enumerables\ImportCategory;
use App\Enumerables\Permission;
use App\Enumerables\Role;
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
        $this->sortBy = $this->sortBy ?? 'name';
        $this->sortDirection = $this->sortDirection ?? 'asc';
    }

    #[Computed]
    public function items(): LengthAwarePaginator {
        return User::query()
            ->when($this->q, fn($query) => $query->whereAny(
                ['name', 'email'], 'ILIKE', "%{$this->q}%")
            )
            ->when($this->role, fn($query) => $query->whereHas('roleAssignments', fn($assignments) => $assignments
                ->where('role', $this->role)
                ->whereNull('scopeable_type')
            ))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render(): View {
        return view($this->getViewTemplate())
            ->title(__('pages.users.title'));
    }
}

?>
<section>
    <x-table.filters :headline="__('pages.users.headline')"
                     :subtitle="__('pages.users.subtitle')"
                     :modal="$this->modalName"
                     :allow-create="auth()->user()->can(Permission::CREATE_USER)"
                     :active-filters="$this->activeFilterCount">
        <flux:select variant="listbox" wire:model.live="role" placeholder="{{ trans_choice('app.role.label', 1) }}"
                     clearable class="md:flex-1 !w-auto grow">
            @foreach (Role::cases() as $case)
                <flux:select.option value="{{ $case->value }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>
    </x-table.filters>

    <x-table.container :paginate="$this->items">
        <x-slot:columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection"
                               wire:click="sort('id')">{{ __('app.id') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                               wire:click="sort('name')">{{ __('app.name') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'email'" :direction="$sortDirection"
                               wire:click="sort('email')">{{ __('app.email') }}</flux:table.column>
            <flux:table.column>{{ trans_choice('app.role.label', 2) }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                               wire:click="sort('created_at')">{{ __('app.created_at') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'updated_at'" :direction="$sortDirection"
                               wire:click="sort('updated_at')">{{ __('app.updated_at') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </x-slot:columns>

        @foreach ($this->items as $item)
            @include('pages.users._user-card')
            @include('pages.users._user-row')
        @endforeach
    </x-table.container>

    @canany([Permission::CREATE_USER, Permission::EDIT_USER])
        <x-modals.flyout name="{{ $this->modalName }}" position="{{ $this->modalPosition }}">
            <livewire:pages::users.user-form/>
        </x-modals.flyout>
    @endcanany
</section>
