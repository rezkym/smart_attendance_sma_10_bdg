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
                return $teacher->user?->display_name ?? '';
            })
            ->addColumn('user_email', function ($teacher) {
                return $teacher->user?->email ?? '';
            })
            ->addColumn('user_phone', function ($teacher) {
                return $teacher->user?->phone_number ?? '';
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
                    'name' => $user->display_name,
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
                'is_active' => $request->validated('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Teacher '{$teacher->display_name}' created successfully.",
                'data' => $this->formatTeacherResponse($teacher),
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
            'data' => $this->formatTeacherResponse($teacherData),
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
                    'is_active' => $request->validated('is_active', true),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Teacher '{$updatedTeacher->display_name}' updated successfully.",
                'data' => $this->formatTeacherResponse($updatedTeacher),
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

    /**
     * Format teacher data for API response including user profile.
     *
     * @return array<string, mixed>
     */
    private function formatTeacherResponse(\App\Models\Teacher $teacher): array
    {
        $user = $teacher->user;

        return [
            'id' => $teacher->id,
            'user_id' => $teacher->user_id,
            // Teacher specific fields
            'nip' => $teacher->nip,
            'is_active' => $teacher->is_active,
            // User account fields
            'name' => $user?->name,
            'email' => $user?->email,
            // User profile fields (from users table)
            'full_name' => $user?->full_name,
            'gender' => $user?->gender?->value,
            'gender_label' => $user?->gender?->label(),
            'birth_place' => $user?->birth_place,
            'birth_date' => $user?->birth_date?->format('Y-m-d'),
            'address' => $user?->address,
            'phone_number' => $user?->phone_number,
        ];
    }
}
