<?php

namespace App\Livewire\Components;

use App\Enumerables\FormStatus;
use App\Helpers\ComponentHelpers;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

abstract class FormComponent extends Component {
    use ComponentHelpers;

    #[Locked]
    public ?Model $resource = null;

    #[Locked]
    public bool $duplicating = false;

    #[On('edit-resource')]
    public function edit(int $id, string $class): void {
        $abstract = app()->make($class);
        $this->resource = $abstract::find($id);
        $this->hydrateFields($this->resource);
        $this->dispatch('edit-hydrated');
    }

    /**
     * Duplicate an existing resource based on ID and class.
     * The fields are hydrated from the source, but the resource itself is
     * deliberately not kept, so the form stays in creating mode and saves
     * the hydrated values as a new record.
     */
    #[On('duplicate-resource')]
    public function duplicate(int $id, string $class): void {
        $abstract = app()->make($class);
        $this->hydrateFields($abstract::find($id));
        $this->duplicating = true;
        $this->dispatch('edit-hydrated');
    }

    #[On('reset-modal')]
    public function clear(): void {
        $this->reset();
        $this->resetValidation();
    }

    /**
     * Determines the form status based on whether the resource exists or not.
     */
    public function formStatus(): FormStatus {
        return (isset($this->resource) && $this->resource->exists)
            ? FormStatus::EDITING
            : FormStatus::CREATING;
    }
}
