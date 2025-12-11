<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AcademicYear;
use App\Repositories\Contracts\AcademicYearRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
    public function __construct(
        protected AcademicYearRepositoryInterface $academicYearRepository
    ) {}

    /**
     * Get all academic years
     *
     * @return Collection<int, AcademicYear>
     */
    public function getAllAcademicYears(): Collection
    {
        return $this->academicYearRepository->getAll();
    }

    /**
     * Get academic year by ID
     */
    public function getAcademicYearById(int $academicYearId): ?AcademicYear
    {
        return $this->academicYearRepository->findById($academicYearId);
    }

    /**
     * Get active academic year
     */
    public function getActiveYear(): ?AcademicYear
    {
        return $this->academicYearRepository->getActive();
    }

    /**
     * Create a new academic year
     *
     * @param array{name: string, start_date: string, end_date: string, is_active?: bool, description?: string|null} $data
     */
    public function createAcademicYear(array $data): AcademicYear
    {
        return DB::transaction(function () use ($data) {
            // If this year should be active, deactivate all others first
            if (($data['is_active'] ?? false) === true) {
                $this->academicYearRepository->deactivateAll();
            }

            return $this->academicYearRepository->create($data);
        });
    }

    /**
     * Update academic year
     *
     * @param array{name?: string, start_date?: string, end_date?: string, is_active?: bool, description?: string|null} $data
     *
     * @throws \InvalidArgumentException
     */
    public function updateAcademicYear(int $academicYearId, array $data): AcademicYear
    {
        $academicYear = $this->academicYearRepository->findById($academicYearId);

        if ($academicYear === null) {
            throw new \InvalidArgumentException("Academic Year with ID {$academicYearId} not found.");
        }

        return DB::transaction(function () use ($academicYear, $data) {
            // If setting this year as active, deactivate all others first
            if (($data['is_active'] ?? false) === true && !$academicYear->is_active) {
                $this->academicYearRepository->deactivateAll();
            }

            return $this->academicYearRepository->update($academicYear, $data);
        });
    }

    /**
     * Delete academic year
     *
     * @throws \InvalidArgumentException
     */
    public function deleteAcademicYear(int $academicYearId): bool
    {
        $academicYear = $this->academicYearRepository->findById($academicYearId);

        if ($academicYear === null) {
            throw new \InvalidArgumentException("Academic Year with ID {$academicYearId} not found.");
        }

        // Prevent deleting active academic year
        if ($academicYear->is_active) {
            throw new \InvalidArgumentException('Cannot delete the active academic year. Please set another year as active first.');
        }

        return $this->academicYearRepository->delete($academicYear);
    }

    /**
     * Set an academic year as active
     *
     * @throws \InvalidArgumentException
     */
    public function setActiveYear(int $academicYearId): AcademicYear
    {
        $academicYear = $this->academicYearRepository->findById($academicYearId);

        if ($academicYear === null) {
            throw new \InvalidArgumentException("Academic Year with ID {$academicYearId} not found.");
        }

        return DB::transaction(function () use ($academicYear) {
            // Deactivate all other academic years
            $this->academicYearRepository->deactivateAll();

            // Set this one as active
            return $this->academicYearRepository->update($academicYear, ['is_active' => true]);
        });
    }

    /**
     * Get statistics for dashboard cards
     *
     * @return array{total: int, active_year: string|null}
     */
    public function getStats(): array
    {
        $activeYear = $this->academicYearRepository->getActive();

        return [
            'total' => $this->academicYearRepository->getTotalCount(),
            'active_year' => $activeYear?->name,
        ];
    }

    /**
     * Get DataTables query builder
     */
    public function getDataTableQuery(): Builder
    {
        return $this->academicYearRepository->getDataTableQuery();
    }
}
