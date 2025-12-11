<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property int $grade_level
 * @property int $academic_year_id
 * @property int|null $capacity
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read AcademicYear $academicYear
 */
class Classroom extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'grade_level',
        'academic_year_id',
        'capacity',
        'description',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'grade_level' => 'integer',
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the academic year for this classroom.
     *
     * @return BelongsTo<AcademicYear, Classroom>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Scope to get only active classrooms.
     *
     * @param Builder<Classroom> $query
     * @return Builder<Classroom>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by grade level.
     *
     * @param Builder<Classroom> $query
     * @return Builder<Classroom>
     */
    public function scopeByGradeLevel(Builder $query, int $gradeLevel): Builder
    {
        return $query->where('grade_level', $gradeLevel);
    }
}
