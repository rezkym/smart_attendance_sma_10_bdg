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
            ->addColumn('classroom_name', function ($student) {
                return $student->classroom?->name ?? '-';
            })
            ->addColumn('gender_label', function ($student) {
                return $student->gender->label();
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
     * Store a new student.
     */
    public function store(StoreStudentRequest $request): JsonResponse
    {
        try {
            $student = $this->studentService->createStudent([
                'nisn' => $request->validated('nisn'),
                'nis' => $request->validated('nis'),
                'full_name' => $request->validated('full_name'),
                'gender' => $request->validated('gender'),
                'birth_place' => $request->validated('birth_place'),
                'birth_date' => $request->validated('birth_date'),
                'address' => $request->validated('address'),
                'phone_number' => $request->validated('phone_number'),
                'rfid_card_number' => $request->validated('rfid_card_number'),
                'enrollment_date' => $request->validated('enrollment_date'),
                'classroom_id' => $request->validated('classroom_id'),
                'is_active' => $request->validated('is_active', true),
                'notes' => $request->validated('notes'),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Student '{$student->full_name}' created successfully.",
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
                    'full_name' => $request->validated('full_name'),
                    'gender' => $request->validated('gender'),
                    'birth_place' => $request->validated('birth_place'),
                    'birth_date' => $request->validated('birth_date'),
                    'address' => $request->validated('address'),
                    'phone_number' => $request->validated('phone_number'),
                    'rfid_card_number' => $request->validated('rfid_card_number'),
                    'enrollment_date' => $request->validated('enrollment_date'),
                    'classroom_id' => $request->validated('classroom_id'),
                    'is_active' => $request->validated('is_active', true),
                    'notes' => $request->validated('notes'),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Student '{$updatedStudent->full_name}' updated successfully.",
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
     * Format student data for JSON response.
     *
     * @return array<string, mixed>
     */
    private function formatStudentData(\App\Models\Student $student): array
    {
        return [
            'id' => $student->id,
            'user_id' => $student->user_id,
            'classroom_id' => $student->classroom_id,
            'classroom_name' => $student->classroom?->name,
            'nisn' => $student->nisn,
            'nis' => $student->nis,
            'full_name' => $student->full_name,
            'gender' => $student->gender->value,
            'gender_label' => $student->gender->label(),
            'birth_place' => $student->birth_place,
            'birth_date' => $student->birth_date?->format('Y-m-d'),
            'address' => $student->address,
            'phone_number' => $student->phone_number,
            'rfid_card_number' => $student->rfid_card_number,
            'photo' => $student->photo,
            'enrollment_date' => $student->enrollment_date?->format('Y-m-d'),
            'is_active' => $student->is_active,
            'notes' => $student->notes,
        ];
    }
}
