<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\SemesterType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Semester\StoreSemesterRequest;
use App\Http\Requests\Semester\UpdateSemesterRequest;
use App\Services\AcademicYearService;
use App\Services\SemesterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SemesterController extends Controller
{
    public function __construct(
        protected SemesterService $semesterService,
        protected AcademicYearService $academicYearService
    ) {}

    /**
     * Display semesters list page.
     */
    public function index(): View
    {
        $stats = $this->semesterService->getStats();
        $academicYears = $this->academicYearService->getAllAcademicYears();
        $semesterTypes = SemesterType::toArray();

        return view('content.pages.admin.semesters', compact('stats', 'academicYears', 'semesterTypes'));
    }

    /**
     * DataTables server-side data.
     */
    public function list(Request $request): JsonResponse
    {
        $query = $this->semesterService->getDataTableQuery();

        return DataTables::eloquent($query)
            ->addColumn('academic_year_name', function ($semester) {
                return $semester->academicYear->name;
            })
            ->addColumn('type_label', function ($semester) {
                return $semester->type->label();
            })
            ->addColumn('period', function ($semester) {
                return $semester->start_date->format('d M Y') . ' - ' . $semester->end_date->format('d M Y');
            })
            ->addColumn('status', function ($semester) {
                return $semester->is_active;
            })
            ->addColumn('actions', function ($semester) {
                return $semester->id;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Get semesters by academic year for dropdowns.
     */
    public function byAcademicYear(int $academicYearId): JsonResponse
    {
        $semesters = $this->semesterService->getSemestersByAcademicYear($academicYearId);

        $formatted = $semesters->map(function ($semester) {
            return [
                'id' => $semester->id,
                'label' => $semester->type->label(),
                'is_active' => $semester->is_active,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Store a new semester.
     */
    public function store(StoreSemesterRequest $request): JsonResponse
    {
        try {
            $semester = $this->semesterService->createSemester([
                'academic_year_id' => $request->validated('academic_year_id'),
                'type' => $request->validated('type'),
                'start_date' => $request->validated('start_date'),
                'end_date' => $request->validated('end_date'),
                'is_active' => $request->validated('is_active', false),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Semester {$semester->type->label()} berhasil dibuat.",
                'data' => $semester,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get semester data for editing.
     */
    public function show(int $semester): JsonResponse
    {
        $semesterData = $this->semesterService->getSemesterById($semester);

        if ($semesterData === null) {
            return response()->json([
                'success' => false,
                'message' => 'Semester tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $semesterData->id,
                'academic_year_id' => $semesterData->academic_year_id,
                'type' => $semesterData->type->value,
                'start_date' => $semesterData->start_date->format('Y-m-d'),
                'end_date' => $semesterData->end_date->format('Y-m-d'),
                'is_active' => $semesterData->is_active,
            ],
        ]);
    }

    /**
     * Update an existing semester.
     */
    public function update(UpdateSemesterRequest $request, int $semester): JsonResponse
    {
        try {
            $updatedSemester = $this->semesterService->updateSemester(
                $semester,
                [
                    'academic_year_id' => $request->validated('academic_year_id'),
                    'type' => $request->validated('type'),
                    'start_date' => $request->validated('start_date'),
                    'end_date' => $request->validated('end_date'),
                    'is_active' => $request->validated('is_active', false),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Semester {$updatedSemester->type->label()} berhasil diperbarui.",
                'data' => $updatedSemester,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a semester.
     */
    public function destroy(int $semester): JsonResponse
    {
        try {
            $this->semesterService->deleteSemester($semester);

            return response()->json([
                'success' => true,
                'message' => 'Semester berhasil dihapus.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Set a semester as active.
     */
    public function setActive(int $semester): JsonResponse
    {
        try {
            $activeSemester = $this->semesterService->setActiveSemester($semester);

            return response()->json([
                'success' => true,
                'message' => "Semester {$activeSemester->type->label()} sekarang aktif.",
                'data' => $activeSemester,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}

