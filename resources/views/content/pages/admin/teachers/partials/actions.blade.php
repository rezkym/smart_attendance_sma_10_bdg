<div class="d-flex gap-2">
    @can('teachers.update')
        <button type="button" class="btn btn-sm btn-icon btn-text-secondary edit-teacher" data-id="{{ $teacher->id }}"
            title="Edit">
            <i class="icon-base ri ri-edit-2-line"></i>
        </button>
    @endcan

    @can('teachers.assign-subjects')
        <button type="button" class="btn btn-sm btn-icon btn-text-secondary assign-subjects-teacher" data-id="{{ $teacher->id }}"
            title="Kelola Mata Pelajaran">
            <i class="icon-base ri ri-book-2-line"></i>
        </button>
    @endcan

    @can('teachers.assign-classroom')
        <button type="button" class="btn btn-sm btn-icon btn-text-secondary assign-homeroom-teacher" data-id="{{ $teacher->id }}"
            title="Atur Wali Kelas">
            <i class="icon-base ri ri-home-smile-2-line"></i>
        </button>
    @endcan

    @can('teachers.delete')
        <button type="button" class="btn btn-sm btn-icon btn-text-secondary delete-teacher" data-id="{{ $teacher->id }}"
            title="Delete">
            <i class="icon-base ri ri-delete-bin-7-line"></i>
        </button>
    @endcan
</div>
