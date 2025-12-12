<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['code' => 'MTK', 'name' => 'Matematika', 'description' => 'Mata pelajaran Matematika'],
            ['code' => 'BIN', 'name' => 'Bahasa Indonesia', 'description' => 'Mata pelajaran Bahasa Indonesia'],
            ['code' => 'BIG', 'name' => 'Bahasa Inggris', 'description' => 'Mata pelajaran Bahasa Inggris'],
            ['code' => 'FIS', 'name' => 'Fisika', 'description' => 'Mata pelajaran Fisika'],
            ['code' => 'KIM', 'name' => 'Kimia', 'description' => 'Mata pelajaran Kimia'],
            ['code' => 'BIO', 'name' => 'Biologi', 'description' => 'Mata pelajaran Biologi'],
            ['code' => 'SEJ', 'name' => 'Sejarah', 'description' => 'Mata pelajaran Sejarah'],
            ['code' => 'GEO', 'name' => 'Geografi', 'description' => 'Mata pelajaran Geografi'],
            ['code' => 'EKO', 'name' => 'Ekonomi', 'description' => 'Mata pelajaran Ekonomi'],
            ['code' => 'SOS', 'name' => 'Sosiologi', 'description' => 'Mata pelajaran Sosiologi'],
            ['code' => 'PKN', 'name' => 'Pendidikan Kewarganegaraan', 'description' => 'Mata pelajaran PKN'],
            ['code' => 'PAI', 'name' => 'Pendidikan Agama Islam', 'description' => 'Mata pelajaran PAI'],
            ['code' => 'PJK', 'name' => 'Pendidikan Jasmani dan Kesehatan', 'description' => 'Mata pelajaran Penjas'],
            ['code' => 'SBK', 'name' => 'Seni Budaya', 'description' => 'Mata pelajaran Seni Budaya'],
            ['code' => 'TIK', 'name' => 'Teknologi Informasi', 'description' => 'Mata pelajaran TIK'],
        ];

        $created = 0;
        foreach ($subjects as $subjectData) {
            Subject::updateOrCreate(
                ['code' => $subjectData['code']],
                array_merge($subjectData, ['is_active' => true])
            );
            $created++;
        }

        $this->command->info("Seeded/Updated {$created} subjects.");
    }
}
