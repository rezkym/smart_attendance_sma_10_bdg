<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // Mata Pelajaran Umum
            [
                'name' => 'Pendidikan Agama dan Budi Pekerti',
                'code' => 'PAI',
                'description' => 'Mata pelajaran Pendidikan Agama Islam dan Budi Pekerti',
                'credit_hours' => 3,
            ],
            [
                'name' => 'Pendidikan Pancasila dan Kewarganegaraan',
                'code' => 'PPKn',
                'description' => 'Mata pelajaran Pendidikan Pancasila dan Kewarganegaraan',
                'credit_hours' => 2,
            ],
            [
                'name' => 'Bahasa Indonesia',
                'code' => 'BIND',
                'description' => 'Mata pelajaran Bahasa Indonesia',
                'credit_hours' => 4,
            ],
            [
                'name' => 'Bahasa Inggris',
                'code' => 'BING',
                'description' => 'Mata pelajaran Bahasa Inggris',
                'credit_hours' => 3,
            ],
            [
                'name' => 'Matematika',
                'code' => 'MAT',
                'description' => 'Mata pelajaran Matematika',
                'credit_hours' => 4,
            ],
            [
                'name' => 'Sejarah Indonesia',
                'code' => 'SEJ',
                'description' => 'Mata pelajaran Sejarah Indonesia',
                'credit_hours' => 2,
            ],
            [
                'name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan',
                'code' => 'PJOK',
                'description' => 'Mata pelajaran Pendidikan Jasmani, Olahraga, dan Kesehatan',
                'credit_hours' => 3,
            ],
            [
                'name' => 'Seni Budaya',
                'code' => 'SB',
                'description' => 'Mata pelajaran Seni Budaya',
                'credit_hours' => 2,
            ],

            // Mata Pelajaran Peminatan IPA
            [
                'name' => 'Matematika Peminatan',
                'code' => 'MATP',
                'description' => 'Mata pelajaran Matematika Peminatan untuk jurusan IPA',
                'credit_hours' => 4,
            ],
            [
                'name' => 'Biologi',
                'code' => 'BIO',
                'description' => 'Mata pelajaran Biologi',
                'credit_hours' => 4,
            ],
            [
                'name' => 'Fisika',
                'code' => 'FIS',
                'description' => 'Mata pelajaran Fisika',
                'credit_hours' => 4,
            ],
            [
                'name' => 'Kimia',
                'code' => 'KIM',
                'description' => 'Mata pelajaran Kimia',
                'credit_hours' => 4,
            ],

            // Mata Pelajaran Peminatan IPS
            [
                'name' => 'Geografi',
                'code' => 'GEO',
                'description' => 'Mata pelajaran Geografi',
                'credit_hours' => 4,
            ],
            [
                'name' => 'Sejarah Peminatan',
                'code' => 'SEJP',
                'description' => 'Mata pelajaran Sejarah Peminatan untuk jurusan IPS',
                'credit_hours' => 4,
            ],
            [
                'name' => 'Sosiologi',
                'code' => 'SOS',
                'description' => 'Mata pelajaran Sosiologi',
                'credit_hours' => 4,
            ],
            [
                'name' => 'Ekonomi',
                'code' => 'EKO',
                'description' => 'Mata pelajaran Ekonomi',
                'credit_hours' => 4,
            ],

            // Mata Pelajaran Pilihan
            [
                'name' => 'Bahasa Arab',
                'code' => 'BARB',
                'description' => 'Mata pelajaran Bahasa Arab',
                'credit_hours' => 2,
            ],
            [
                'name' => 'Bahasa Jepang',
                'code' => 'BJPN',
                'description' => 'Mata pelajaran Bahasa Jepang',
                'credit_hours' => 2,
            ],
            [
                'name' => 'Teknologi Informasi dan Komunikasi',
                'code' => 'TIK',
                'description' => 'Mata pelajaran Teknologi Informasi dan Komunikasi',
                'credit_hours' => 2,
            ],
            [
                'name' => 'Prakarya dan Kewirausahaan',
                'code' => 'PKK',
                'description' => 'Mata pelajaran Prakarya dan Kewirausahaan',
                'credit_hours' => 2,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }

        $this->command->info('Subjects seeded successfully!');
    }
}
