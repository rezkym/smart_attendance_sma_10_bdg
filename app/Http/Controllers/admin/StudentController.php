<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignStudentClassroomRequest;
use App\Http\Requests\Admin\AssignStudentRfidRequest;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function __construct(
        private readonly StudentService $studentService
    ) {
        $this->middleware('permission:students.view')->only(['index', 'show']);
        $this->middleware('permission:students.create')->only(['store']);
        $this->middleware('permission:students.update')->only(['update']);
        $this->middleware('permission:students.delete')->only(['destroy']);
        $this->middleware('permission:students.assign-rfid')->only(['assignRfid']);
        $this->middleware('permission:students.assign-classroom')->only(['assignClassroom']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->studentService->datatableQuery($request->only('classroom_id'));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('user_name', fn (Student $student) => $student->user->name ?? '-')
                ->addColumn('user_email', fn (Student $student) => $student->user->email ?? '-')
                ->editColumn('rfid_card_number', fn (Student $student) => $student->rfid_card_number ?? '-')
                ->addColumn('classroom_name', fn (Student $student) => $student->classroom->name ?? '-')
                ->addColumn('actions', fn (Student $student) => view('content.pages.admin.students.partials.actions', compact('student'))->render())
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('student_number', 'like', "%{$search}%")
                                ->orWhere('rfid_card_number', 'like', "%{$search}%")
                                ->orWhere('parent_name', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%"))
                                ->orWhereHas('classroom', fn ($classroomQuery) => $classroomQuery->where('name', 'like', "%{$search}%"));
                        });
                    }
                })
                ->rawColumns(['actions'])
                ->toJson();
        }

        $classrooms = Classroom::query()->orderBy('name')->get();

        return view('content.pages.admin.students.index', [
            'classrooms' => $classrooms,
        ]);
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $student = $this->studentService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully.',
            'data' => new StudentResource($student),
        ], 201);
    }

    public function show(Student $student): JsonResponse
    {
        $student->load(['user.roles', 'classroom']);

        return response()->json([
            'success' => true,
            'data' => new StudentResource($student),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $updated = $this->studentService->update($student, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully.',
            'data' => new StudentResource($updated),
        ]);
    }

    public function destroy(Student $student): JsonResponse
    {
        $this->studentService->delete($student);

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully.',
        ]);
    }

    public function assignRfid(AssignStudentRfidRequest $request, Student $student): JsonResponse
    {
        $updated = $this->studentService->assignRfid($student, $request->validated('rfid_card_number'));

        return response()->json([
            'success' => true,
            'message' => 'RFID updated successfully.',
            'data' => new StudentResource($updated),
        ]);
    }

    public function assignClassroom(AssignStudentClassroomRequest $request, Student $student): JsonResponse
    {
        $updated = $this->studentService->assignClassroom($student, $request->validated('classroom_id'));

        return response()->json([
            'success' => true,
            'message' => 'Classroom updated successfully.',
            'data' => new StudentResource($updated),
        ]);
    }

    public function getAvailableUsers(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        // Get users with 'student' role yang belum punya student record
        $users = User::role('student')
            ->whereDoesntHave('student')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->limit(10)
            ->get(['id', 'name', 'email']);

        // Format untuk Select2
        $results = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'text' => "{$user->name} ({$user->email})",
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => false],
        ]);
    }
}
