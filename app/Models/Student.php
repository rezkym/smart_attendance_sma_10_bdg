<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $classroom_id
 * @property string $nisn
 * @property string $nis
 * @property string|null $rfid_card_number
 * @property string|null $photo
 * @property \Illuminate\Support\Carbon|null $enrollment_date
 * @property bool $is_active
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read Classroom|null $classroom
 * @property-read string $display_name
 */
class Student extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'classroom_id',
        'nisn',
        'nis',
        'rfid_card_number',
        'photo',
        'enrollment_date',
        'is_active',
        'notes',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = ['display_name'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enrollment_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the display name from user.
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->user?->display_name ?? '',
        );
    }

    /**
     * Get the user account associated with this student.
     *
     * @return BelongsTo<User, Student>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the classroom this student belongs to.
     *
     * @return BelongsTo<Classroom, Student>
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Scope to get only active students.
     *
     * @param Builder<Student> $query
     * @return Builder<Student>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by classroom.
     *
     * @param Builder<Student> $query
     * @return Builder<Student>
     */
    public function scopeInClassroom(Builder $query, int $classroomId): Builder
    {
        return $query->where('classroom_id', $classroomId);
    }
}

