<?php namespace App\Livewire\Components;

use App\Enumerables\FormStatus;
use App\Helpers\ComponentHelpers;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

abstract class FormComponent extends Component {
    use ComponentHelpers;

    #[Locked]
    public Model $resource;

    #[On('edit-resource')]
    public function edit(int $id, string $class): void {
        $abstract = app()->make($class);
        $this->resource = $abstract::find($id);
        $this->hydrateFields($this->resource);
    }

    #[On('reset-modal')]
    public function clear(): void {
        $this->reset();
        $this->resetValidation();
    }

    /**
     * Determines the form status based on whether the resource exists or not.
     * @return FormStatus
     */
    public function formStatus(): FormStatus {
        return (isset($this->resource) && $this->resource->exists)
            ? FormStatus::EDITING
            : FormStatus::CREATING;
    }

    /**
     * Dispatch the event which initializes the QR code scanner.
     * @return void
     */
    public function scan(): void {
        $this->dispatch('scan');
    }
}
