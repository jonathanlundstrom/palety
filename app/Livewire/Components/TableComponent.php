<?php

namespace App\Livewire\Components;

use Livewire\Attributes\Url;
use Livewire\Volt\Component;
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
    public string $sortBy = 'created_at';

    /**
     * Sort direction on a specific column.
     */
    #[Url(as: 'sortDirection')]
    public ?string $sortDirection = 'desc';

    /**
     * Sorting function
     */
    public function sort(string $key): void {
        if ($this->sortBy === $key) {
            if ($this->sortDirection === 'asc') {
                $this->sortDirection = 'desc';
            } elseif ($this->sortDirection === 'desc' && $key !== 'created_at') {
                $this->sortBy = 'created_at';
                $this->sortDirection = 'desc';
            } else {
                $this->sortDirection = 'asc';
            }
        } else {
            $this->sortBy = $key;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }
}
