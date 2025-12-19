<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicYear\StoreAcademicYearRequest;
use App\Http\Requests\AcademicYear\UpdateAcademicYearRequest;
use App\Services\AcademicYearService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AcademicYearController extends Controller
{
    public function __construct(
        protected AcademicYearService $academicYearService
    ) {}

    /**
     * Display academic years list page
     */
    public function index(): View
    {
        $stats = $this->academicYearService->getStats();

        return view('content.pages.admin.academic-years', compact('stats'));
    }

    /**
     * DataTables server-side data
     */
    public function list(Request $request): JsonResponse
    {
        $query = $this->academicYearService->getDataTableQuery();

        return DataTables::eloquent($query)
            ->addColumn('status', function ($academicYear) {
                return $academicYear->is_active;
            })
            ->addColumn('period', function ($academicYear) {
                return $academicYear->start_date->format('d M Y') . ' - ' . $academicYear->end_date->format('d M Y');
            })
            ->addColumn('actions', function ($academicYear) {
                return $academicYear->id;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Store a new academic year
     */
    public function store(StoreAcademicYearRequest $request): JsonResponse
    {
        try {
            $academicYear = $this->academicYearService->createAcademicYear([
                'name' => $request->validated('name'),
                'start_date' => $request->validated('start_date'),
                'end_date' => $request->validated('end_date'),
                'is_active' => $request->validated('is_active', false),
                'description' => $request->validated('description'),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Academic Year '{$academicYear->name}' created successfully.",
                'data' => $academicYear,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get academic year data for editing
     */
    public function show(int $academic_year): JsonResponse
    {
        $academicYearData = $this->academicYearService->getAcademicYearById($academic_year);

        if ($academicYearData === null) {
            return response()->json([
                'success' => false,
                'message' => 'Academic Year not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $academicYearData->id,
                'name' => $academicYearData->name,
                'start_date' => $academicYearData->start_date->format('Y-m-d'),
                'end_date' => $academicYearData->end_date->format('Y-m-d'),
                'is_active' => $academicYearData->is_active,
                'description' => $academicYearData->description,
            ],
        ]);
    }

    /**
     * Update an existing academic year
     */
    public function update(UpdateAcademicYearRequest $request, int $academic_year): JsonResponse
    {
        try {
            $updatedAcademicYear = $this->academicYearService->updateAcademicYear(
                $academic_year,
                [
                    'name' => $request->validated('name'),
                    'start_date' => $request->validated('start_date'),
                    'end_date' => $request->validated('end_date'),
                    'is_active' => $request->validated('is_active', false),
                    'description' => $request->validated('description'),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Academic Year '{$updatedAcademicYear->name}' updated successfully.",
                'data' => $updatedAcademicYear,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete an academic year
     */
    public function destroy(int $academic_year): JsonResponse
    {
        try {
            $this->academicYearService->deleteAcademicYear($academic_year);

            return response()->json([
                'success' => true,
                'message' => 'Academic Year deleted successfully.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Set an academic year as active
     */
    public function setActive(int $academic_year): JsonResponse
    {
        try {
            $academicYear = $this->academicYearService->setActiveYear($academic_year);

            return response()->json([
                'success' => true,
                'message' => "Academic Year '{$academicYear->name}' is now active.",
                'data' => $academicYear,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
