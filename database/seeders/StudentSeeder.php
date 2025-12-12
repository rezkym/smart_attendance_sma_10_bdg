<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Seed the students table by linking existing student users to student profiles.
     */
    public function run(): void
    {
        // Get users with student role who don't have student profile yet
        $studentUsers = User::role('student')
            ->whereDoesntHave('student')
            ->get();

        if ($studentUsers->isEmpty()) {
            $this->command->warn('No student users without profile found. Run StudentUserSeeder first.');
            return;
        }

        // Get first classroom for assignment
        $classroom = Classroom::where('is_active', true)->first();

        $classroomId = $classroom?->id;
        $nisnPrefix = '00' . date('Y');
        $nisPrefix = date('y');

        $created = 0;
        foreach ($studentUsers as $index => $user) {
            $nisn = $nisnPrefix . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
            $nis = $nisPrefix . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

            Student::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nisn' => $nisn,
                    'nis' => $nis,
                    'classroom_id' => $classroomId,
                    'enrollment_date' => now()->subMonths(rand(1, 24)),
                    'rfid_card_number' => 'RFID' . strtoupper(substr(md5((string) $user->id), 0, 8)),
                    'is_active' => true,
                ]
            );
            $created++;
        }

        $this->command->info("Seeded {$created} student profiles from existing student users.");
    }
}
