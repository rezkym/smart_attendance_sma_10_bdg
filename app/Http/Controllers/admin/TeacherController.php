<?php

declare(strict_types=1);

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Services\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TeacherController extends Controller
{
    public function __construct(
        protected TeacherService $teacherService
    ) {}

    /**
     * Display teachers list page
     */
    public function index(): View
    {
        $stats = $this->teacherService->getStats();

        return view('content.pages.admin.teachers', compact('stats'));
    }

    /**
     * DataTables server-side data
     */
    public function list(Request $request): JsonResponse
    {
        $query = $this->teacherService->getDataTableQuery();

        return DataTables::eloquent($query)
            ->addColumn('user_name', function ($teacher) {
                return $teacher->user->name ?? '';
            })
            ->addColumn('user_email', function ($teacher) {
                return $teacher->user->email ?? '';
            })
            ->addColumn('status', function ($teacher) {
                return $teacher->is_active;
            })
            ->addColumn('actions', function ($teacher) {
                return $teacher->id;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Get available users with teacher role who don't have a teacher profile yet
     */
    public function availableUsers(): JsonResponse
    {
        $users = $this->teacherService->getAvailableUsers();

        return response()->json([
            'success' => true,
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            }),
        ]);
    }

    /**
     * Store a new teacher
     */
    public function store(StoreTeacherRequest $request): JsonResponse
    {
        try {
            $teacher = $this->teacherService->createTeacher([
                'user_id' => $request->validated('user_id'),
                'nip' => $request->validated('nip'),
                'phone' => $request->validated('phone'),
                'address' => $request->validated('address'),
                'is_active' => $request->validated('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Teacher '{$teacher->user->name}' created successfully.",
                'data' => [
                    'id' => $teacher->id,
                    'user_id' => $teacher->user_id,
                    'name' => $teacher->user->name,
                    'email' => $teacher->user->email,
                    'nip' => $teacher->nip,
                    'phone' => $teacher->phone,
                    'address' => $teacher->address,
                    'is_active' => $teacher->is_active,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get teacher data for editing
     */
    public function show(int $teacher): JsonResponse
    {
        $teacherData = $this->teacherService->getTeacherById($teacher);

        if ($teacherData === null) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $teacherData->id,
                'user_id' => $teacherData->user_id,
                'name' => $teacherData->user->name,
                'email' => $teacherData->user->email,
                'nip' => $teacherData->nip,
                'phone' => $teacherData->phone,
                'address' => $teacherData->address,
                'is_active' => $teacherData->is_active,
            ],
        ]);
    }

    /**
     * Update an existing teacher
     */
    public function update(UpdateTeacherRequest $request, int $teacher): JsonResponse
    {
        try {
            $updatedTeacher = $this->teacherService->updateTeacher(
                $teacher,
                [
                    'nip' => $request->validated('nip'),
                    'phone' => $request->validated('phone'),
                    'address' => $request->validated('address'),
                    'is_active' => $request->validated('is_active', true),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Teacher '{$updatedTeacher->user->name}' updated successfully.",
                'data' => [
                    'id' => $updatedTeacher->id,
                    'user_id' => $updatedTeacher->user_id,
                    'name' => $updatedTeacher->user->name,
                    'email' => $updatedTeacher->user->email,
                    'nip' => $updatedTeacher->nip,
                    'phone' => $updatedTeacher->phone,
                    'address' => $updatedTeacher->address,
                    'is_active' => $updatedTeacher->is_active,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a teacher
     */
    public function destroy(int $teacher): JsonResponse
    {
        try {
            $this->teacherService->deleteTeacher($teacher);

            return response()->json([
                'success' => true,
                'message' => 'Teacher deleted successfully.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
