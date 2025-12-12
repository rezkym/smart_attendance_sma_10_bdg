<?php

declare(strict_types=1);

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classroom\StoreClassroomRequest;
use App\Http\Requests\Classroom\UpdateClassroomRequest;
use App\Services\ClassroomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClassroomController extends Controller
{
    public function __construct(
        protected ClassroomService $classroomService,
        protected \App\Services\AcademicYearService $academicYearService
    ) {}

    /**
     * Display classrooms list page
     */
    public function index()
    {
        $stats = $this->classroomService->getStats();
        $academicYears = $this->academicYearService->getAllAcademicYears();

        return view('content.pages.admin.classrooms', compact('stats', 'academicYears'));
    }

    /**
     * DataTables server-side data
     */
    public function list(Request $request): JsonResponse
    {
        $query = $this->classroomService->getDataTableQuery();

        return DataTables::eloquent($query)
            ->addColumn('academic_year_name', function ($classroom) {
                return $classroom->academicYear->name ?? '-';
            })
            ->addColumn('homeroom_teacher_name', function ($classroom) {
                return $classroom->homeroomTeacher?->user?->name ?? '-';
            })
            ->addColumn('status', function ($classroom) {
                return $classroom->is_active;
            })
            ->addColumn('actions', function ($classroom) {
                return $classroom->id;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Get classrooms by academic year (for dropdowns)
     */
    public function getByAcademicYear(int $academicYear): JsonResponse
    {
        $classrooms = $this->classroomService->getClassroomsByAcademicYear($academicYear);

        return response()->json([
            'success' => true,
            'data' => $classrooms->map(function ($classroom) {
                return [
                    'id' => $classroom->id,
                    'name' => $classroom->name,
                    'grade_level' => $classroom->grade_level,
                    'capacity' => $classroom->capacity,
                ];
            }),
        ]);
    }

    /**
     * Store a new classroom
     */
    public function store(StoreClassroomRequest $request): JsonResponse
    {
        try {
            $classroom = $this->classroomService->createClassroom($request->validated());

            return response()->json([
                'success' => true,
                'message' => "Classroom '{$classroom->name}' created successfully.",
                'data' => $classroom,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get classroom data for editing
     */
    public function show(int $classroom): JsonResponse
    {
        $classroomData = $this->classroomService->getClassroomById($classroom);

        if ($classroomData === null) {
            return response()->json([
                'success' => false,
                'message' => 'Classroom not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $classroomData->id,
                'name' => $classroomData->name,
                'grade_level' => $classroomData->grade_level,
                'academic_year_id' => $classroomData->academic_year_id,
                'academic_year_name' => $classroomData->academicYear?->name,
                'homeroom_teacher_id' => $classroomData->homeroom_teacher_id,
                'homeroom_teacher_name' => $classroomData->homeroomTeacher?->user?->name,
                'capacity' => $classroomData->capacity,
                'description' => $classroomData->description,
                'is_active' => $classroomData->is_active,
            ],
        ]);
    }

    /**
     * Update an existing classroom
     */
    public function update(UpdateClassroomRequest $request, int $classroom): JsonResponse
    {
        try {
            $updatedClassroom = $this->classroomService->updateClassroom($classroom, $request->validated());

            return response()->json([
                'success' => true,
                'message' => "Classroom '{$updatedClassroom->name}' updated successfully.",
                'data' => $updatedClassroom,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a classroom
     */
    public function destroy(int $classroom): JsonResponse
    {
        try {
            $this->classroomService->deleteClassroom($classroom);

            return response()->json([
                'success' => true,
                'message' => 'Classroom deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get available homeroom teachers for dropdown
     */
    public function availableHomeroomTeachers(): JsonResponse
    {
        $teachers = $this->classroomService->getAvailableHomeroomTeachers();

        return response()->json([
            'success' => true,
            'data' => $teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name,
                    'nip' => $teacher->nip,
                ];
            }),
        ]);
    }

    /**
     * Assign homeroom teacher to classroom
     */
    public function assignHomeroom(Request $request, int $classroom): JsonResponse
    {
        $request->validate([
            'homeroom_teacher_id' => 'nullable|integer|exists:teachers,id',
        ]);

        try {
            $updatedClassroom = $this->classroomService->assignHomeroomTeacher(
                $classroom,
                $request->input('homeroom_teacher_id')
            );

            return response()->json([
                'success' => true,
                'message' => 'Homeroom teacher assigned successfully.',
                'data' => [
                    'id' => $updatedClassroom->id,
                    'name' => $updatedClassroom->name,
                    'homeroom_teacher_id' => $updatedClassroom->homeroom_teacher_id,
                    'homeroom_teacher_name' => $updatedClassroom->homeroomTeacher?->user?->name,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
