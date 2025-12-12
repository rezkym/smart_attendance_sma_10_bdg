<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DayOfWeek;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $classroom_id
 * @property int $subject_id
 * @property int $teacher_id
 * @property int $academic_year_id
 * @property DayOfWeek $day_of_week
 * @property string $start_time
 * @property string $end_time
 * @property bool $is_active
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Classroom $classroom
 * @property-read Subject $subject
 * @property-read Teacher $teacher
 * @property-read AcademicYear $academicYear
 */
class Schedule extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'classroom_id',
        'subject_id',
        'teacher_id',
        'academic_year_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => DayOfWeek::class,
            'start_time' => 'string',
            'end_time' => 'string',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the classroom for this schedule.
     *
     * @return BelongsTo<Classroom, Schedule>
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Get the subject for this schedule.
     *
     * @return BelongsTo<Subject, Schedule>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher for this schedule.
     *
     * @return BelongsTo<Teacher, Schedule>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the academic year for this schedule.
     *
     * @return BelongsTo<AcademicYear, Schedule>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Scope to get only active schedules.
     *
     * @param Builder<Schedule> $query
     * @return Builder<Schedule>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by classroom.
     *
     * @param Builder<Schedule> $query
     * @return Builder<Schedule>
     */
    public function scopeByClassroom(Builder $query, int $classroomId): Builder
    {
        return $query->where('classroom_id', $classroomId);
    }

    /**
     * Scope to filter by academic year.
     *
     * @param Builder<Schedule> $query
     * @return Builder<Schedule>
     */
    public function scopeByAcademicYear(Builder $query, int $academicYearId): Builder
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope to filter by day of week.
     *
     * @param Builder<Schedule> $query
     * @return Builder<Schedule>
     */
    public function scopeByDay(Builder $query, int $dayOfWeek): Builder
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    /**
     * Scope to filter by teacher.
     *
     * @param Builder<Schedule> $query
     * @return Builder<Schedule>
     */
    public function scopeByTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }
}
