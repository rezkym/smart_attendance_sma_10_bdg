<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SemesterType;
use App\Models\Semester;
use App\Repositories\Contracts\SemesterRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SemesterService
{
    public function __construct(
        protected SemesterRepositoryInterface $semesterRepository
    ) {}

    /**
     * Get all semesters.
     *
     * @return Collection<int, Semester>
     */
    public function getAllSemesters(): Collection
    {
        return $this->semesterRepository->getAll();
    }

    /**
     * Get semester by ID.
     */
    public function getSemesterById(int $semesterId): ?Semester
    {
        return $this->semesterRepository->findById($semesterId);
    }

    /**
     * Get currently active semester.
     */
    public function getActiveSemester(): ?Semester
    {
        return $this->semesterRepository->getActive();
    }

    /**
     * Get semesters by academic year.
     *
     * @return Collection<int, Semester>
     */
    public function getSemestersByAcademicYear(int $academicYearId): Collection
    {
        return $this->semesterRepository->getByAcademicYear($academicYearId);
    }

    /**
     * Create a new semester.
     *
     * @param array{academic_year_id: int, type: int, start_date: string, end_date: string, is_active?: bool} $data
     *
     * @throws \InvalidArgumentException
     */
    public function createSemester(array $data): Semester
    {
        $semesterType = SemesterType::from($data['type']);

        // Check if semester type already exists for this academic year
        if ($this->semesterRepository->existsByAcademicYearAndType($data['academic_year_id'], $semesterType)) {
            throw new \InvalidArgumentException(
                "Semester {$semesterType->label()} sudah ada untuk tahun ajaran ini."
            );
        }

        return DB::transaction(function () use ($data) {
            // If this semester should be active, deactivate all others first
            if (($data['is_active'] ?? false) === true) {
                $this->semesterRepository->deactivateAll();
            }

            return $this->semesterRepository->create($data);
        });
    }

    /**
     * Update semester.
     *
     * @param array{academic_year_id?: int, type?: int, start_date?: string, end_date?: string, is_active?: bool} $data
     *
     * @throws \InvalidArgumentException
     */
    public function updateSemester(int $semesterId, array $data): Semester
    {
        $semester = $this->semesterRepository->findById($semesterId);

        if ($semester === null) {
            throw new \InvalidArgumentException("Semester dengan ID {$semesterId} tidak ditemukan.");
        }

        // Check if changing type would cause duplicate
        if (isset($data['type'])) {
            $semesterType = SemesterType::from($data['type']);
            $academicYearId = $data['academic_year_id'] ?? $semester->academic_year_id;

            if ($this->semesterRepository->existsByAcademicYearAndType($academicYearId, $semesterType, $semesterId)) {
                throw new \InvalidArgumentException(
                    "Semester {$semesterType->label()} sudah ada untuk tahun ajaran ini."
                );
            }
        }

        return DB::transaction(function () use ($semester, $data) {
            // If setting this semester as active, deactivate all others first
            if (($data['is_active'] ?? false) === true && !$semester->is_active) {
                $this->semesterRepository->deactivateAll();
            }

            return $this->semesterRepository->update($semester, $data);
        });
    }

    /**
     * Delete semester.
     *
     * @throws \InvalidArgumentException
     */
    public function deleteSemester(int $semesterId): bool
    {
        $semester = $this->semesterRepository->findById($semesterId);

        if ($semester === null) {
            throw new \InvalidArgumentException("Semester dengan ID {$semesterId} tidak ditemukan.");
        }

        // Prevent deleting active semester
        if ($semester->is_active) {
            throw new \InvalidArgumentException('Tidak dapat menghapus semester yang sedang aktif. Silakan aktifkan semester lain terlebih dahulu.');
        }

        // Check if semester has schedules
        if ($semester->schedules()->exists()) {
            throw new \InvalidArgumentException('Tidak dapat menghapus semester yang memiliki jadwal. Hapus jadwal terlebih dahulu.');
        }

        return $this->semesterRepository->delete($semester);
    }

    /**
     * Set a semester as active.
     *
     * @throws \InvalidArgumentException
     */
    public function setActiveSemester(int $semesterId): Semester
    {
        $semester = $this->semesterRepository->findById($semesterId);

        if ($semester === null) {
            throw new \InvalidArgumentException("Semester dengan ID {$semesterId} tidak ditemukan.");
        }

        return DB::transaction(function () use ($semester) {
            // Deactivate all other semesters
            $this->semesterRepository->deactivateAll();

            // Set this one as active
            return $this->semesterRepository->update($semester, ['is_active' => true]);
        });
    }

    /**
     * Get statistics for dashboard cards.
     *
     * @return array{total: int, active_semester: string|null}
     */
    public function getStats(): array
    {
        $activeSemester = $this->semesterRepository->getActive();
        $activeName = null;

        if ($activeSemester !== null) {
            $activeName = $activeSemester->academicYear->name . ' - ' . $activeSemester->type->label();
        }

        return [
            'total' => $this->semesterRepository->getTotalCount(),
            'active_semester' => $activeName,
        ];
    }

    /**
     * Get DataTables query builder.
     */
    public function getDataTableQuery(): Builder
    {
        return $this->semesterRepository->getDataTableQuery();
    }
}
