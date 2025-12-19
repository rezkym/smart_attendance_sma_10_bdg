<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EnrollmentStatus;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Seeder;

class StudentEnrollmentSeeder extends Seeder
{
    /**
     * Seed the student_enrollments table.
     * Assigns each active student to a classroom via enrollment.
     */
    public function run(): void
    {
        // Get active academic year
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        if ($activeAcademicYear === null) {
            $this->command->error('No active academic year found. Please run AcademicYearSeeder first.');
            return;
        }

        // Get active classrooms for this academic year
        $classrooms = Classroom::where('is_active', true)
            ->where('academic_year_id', $activeAcademicYear->id)
            ->get();

        if ($classrooms->isEmpty()) {
            $this->command->error('No classrooms found. Please run ClassroomSeeder first.');
            return;
        }

        // Get all active students
        $students = Student::where('is_active', true)->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students found. Please run StudentSeeder first.');
            return;
        }

        $created = 0;
        $classroomCount = $classrooms->count();

        foreach ($students as $index => $student) {
            // Distribute students across classrooms evenly
            $classroom = $classrooms[$index % $classroomCount];

            // Check if enrollment already exists
            $exists = StudentEnrollment::where('student_id', $student->id)
                ->where('academic_year_id', $activeAcademicYear->id)
                ->where('status', EnrollmentStatus::ACTIVE)
                ->exists();

            if (!$exists) {
                StudentEnrollment::create([
                    'student_id' => $student->id,
                    'classroom_id' => $classroom->id,
                    'academic_year_id' => $activeAcademicYear->id,
                    'enrolled_at' => $student->enrollment_date ?? now(),
                    'status' => EnrollmentStatus::ACTIVE,
                ]);
                $created++;
            }
        }

        $this->command->info("Seeded {$created} student enrollments for academic year {$activeAcademicYear->name}.");
    }
}
