<?php

use App\Enumerables\Permission;

?>
<x-item-actions>
    @can(Permission::EDIT_USER)
        <x-actions.edit-button :form="$this->modalName" :object="$item"/>
    @endcan
</x-item-actions>
