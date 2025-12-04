<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignTeacherClassroomRequest;
use App\Http\Requests\Admin\AssignTeacherSubjectsRequest;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TeacherController extends Controller
{
    public function __construct(
        private readonly TeacherService $teacherService
    ) {
        $this->middleware('permission:teachers.view')->only(['index', 'show']);
        $this->middleware('permission:teachers.create')->only(['store']);
        $this->middleware('permission:teachers.update')->only(['update']);
        $this->middleware('permission:teachers.delete')->only(['destroy']);
        $this->middleware('permission:teachers.assign-subjects')->only(['assignSubjects']);
        $this->middleware('permission:teachers.assign-classroom')->only(['assignClassroom']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->teacherService->datatableQuery(
                $request->only(['subject_id', 'classroom_id'])
            );

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('user_name', fn (Teacher $teacher) => $teacher->user->name ?? '-')
                ->addColumn('user_email', fn (Teacher $teacher) => $teacher->user->email ?? '-')
                ->addColumn('subjects', fn (Teacher $teacher) => $teacher->subjects->pluck('name')->implode(', ') ?: '-')
                ->addColumn('homeroom_classrooms', fn (Teacher $teacher) => $teacher->homeroomClassrooms->pluck('name')->implode(', ') ?: '-')
                ->addColumn('actions', fn (Teacher $teacher) => view('content.pages.admin.teachers.partials.actions', compact('teacher'))->render())
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('teacher_number', 'like', "%{$search}%")
                                ->orWhere('specialization', 'like', "%{$search}%")
                                ->orWhere('phone_number', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%"))
                                ->orWhereHas('subjects', fn ($subjectQuery) => $subjectQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('code', 'like', "%{$search}%"))
                                ->orWhereHas('homeroomClassrooms', fn ($classroomQuery) => $classroomQuery->where('name', 'like', "%{$search}%"));
                        });
                    }
                })
                ->rawColumns(['actions'])
                ->toJson();
        }

        $subjects = Subject::query()->orderBy('name')->get();
        $classrooms = Classroom::query()->orderBy('name')->get();

        return view('content.pages.admin.teachers.index', [
            'subjects' => $subjects,
            'classrooms' => $classrooms,
        ]);
    }

    public function store(StoreTeacherRequest $request): JsonResponse
    {
        $teacher = $this->teacherService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Teacher created successfully.',
            'data' => new TeacherResource($teacher),
        ], 201);
    }

    public function show(Teacher $teacher): JsonResponse
    {
        $teacher->load(['user.roles', 'subjects', 'homeroomClassrooms']);

        return response()->json([
            'success' => true,
            'data' => new TeacherResource($teacher),
        ]);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): JsonResponse
    {
        $updated = $this->teacherService->update($teacher, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Teacher updated successfully.',
            'data' => new TeacherResource($updated),
        ]);
    }

    public function destroy(Teacher $teacher): JsonResponse
    {
        $this->teacherService->delete($teacher);

        return response()->json([
            'success' => true,
            'message' => 'Teacher deleted successfully.',
        ]);
    }

    public function assignSubjects(AssignTeacherSubjectsRequest $request, Teacher $teacher): JsonResponse
    {
        $updated = $this->teacherService->syncSubjects($teacher, $request->validated('subject_ids'));

        return response()->json([
            'success' => true,
            'message' => 'Subjects updated successfully.',
            'data' => new TeacherResource($updated),
        ]);
    }

    public function assignClassroom(AssignTeacherClassroomRequest $request, Teacher $teacher): JsonResponse
    {
        $updated = $this->teacherService->assignHomeroom($request->validated('classroom_id'), $teacher);

        return response()->json([
            'success' => true,
            'message' => 'Homeroom assignment updated successfully.',
            'data' => new TeacherResource($updated),
        ]);
    }

    public function getAvailableUsers(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $users = User::role('teacher')
            ->whereDoesntHave('teacher')
            ->when($search, function ($query, $searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%");
                });
            })
            ->limit(10)
            ->get(['id', 'name', 'email']);

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
