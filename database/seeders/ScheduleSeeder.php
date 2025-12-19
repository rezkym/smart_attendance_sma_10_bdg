<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SemesterType;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Seed the schedules table.
     */
    public function run(): void
    {
        // Get active academic year
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        if ($activeAcademicYear === null) {
            $this->command->error('No active academic year found. Please run AcademicYearSeeder first.');
            return;
        }

        // Get or create active semester for this academic year
        $activeSemester = Semester::where('academic_year_id', $activeAcademicYear->id)
            ->where('is_active', true)
            ->first();

        if ($activeSemester === null) {
            // Create ODD semester if none exists
            $activeSemester = Semester::firstOrCreate(
                [
                    'academic_year_id' => $activeAcademicYear->id,
                    'type' => SemesterType::ODD,
                ],
                [
                    'start_date' => $activeAcademicYear->start_date,
                    'end_date' => $activeAcademicYear->start_date->copy()->addMonths(6),
                    'is_active' => true,
                ]
            );
            $this->command->info("Created semester {$activeSemester->type->label()} for {$activeAcademicYear->name}.");
        }

        // Get active classrooms
        $classrooms = Classroom::where('is_active', true)
            ->where('academic_year_id', $activeAcademicYear->id)
            ->get();

        if ($classrooms->isEmpty()) {
            $this->command->error('No classrooms found. Please run ClassroomSeeder first.');
            return;
        }

        // Get subjects and teachers
        $subjects = Subject::where('is_active', true)->get();
        $teachers = Teacher::where('is_active', true)->get();

        if ($subjects->isEmpty() || $teachers->isEmpty()) {
            $this->command->error('No subjects or teachers found. Please run SubjectSeeder and TeacherSeeder first.');
            return;
        }

        // Schedule times
        $scheduleSlots = [
            ['start' => '07:00', 'end' => '07:45'],
            ['start' => '07:45', 'end' => '08:30'],
            ['start' => '08:30', 'end' => '09:15'],
            ['start' => '09:30', 'end' => '10:15'], // After break
            ['start' => '10:15', 'end' => '11:00'],
            ['start' => '11:00', 'end' => '11:45'],
            ['start' => '13:00', 'end' => '13:45'], // After lunch
            ['start' => '13:45', 'end' => '14:30'],
        ];

        // Days: 1=Monday to 6=Saturday
        $days = [1, 2, 3, 4, 5, 6];

        $created = 0;
        $subjectIndex = 0;
        $teacherIndex = 0;

        foreach ($classrooms->take(4) as $classroom) { // Only first 4 classrooms
            foreach ($days as $day) {
                // 4-6 lessons per day
                $lessonsPerDay = rand(4, 6);
                $usedSlots = array_slice($scheduleSlots, 0, $lessonsPerDay);

                foreach ($usedSlots as $slot) {
                    $subject = $subjects[$subjectIndex % $subjects->count()];
                    $teacher = $teachers[$teacherIndex % $teachers->count()];

                    $exists = Schedule::where('classroom_id', $classroom->id)
                        ->where('academic_year_id', $activeAcademicYear->id)
                        ->where('semester_id', $activeSemester->id)
                        ->where('day_of_week', $day)
                        ->where('start_time', $slot['start'])
                        ->exists();

                    if (!$exists) {
                        Schedule::create([
                            'classroom_id' => $classroom->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacher->id,
                            'academic_year_id' => $activeAcademicYear->id,
                            'semester_id' => $activeSemester->id,
                            'day_of_week' => $day,
                            'start_time' => $slot['start'],
                            'end_time' => $slot['end'],
                            'is_active' => true,
                        ]);
                        $created++;
                    }

                    $subjectIndex++;
                    $teacherIndex++;
                }
            }
        }

        $this->command->info("Seeded {$created} schedules for academic year {$activeAcademicYear->name}.");
    }
}
