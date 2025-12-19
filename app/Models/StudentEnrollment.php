<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $student_id
 * @property int $classroom_id
 * @property int $academic_year_id
 * @property \Illuminate\Support\Carbon $enrolled_at
 * @property \Illuminate\Support\Carbon|null $left_at
 * @property EnrollmentStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Student $student
 * @property-read Classroom $classroom
 * @property-read AcademicYear $academicYear
 */
class StudentEnrollment extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Get the activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName): string => "Student Enrollment {$eventName}")
            ->useLogName('student-enrollment');
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'classroom_id',
        'academic_year_id',
        'enrolled_at',
        'left_at',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enrolled_at' => 'date',
            'left_at' => 'date',
            'status' => EnrollmentStatus::class,
        ];
    }

    /**
     * Get the student for this enrollment.
     *
     * @return BelongsTo<Student, StudentEnrollment>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the classroom for this enrollment.
     *
     * @return BelongsTo<Classroom, StudentEnrollment>
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Get the academic year for this enrollment.
     *
     * @return BelongsTo<AcademicYear, StudentEnrollment>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Scope to get only active enrollments.
     *
     * @param Builder<StudentEnrollment> $query
     * @return Builder<StudentEnrollment>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::ACTIVE);
    }

    /**
     * Scope to filter by student.
     *
     * @param Builder<StudentEnrollment> $query
     * @return Builder<StudentEnrollment>
     */
    public function scopeByStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to filter by classroom.
     *
     * @param Builder<StudentEnrollment> $query
     * @return Builder<StudentEnrollment>
     */
    public function scopeByClassroom(Builder $query, int $classroomId): Builder
    {
        return $query->where('classroom_id', $classroomId);
    }

    /**
     * Scope to filter by academic year.
     *
     * @param Builder<StudentEnrollment> $query
     * @return Builder<StudentEnrollment>
     */
    public function scopeByAcademicYear(Builder $query, int $academicYearId): Builder
    {
        return $query->where('academic_year_id', $academicYearId);
    }
}
