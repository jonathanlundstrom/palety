<?php

namespace App\Livewire\Components;

use App\Enumerables\FormStatus;
use App\Helpers\ComponentHelpers;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

abstract class FormComponent extends Component {
    use ComponentHelpers;

    #[Locked]
    public ?Model $resource = null;

    #[Locked]
    public bool $duplicating = false;

    /**
     * The model class this form manages. Only resources of this class can be
     * loaded into the form, so a crafted request cannot edit another model.
     *
     * @var class-string<Model>
     */
    protected string $model;

    #[On('edit-resource')]
    public function edit(int $id, string $class): void {
        abort_unless($class === $this->model, 403);

        $this->resource = $class::findOrFail($id);
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
        abort_unless($class === $this->model, 403);

        $this->hydrateFields($class::findOrFail($id));
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

    /**
     * Determines the translation sub-key used to label the interface.
     * Matches the lowercased form status, with duplicating as a third state.
     */
    #[Computed]
    public function formMode(): string {
        return $this->duplicating
            ? 'duplicating'
            : mb_strtolower($this->formStatus()->name);
    }

    /**
     * Resolves the appropriate label for the form action based on the current form state.
     * Incorporates a distinct label when duplicating in creating mode.
     */
    #[Computed]
    public function formActionLabel(): string {
        return match ($this->formStatus()) {
            FormStatus::CREATING => $this->duplicating
                ? __('app.save_copy')
                : __('app.save'),
            FormStatus::EDITING => __('app.update')
        };
    }
}
