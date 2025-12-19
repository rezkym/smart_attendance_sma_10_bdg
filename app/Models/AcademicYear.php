<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property bool $is_active
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Semester> $semesters
 */
class AcademicYear extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope to get only active academic year.
     *
     * @param Builder<AcademicYear> $query
     * @return Builder<AcademicYear>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get academic years ordered by start date descending.
     *
     * @param Builder<AcademicYear> $query
     * @return Builder<AcademicYear>
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('start_date', 'desc');
    }

    /**
     * Get the semesters for this academic year.
     *
     * @return HasMany<Semester>
     */
    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }
}
