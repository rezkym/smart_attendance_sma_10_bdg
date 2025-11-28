<div class="d-flex gap-2">
    @can('users.update')
        <button type="button" class="btn btn-sm btn-icon btn-text-secondary edit-user" data-id="{{ $user->id }}"
            title="Edit">
            <i class="icon-base ri ri-edit-2-line"></i>
        </button>
    @endcan

    @can('users.delete')
        <button type="button" class="btn btn-sm btn-icon btn-text-secondary delete-user" data-id="{{ $user->id }}"
            title="Delete">
            <i class="icon-base ri ri-delete-bin-7-line"></i>
        </button>
    @endcan
</div>
