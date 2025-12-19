<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SemesterType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $academic_year_id
 * @property SemesterType $type
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read AcademicYear $academicYear
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Schedule> $schedules
 */
class Semester extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'academic_year_id',
        'type',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SemesterType::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the academic year for this semester.
     *
     * @return BelongsTo<AcademicYear, Semester>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the schedules for this semester.
     *
     * @return HasMany<Schedule>
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Scope to get only active semester.
     *
     * @param Builder<Semester> $query
     * @return Builder<Semester>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by academic year.
     *
     * @param Builder<Semester> $query
     * @return Builder<Semester>
     */
    public function scopeByAcademicYear(Builder $query, int $academicYearId): Builder
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope to filter by type.
     *
     * @param Builder<Semester> $query
     * @return Builder<Semester>
     */
    public function scopeByType(Builder $query, SemesterType $type): Builder
    {
        return $query->where('type', $type);
    }
}
