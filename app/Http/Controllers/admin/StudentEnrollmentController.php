<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentEnrollment\StoreStudentEnrollmentRequest;
use App\Http\Requests\StudentEnrollment\TransferStudentRequest;
use App\Http\Requests\StudentEnrollment\UpdateStudentEnrollmentRequest;
use App\Services\AcademicYearService;
use App\Services\ClassroomService;
use App\Services\StudentEnrollmentService;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StudentEnrollmentController extends Controller
{
    public function __construct(
        protected StudentEnrollmentService $enrollmentService,
        protected StudentService $studentService,
        protected ClassroomService $classroomService,
        protected AcademicYearService $academicYearService
    ) {}

    /**
     * Display enrollments list page.
     */
    public function index(): View
    {
        $stats = $this->enrollmentService->getStats();
        $academicYears = $this->academicYearService->getAllAcademicYears();
        $classrooms = $this->classroomService->getAllActiveClassrooms();
        $statuses = EnrollmentStatus::cases();

        return view('content.pages.admin.student-enrollments', compact('stats', 'academicYears', 'classrooms', 'statuses'));
    }

    /**
     * DataTables server-side data.
     */
    public function list(Request $request): JsonResponse
    {
        $query = $this->enrollmentService->getDataTableQuery();

        // Apply filters
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->input('academic_year_id'));
        }
        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->input('classroom_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return DataTables::eloquent($query)
            ->addColumn('student_name', function ($enrollment) {
                return $enrollment->student->user->display_name ?? '-';
            })
            ->addColumn('student_nisn', function ($enrollment) {
                return $enrollment->student->nisn ?? '-';
            })
            ->addColumn('student_id', function ($enrollment) {
                return $enrollment->student_id;
            })
            ->addColumn('classroom_name', function ($enrollment) {
                return $enrollment->classroom->name ?? '-';
            })
            ->addColumn('academic_year_name', function ($enrollment) {
                return $enrollment->academicYear->name ?? '-';
            })
            ->addColumn('status_label', function ($enrollment) {
                return $enrollment->status->label();
            })
            ->addColumn('status_key', function ($enrollment) {
                return strtolower($enrollment->status->name);
            })
            ->addColumn('enrolled_at', function ($enrollment) {
                return $enrollment->enrolled_at->format('d M Y');
            })
            ->addColumn('left_at', function ($enrollment) {
                return $enrollment->left_at?->format('d M Y') ?? '-';
            })
            ->addColumn('actions', function ($enrollment) {
                return $enrollment->id;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Get enrollment history by student.
     */
    public function byStudent(int $studentId): JsonResponse
    {
        $enrollments = $this->enrollmentService->getEnrollmentHistory($studentId);

        $formatted = $enrollments->map(function ($enrollment) {
            return [
                'id' => $enrollment->id,
                'classroom' => $enrollment->classroom->name,
                'academic_year' => $enrollment->academicYear->name,
                'enrolled_at' => $enrollment->enrolled_at->format('d M Y'),
                'left_at' => $enrollment->left_at?->format('d M Y'),
                'status' => $enrollment->status->label(),
                'status_badge' => $enrollment->status->badgeColor(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Get active enrollments by classroom.
     */
    public function byClassroom(int $classroomId): JsonResponse
    {
        $enrollments = $this->enrollmentService->getActiveEnrollmentsByClassroom($classroomId);

        $formatted = $enrollments->map(function ($enrollment) {
            return [
                'id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'student_name' => $enrollment->student->user->display_name ?? '-',
                'student_nisn' => $enrollment->student->nisn,
                'enrolled_at' => $enrollment->enrolled_at->format('d M Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Store a new enrollment.
     */
    public function store(StoreStudentEnrollmentRequest $request): JsonResponse
    {
        try {
            $enrollment = $this->enrollmentService->enrollStudent([
                'student_id' => $request->validated('student_id'),
                'classroom_id' => $request->validated('classroom_id'),
                'academic_year_id' => $request->validated('academic_year_id'),
                'enrolled_at' => $request->validated('enrolled_at'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil didaftarkan ke kelas.',
                'data' => $enrollment,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get enrollment data for editing.
     */
    public function show(int $enrollment): JsonResponse
    {
        $enrollmentData = $this->enrollmentService->getEnrollmentById($enrollment);

        if ($enrollmentData === null) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $enrollmentData->id,
                'student_id' => $enrollmentData->student_id,
                'student_name' => $enrollmentData->student->user->display_name ?? '-',
                'classroom_id' => $enrollmentData->classroom_id,
                'classroom_name' => $enrollmentData->classroom->name,
                'academic_year_id' => $enrollmentData->academic_year_id,
                'academic_year_name' => $enrollmentData->academicYear->name,
                'enrolled_at' => $enrollmentData->enrolled_at->format('Y-m-d'),
                'left_at' => $enrollmentData->left_at?->format('Y-m-d'),
                'status' => $enrollmentData->status->value,
                'status_label' => $enrollmentData->status->label(),
            ],
        ]);
    }

    /**
     * Update an existing enrollment.
     */
    public function update(UpdateStudentEnrollmentRequest $request, int $enrollment): JsonResponse
    {
        try {
            $updatedEnrollment = $this->enrollmentService->updateEnrollment(
                $enrollment,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Enrollment berhasil diperbarui.',
                'data' => $updatedEnrollment,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete an enrollment.
     */
    public function destroy(int $enrollment): JsonResponse
    {
        try {
            $this->enrollmentService->deleteEnrollment($enrollment);

            return response()->json([
                'success' => true,
                'message' => 'Enrollment berhasil dihapus.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Transfer student to new classroom.
     */
    public function transfer(TransferStudentRequest $request): JsonResponse
    {
        try {
            $enrollment = $this->enrollmentService->transferStudent(
                $request->validated('student_id'),
                $request->validated('classroom_id'),
                $request->validated('academic_year_id'),
                $request->validated('transfer_date')
            );

            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil dipindahkan ke kelas baru.',
                'data' => $enrollment,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Graduate a student.
     */
    public function graduate(Request $request): JsonResponse
    {
        $request->validate([
            'enrollment_id' => 'required|integer|exists:student_enrollments,id',
            'graduation_date' => 'required|date',
        ]);

        try {
            // Get student_id from enrollment
            $enrollmentData = $this->enrollmentService->getEnrollmentById($request->input('enrollment_id'));
            if (!$enrollmentData) {
                throw new \InvalidArgumentException('Enrollment tidak ditemukan.');
            }

            $enrollment = $this->enrollmentService->graduateStudent(
                $enrollmentData->student_id,
                $request->input('graduation_date')
            );

            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil diluluskan.',
                'data' => $enrollment,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Drop a student.
     */
    public function drop(Request $request): JsonResponse
    {
        $request->validate([
            'enrollment_id' => 'required|integer|exists:student_enrollments,id',
            'drop_date' => 'required|date',
        ]);

        try {
            // Get student_id from enrollment
            $enrollmentData = $this->enrollmentService->getEnrollmentById($request->input('enrollment_id'));
            if (!$enrollmentData) {
                throw new \InvalidArgumentException('Enrollment tidak ditemukan.');
            }

            $enrollment = $this->enrollmentService->dropStudent(
                $enrollmentData->student_id,
                $request->input('drop_date')
            );

            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil dikeluarkan.',
                'data' => $enrollment,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get available students for enrollment (not enrolled in current academic year).
     */
    public function availableStudents(Request $request): JsonResponse
    {
        $students = $this->studentService->getAllActiveStudents();

        $formatted = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->user->display_name ?? '-',
                'nisn' => $student->nisn,
                'nis' => $student->nis,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Get available classrooms.
     */
    public function availableClassrooms(): JsonResponse
    {
        $classrooms = $this->classroomService->getAllActiveClassrooms();

        $formatted = $classrooms->map(function ($classroom) {
            return [
                'id' => $classroom->id,
                'name' => $classroom->name,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Get enrollment statuses.
     */
    public function statuses(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => EnrollmentStatus::toArray(),
        ]);
    }
}
