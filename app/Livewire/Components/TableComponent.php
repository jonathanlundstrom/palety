<?php

namespace App\Livewire\Components;

use App\Enumerables\DeliveryType;
use App\Enumerables\RecipientType;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use phpDocumentor\Reflection\Types\ClassString;
use UnitEnum;

abstract class TableComponent extends Component {
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(as: 'sort')]
    public string $sortBy = 'id';

    #[Url(as: 'direction')]
    public ?string $sortDirection = 'asc';

    /**
     * Handles the sorting logic for the data.
     *
     * Updates the sorting direction if the current sort key matches the provided key.
     * Otherwise, sets the provided key as the new sort key and defaults the sorting direction to ascending.
     * Resets the pagination state after updating the sorting configuration.
     *
     * @param string $key The key to sort the data by.
     * @return void
     */
    public function sort(string $key): void {
        if ($this->sortBy === $key) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $key;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Edit an existing resource based on ID.
     * @param int $id
     * @return void
     */
    public function edit(int $id): void {
        $this->dispatch('edit-resource', id: $id);
    }

    /**
     * Determines the color associated with a given enumeration value.
     *
     * @param UnitEnum $enum An enumeration instance whose name determines the color mapping.
     * @return string The color associated with the provided enum value.
     */
    public function color(UnitEnum $enum): string {
        return match($enum->name) {
            RecipientType::INDIVIDUAL->name => 'lime',
            RecipientType::ORGANISATION->name => 'blue',
            DeliveryType::SELF_PICKUP->name => 'yellow',
            DeliveryType::ADDRESS_DELIVERY->name => 'green',
            DeliveryType::NOVA_POSHTA_DELIVERY->name => 'red',
            default => 'zinc',
        };
    }
}
