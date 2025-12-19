<?php

declare(strict_types=1);

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService
    ) {}

    /**
     * Display students list page.
     */
    public function index(): View
    {
        $stats = $this->studentService->getStats();

        return view('content.pages.admin.students', compact('stats'));
    }

    /**
     * DataTables server-side data.
     */
    public function list(Request $request): JsonResponse
    {
        $query = $this->studentService->getDataTableQuery();

        return DataTables::eloquent($query)
            ->addColumn('user_name', function ($student) {
                return $student->user?->display_name ?? '';
            })
            ->addColumn('user_email', function ($student) {
                return $student->user?->email ?? '';
            })
            ->addColumn('user_phone', function ($student) {
                return $student->user?->phone_number ?? '';
            })
            ->addColumn('user_gender', function ($student) {
                return $student->user?->gender?->label() ?? '';
            })
            ->addColumn('classroom_name', function ($student) {
                // Phase G: Get classroom via enrollment
                return $student->currentClassroom()?->name ?? '-';
            })
            ->addColumn('status', function ($student) {
                return $student->is_active;
            })
            ->addColumn('actions', function ($student) {
                return $student->id;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Get available classrooms for dropdown.
     */
    public function availableClassrooms(): JsonResponse
    {
        $classrooms = $this->studentService->getAvailableClassrooms();

        return response()->json([
            'success' => true,
            'data' => $classrooms->map(function ($classroom) {
                return [
                    'id' => $classroom->id,
                    'name' => $classroom->name,
                    'grade_level' => $classroom->grade_level,
                ];
            }),
        ]);
    }

    /**
     * Get available users for dropdown (users with student role who don't have a student profile).
     */
    public function availableUsers(): JsonResponse
    {
        $users = $this->studentService->getAvailableUsers();

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
     * Store a new student.
     */
    public function store(StoreStudentRequest $request): JsonResponse
    {
        try {
            $student = $this->studentService->createStudent([
                'user_id' => $request->validated('user_id'),
                'nisn' => $request->validated('nisn'),
                'nis' => $request->validated('nis'),
                'rfid_card_number' => $request->validated('rfid_card_number'),
                'enrollment_date' => $request->validated('enrollment_date'),
                // classroom_id removed - Phase G: use enrollment
                'is_active' => $request->validated('is_active', true),
                'notes' => $request->validated('notes'),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Student '{$student->display_name}' created successfully.",
                'data' => $this->formatStudentData($student),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get student data for viewing/editing.
     */
    public function show(int $student): JsonResponse
    {
        $studentData = $this->studentService->getStudentById($student);

        if ($studentData === null) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatStudentData($studentData),
        ]);
    }

    /**
     * Update an existing student.
     */
    public function update(UpdateStudentRequest $request, int $student): JsonResponse
    {
        try {
            $updatedStudent = $this->studentService->updateStudent(
                $student,
                [
                    'nisn' => $request->validated('nisn'),
                    'nis' => $request->validated('nis'),
                    'rfid_card_number' => $request->validated('rfid_card_number'),
                    'enrollment_date' => $request->validated('enrollment_date'),
                    // classroom_id removed - Phase G: use enrollment
                    'is_active' => $request->validated('is_active', true),
                    'notes' => $request->validated('notes'),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Student '{$updatedStudent->display_name}' updated successfully.",
                'data' => $this->formatStudentData($updatedStudent),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a student.
     */
    public function destroy(int $student): JsonResponse
    {
        try {
            $this->studentService->deleteStudent($student);

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Format student data for JSON response including user profile.
     * Phase G: Uses enrollment-based classroom lookup.
     *
     * @return array<string, mixed>
     */
    private function formatStudentData(\App\Models\Student $student): array
    {
        $user = $student->user;
        $currentClassroom = $student->currentClassroom();
        $currentEnrollment = $student->currentEnrollment();

        return [
            'id' => $student->id,
            'user_id' => $student->user_id,
            // Phase G: classroom via enrollment
            'classroom_id' => $currentEnrollment?->classroom_id,
            'classroom_name' => $currentClassroom?->name,
            // Student specific fields
            'nisn' => $student->nisn,
            'nis' => $student->nis,
            'rfid_card_number' => $student->rfid_card_number,
            'enrollment_date' => $student->enrollment_date?->format('Y-m-d'),
            'is_active' => $student->is_active,
            'notes' => $student->notes,
            'photo' => $student->photo,
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

