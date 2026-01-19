<?php namespace App\Livewire\Components;

use App\Helpers\ComponentHelpers;
use Livewire\Attributes\On;
use Livewire\Component;

abstract class FormComponent extends Component {
    use ComponentHelpers;

    #[On('reset-modal')]
    public function clear(): void {
        $this->reset();
        $this->resetValidation();
    }
}
