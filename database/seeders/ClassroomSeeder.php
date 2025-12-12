<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    /**
     * Seed the classrooms table.
     */
    public function run(): void
    {
        // Get active academic year
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        if ($activeAcademicYear === null) {
            $this->command->error('No active academic year found. Please run AcademicYearSeeder first.');
            return;
        }

        // Get teachers for homeroom assignment
        $teachers = Teacher::with('user')->where('is_active', true)->get();

        $classrooms = [
            // Grade 10
            ['name' => 'X IPA 1', 'grade_level' => 10, 'capacity' => 36],
            ['name' => 'X IPA 2', 'grade_level' => 10, 'capacity' => 36],
            ['name' => 'X IPS 1', 'grade_level' => 10, 'capacity' => 36],
            ['name' => 'X IPS 2', 'grade_level' => 10, 'capacity' => 36],
            // Grade 11
            ['name' => 'XI IPA 1', 'grade_level' => 11, 'capacity' => 36],
            ['name' => 'XI IPA 2', 'grade_level' => 11, 'capacity' => 36],
            ['name' => 'XI IPS 1', 'grade_level' => 11, 'capacity' => 36],
            ['name' => 'XI IPS 2', 'grade_level' => 11, 'capacity' => 36],
            // Grade 12
            ['name' => 'XII IPA 1', 'grade_level' => 12, 'capacity' => 36],
            ['name' => 'XII IPA 2', 'grade_level' => 12, 'capacity' => 36],
            ['name' => 'XII IPS 1', 'grade_level' => 12, 'capacity' => 36],
            ['name' => 'XII IPS 2', 'grade_level' => 12, 'capacity' => 36],
        ];

        $teacherIndex = 0;
        $teacherCount = $teachers->count();

        foreach ($classrooms as $classroomData) {
            $homeroomTeacherId = null;
            if ($teacherCount > 0) {
                $homeroomTeacherId = $teachers[$teacherIndex % $teacherCount]->id;
                $teacherIndex++;
            }

            Classroom::updateOrCreate(
                [
                    'name' => $classroomData['name'],
                    'academic_year_id' => $activeAcademicYear->id,
                ],
                [
                    'grade_level' => $classroomData['grade_level'],
                    'capacity' => $classroomData['capacity'],
                    'homeroom_teacher_id' => $homeroomTeacherId,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Seeded ' . count($classrooms) . ' classrooms for academic year ' . $activeAcademicYear->name . '.');
    }
}
