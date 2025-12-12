<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $student_id
 * @property int $schedule_id
 * @property \Illuminate\Support\Carbon $attendance_date
 * @property string|null $check_in_time
 * @property string|null $check_out_time
 * @property AttendanceStatus $status
 * @property \Illuminate\Support\Carbon|null $rfid_scan_time
 * @property string|null $notes
 * @property int|null $recorded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Student $student
 * @property-read Schedule $schedule
 * @property-read User|null $recorder
 */
class Attendance extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'schedule_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'status',
        'rfid_scan_time',
        'notes',
        'recorded_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'rfid_scan_time' => 'datetime',
            'status' => AttendanceStatus::class,
        ];
    }

    /**
     * Get the student for this attendance.
     *
     * @return BelongsTo<Student, Attendance>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the schedule for this attendance.
     *
     * @return BelongsTo<Schedule, Attendance>
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the user who recorded this attendance (if manual entry).
     *
     * @return BelongsTo<User, Attendance>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Scope to filter by date.
     *
     * @param Builder<Attendance> $query
     * @return Builder<Attendance>
     */
    public function scopeByDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('attendance_date', $date);
    }

    /**
     * Scope to filter by student.
     *
     * @param Builder<Attendance> $query
     * @return Builder<Attendance>
     */
    public function scopeByStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to filter by schedule.
     *
     * @param Builder<Attendance> $query
     * @return Builder<Attendance>
     */
    public function scopeBySchedule(Builder $query, int $scheduleId): Builder
    {
        return $query->where('schedule_id', $scheduleId);
    }

    /**
     * Scope to filter by status.
     *
     * @param Builder<Attendance> $query
     * @return Builder<Attendance>
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by classroom (through schedule).
     *
     * @param Builder<Attendance> $query
     * @return Builder<Attendance>
     */
    public function scopeByClassroom(Builder $query, int $classroomId): Builder
    {
        return $query->whereHas('schedule', function (Builder $q) use ($classroomId) {
            $q->where('classroom_id', $classroomId);
        });
    }
}
