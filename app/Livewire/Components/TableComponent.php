<?php

namespace App\Livewire\Components;

use App\Enumerables\DeliveryType;
use App\Enumerables\ImportCategory;
use App\Enumerables\PalletType;
use App\Enumerables\ParcelType;
use App\Enumerables\RecipientType;
use App\Enumerables\TransportType;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
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
     * Get the modal name for this form component.
     * Returns the component name with dots replaced by dashes.
     *
     * @return string
     */
    #[Computed]
    public function modalName(): string {
        $component_name = explode('::', $this->getName());
        return array_last($component_name) . '-modal';
    }

    /**
     * Get the view template for this form component.
     * Primarily to be used in the render method to avoid hardcoding the view path.
     * Requires the component to share its name with the containing pages:: folder.
     *
     * Note: This can be avoided by using the full path name when registering the route.
     *
     * @return string
     */
    protected function getViewTemplate(): string {
        $parts = explode('::', $this->getName());
        $template = array_last($parts);
        return array_first($parts).'::'.$template.'.'.$template;
    }

    /**
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
     * Edit an existing resource based on ID and class.
     *
     * @param int $id
     * @param string $class
     * @return void
     */
    public function edit(int $id, string $class): void {
        $this->dispatch('edit-resource', id: $id, class: $class);
    }

    /**
     * Delete an existing resource based on ID and class.
     * Dispatches an event to open the confirmation modal.
     *
     * @param int $id
     * @param string $class
     * @return void
     */
    public function delete(int $id, string $class): void {
        $this->dispatch('confirm-delete', id: $id, class: $class);
    }
}
