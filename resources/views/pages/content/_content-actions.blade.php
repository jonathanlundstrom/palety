<?php

use App\Enumerables\Permission;

?>
<x-item-actions>
    @can(Permission::EDIT_CONTENT)
        <x-actions.edit-button :form="$this->modalName" :object="$item"/>
    @endcan

    @if ($item->usage_count > 0)
        @can(Permission::MERGE_CONTENT)
            <x-actions.merge-button :object="$item"/>
        @endcan
    @else
        @can(Permission::DELETE_CONTENT)
            <x-actions.delete-button :object="$item"/>
        @endcan
    @endif
</x-item-actions>
