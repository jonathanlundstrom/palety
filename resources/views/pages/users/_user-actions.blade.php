<?php

use App\Enumerables\Permission;

// Users who may manage super admins can only be edited or deleted by someone who may as well.
$isProtected = $item->can(Permission::MANAGE_SUPER_ADMINS) && auth()->user()->cannot(Permission::MANAGE_SUPER_ADMINS);

?>
<x-item-actions>
    @can(Permission::EDIT_USER)
        <x-actions.edit-button :form="$this->modalName" :object="$item" :disabled="$isProtected"/>
    @endcan

    {{-- Users delete their own account from the settings page, which also logs them out. --}}
    @unless ($item->is(auth()->user()))
        @can(Permission::DELETE_USER)
            <x-actions.delete-button :object="$item" :disabled="$isProtected"/>
        @endcan
    @endunless
</x-item-actions>
