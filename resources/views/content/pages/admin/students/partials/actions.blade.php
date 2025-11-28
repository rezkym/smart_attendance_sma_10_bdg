<div class="d-flex gap-2">
    @can('students.update')
        <button type="button" class="btn btn-sm btn-icon btn-text-secondary edit-student" data-id="{{ $student->id }}"
            title="Edit">
            <i class="icon-base ri ri-edit-2-line"></i>
        </button>
    @endcan

    @can('students.delete')
        <button type="button" class="btn btn-sm btn-icon btn-text-secondary delete-student" data-id="{{ $student->id }}"
            title="Delete">
            <i class="icon-base ri ri-delete-bin-7-line"></i>
        </button>
    @endcan
</div>
