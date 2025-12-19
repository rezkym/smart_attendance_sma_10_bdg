<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Order matters due to dependencies:
     * 1. PermissionRoleSeeder - Independent (permissions and roles)
     * 2. DefaultUserSeeder - Depends on roles
     * 3. AcademicYearSeeder - Independent
     * 4. SubjectSeeder - Independent
     * 5. StudentUserSeeder - Depends on roles (creates user accounts with 'student' role)
     * 6. TeacherSeeder - Depends on roles (creates user accounts with 'teacher' role)
     * 7. ClassroomSeeder - Depends on AcademicYear, Teachers
     * 8. StudentSeeder - Depends on StudentUsers, Classrooms
     * 9. ScheduleSeeder - Depends on Classrooms, Subjects, Teachers, AcademicYear
     */
    public function run(): void
    {
        $this->call([
            // Phase 1: Core (Independent)
            PermissionRoleSeeder::class,
            DefaultUserSeeder::class,
            // AcademicYearSeeder::class,
            // SubjectSeeder::class,

            // Phase 2: Users with Roles
            // StudentUserSeeder::class,
            // TeacherSeeder::class,

            // Phase 3: Master Data with Dependencies
            // ClassroomSeeder::class,
            // StudentSeeder::class,
            // StudentEnrollmentSeeder::class,

            // Phase 4: Complex Dependencies
            // ScheduleSeeder::class,
        ]);
    }
}
