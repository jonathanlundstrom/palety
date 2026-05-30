<?php

namespace App\Livewire\Components;

use App\Events\PalletSaved;
use App\Events\ParcelSaved;
use App\Models\Pallet;
use App\Models\Parcel;
use Exception;
use Flux\Flux;
use hisorange\BrowserDetect\Facade as Browser;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

abstract class TableComponent extends Component {
    use WithPagination;

    #[Url(except: '')]
    public string $q = '';

    #[Url(as: 'sort')]
    public string $sortBy = 'id';

    #[Url(as: 'direction')]
    public ?string $sortDirection = 'asc';

    /**
     * Get the modal name for the attached form component.
     * Returns the component name with dots replaced by dashes.
     */
    #[Computed]
    public function modalName(): string {
        $component_name = explode('::', $this->getName());

        return array_last($component_name).'-modal';
    }

    /**
     * Get the modal position to use for the attached form component.
     * Returns 'right' for desktop devices and 'bottom' for mobile devices.
     */
    #[Computed]
    public function modalPosition(): string {
        return Browser::isDesktop() ? 'right' : 'bottom';
    }

    /**
     * Get the view template for this form component.
     * Primarily to be used in the render method to avoid hardcoding the view path.
     * Requires the component to share its name with the containing pages:: folder.
     *
     * Note: This can be avoided by using the full path name when registering the route.
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
     * @param  string  $key  The key to sort the data by.
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
     */
    public function edit(int $id, string $class): void {
        $this->dispatch('edit-resource', id: $id, class: $class);
    }

    /**
     * Print a label for the specified resource.
     */
    public function print($id, string $class): void {
        try {
            $object = app()->make($class)::find($id);

            match ($class) {
                Parcel::class => event(new ParcelSaved($object)),
                Pallet::class => event(new PalletSaved($object)),
                default => throw new Exception('Unsupported object type'),
            };

            Flux::toast(
                text: __('toasts.label.printing.success'),
                variant: 'success',
            );
        } catch (Exception) {
            Flux::toast(
                text: __('toasts.label.printing.failed'),
                variant: 'danger',
            );
        }
    }

    /**
     * Delete an existing resource based on ID and class.
     * Dispatches an event to open the confirmation modal.
     */
    public function delete(int $id, string $class): void {
        $this->dispatch('confirm-delete', id: $id, class: $class);
    }
}
