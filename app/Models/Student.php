<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $classroom_id
 * @property string $nisn
 * @property string $nis
 * @property string $full_name
 * @property Gender $gender
 * @property string|null $birth_place
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property string|null $address
 * @property string|null $phone_number
 * @property string|null $rfid_card_number
 * @property string|null $photo
 * @property \Illuminate\Support\Carbon|null $enrollment_date
 * @property bool $is_active
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Classroom|null $classroom
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
        'full_name',
        'gender',
        'birth_place',
        'birth_date',
        'address',
        'phone_number',
        'rfid_card_number',
        'photo',
        'enrollment_date',
        'is_active',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'birth_date' => 'date',
            'enrollment_date' => 'date',
            'is_active' => 'boolean',
        ];
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

    /**
     * Get the gender label for display.
     */
    public function getGenderLabelAttribute(): string
    {
        return $this->gender->label();
    }
}
