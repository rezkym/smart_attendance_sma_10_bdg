<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActivityLogFilterRequest;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * Display activity logs page.
     */
    public function index(): View
    {
        return view('content.pages.admin.activity-logs');
    }

    /**
     * Get activity logs for DataTables.
     */
    public function list(ActivityLogFilterRequest $request): JsonResponse
    {
        $query = $this->activityLogService->getDataTableQuery($request->getFilters());

        return DataTables::eloquent($query)
            ->addColumn('causer_name', function (Activity $activity): string {
                return $activity->causer?->name ?? 'System';
            })
            ->addColumn('subject_name', function (Activity $activity): string {
                $subjectType = $this->activityLogService->getSubjectTypeName($activity->subject_type ?? '');
                $subjectId = $activity->subject_id ?? '-';
                return "{$subjectType} #{$subjectId}";
            })
            ->addColumn('changes_summary', function (Activity $activity): string {
                $properties = $this->activityLogService->formatActivityProperties($activity);
                $changedFields = array_keys($properties['attributes']);
                
                if (empty($changedFields)) {
                    return '-';
                }
                
                return implode(', ', array_slice($changedFields, 0, 3)) . 
                    (count($changedFields) > 3 ? '...' : '');
            })
            ->addColumn('formatted_time', function (Activity $activity): string {
                return $activity->created_at?->format('d M Y H:i:s') ?? '-';
            })
            ->addColumn('action', function (Activity $activity): string {
                return '<a href="' . route('admin.activity-logs.show', $activity) . '" class="btn btn-sm btn-icon btn-secondary" title="View Details"><i class="ri-eye-line"></i></a>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     * Display a specific activity log (JSON for AJAX modal).
     */
    public function show(Activity $activityLog): JsonResponse
    {
        $activityLog->load('causer');
        $properties = $this->activityLogService->formatActivityProperties($activityLog);
        $subjectTypeName = $this->activityLogService->getSubjectTypeName($activityLog->subject_type ?? '');

        return response()->json([
            'success' => true,
            'data' => [
                'activity' => $activityLog,
                'properties' => $properties,
                'subjectTypeName' => $subjectTypeName,
            ],
        ]);
    }

    /**
     * Get available log names for filter dropdown.
     */
    public function logNames(): JsonResponse
    {
        $logNames = $this->activityLogService->getAvailableLogNames();

        return response()->json([
            'success' => true,
            'data' => $logNames,
        ]);
    }

    /**
     * Get available event types for filter dropdown.
     */
    public function eventTypes(): JsonResponse
    {
        $eventTypes = $this->activityLogService->getAvailableEventTypes();

        return response()->json([
            'success' => true,
            'data' => $eventTypes,
        ]);
    }
}
