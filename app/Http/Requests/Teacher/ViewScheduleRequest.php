<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher;

use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;

class ViewScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        // Check basic permission
        if (! $user->can('teacher-schedules.view-own')) {
            return false;
        }

        // Verify schedule belongs to the teacher
        $schedule = $this->route('schedule');
        if (! $schedule instanceof Schedule) {
            return false;
        }

        $teacher = $user->teacher;
        if ($teacher === null) {
            return false;
        }

        return $schedule->teacher_id === $teacher->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Get the schedule from the route.
     */
    public function getSchedule(): Schedule
    {
        /** @var Schedule $schedule */
        $schedule = $this->route('schedule');

        return $schedule;
    }
}
