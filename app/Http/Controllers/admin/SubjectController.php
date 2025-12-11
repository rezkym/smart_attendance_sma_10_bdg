<?php

declare(strict_types=1);

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Services\SubjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SubjectController extends Controller
{
    public function __construct(
        protected SubjectService $subjectService
    ) {}

    /**
     * Display subjects list page
     */
    public function index(): View
    {
        $stats = $this->subjectService->getStats();

        return view('content.pages.admin.subjects', compact('stats'));
    }

    /**
     * DataTables server-side data
     */
    public function list(Request $request): JsonResponse
    {
        $query = $this->subjectService->getDataTableQuery();

        return DataTables::eloquent($query)
            ->addColumn('status', function ($subject) {
                return $subject->is_active;
            })
            ->addColumn('actions', function ($subject) {
                return $subject->id;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Store a new subject
     */
    public function store(StoreSubjectRequest $request): JsonResponse
    {
        try {
            $subject = $this->subjectService->createSubject([
                'code' => $request->validated('code'),
                'name' => $request->validated('name'),
                'description' => $request->validated('description'),
                'is_active' => $request->validated('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Subject '{$subject->name}' created successfully.",
                'data' => $subject,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get subject data for editing
     */
    public function show(int $subject): JsonResponse
    {
        $subjectData = $this->subjectService->getSubjectById($subject);

        if ($subjectData === null) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $subjectData->id,
                'code' => $subjectData->code,
                'name' => $subjectData->name,
                'description' => $subjectData->description,
                'is_active' => $subjectData->is_active,
            ],
        ]);
    }

    /**
     * Update an existing subject
     */
    public function update(UpdateSubjectRequest $request, int $subject): JsonResponse
    {
        try {
            $updatedSubject = $this->subjectService->updateSubject(
                $subject,
                [
                    'code' => $request->validated('code'),
                    'name' => $request->validated('name'),
                    'description' => $request->validated('description'),
                    'is_active' => $request->validated('is_active', true),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Subject '{$updatedSubject->name}' updated successfully.",
                'data' => $updatedSubject,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a subject
     */
    public function destroy(int $subject): JsonResponse
    {
        try {
            $this->subjectService->deleteSubject($subject);

            return response()->json([
                'success' => true,
                'message' => 'Subject deleted successfully.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
