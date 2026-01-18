<?php

namespace App\Livewire\Components;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

abstract class TableComponent extends Component {
    use WithPagination;

    /**
     * Query string; searching primary column.
     */
    #[Url(except: '')]
    public string $q = '';

    /**
     * Sort by on a specific column.
     */
    #[Url(as: 'sortBy')]
    public string $sortBy = 'id';

    /**
     * Sort direction on a specific column.
     */
    #[Url(as: 'sortDirection')]
    public ?string $sortDirection = 'asc';

    /**
     * Sorting function
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
}
