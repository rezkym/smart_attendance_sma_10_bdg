<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Gender;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentUserSeeder extends Seeder
{
    /**
     * Seed student users with role assignment.
     * These users have the 'student' role but are NOT attached to the Student feature.
     */
    public function run(): void
    {
        $students = [
            [
                'name' => 'reizo',
                'full_name' => 'Reizo Oktavian Azzam Jatnika',
                'email' => 'reizko@sman10.sch.id',
                'gender' => Gender::MALE,
            ],
            [
                'name' => 'oktaviani',
                'full_name' => 'Oktaviani Salwa Priatna',
                'email' => 'oktaviani@sman10.sch.id',
                'gender' => Gender::FEMALE,
            ],
            [
                'name' => 'silvy',
                'full_name' => 'Silvy Angraini Syafputri',
                'email' => 'silvy@sman10.sch.id',
                'gender' => Gender::FEMALE,
            ],
            [
                'name' => 'keyla',
                'full_name' => 'Keyla Ramadanisa',
                'email' => 'keyla@sman10.sch.id',
                'gender' => Gender::FEMALE,
            ],
            [
                'name' => 'raihan',
                'full_name' => 'Raihan Ramadhan Permana',
                'email' => 'raihan@sman10.sch.id',
                'gender' => Gender::MALE,
            ],
            [
                'name' => 'tjikyam',
                'full_name' => 'Tjikyam Ainun Habibie',
                'email' => 'tjikyam@sman10.sch.id',
                'gender' => Gender::MALE,
            ],
            [
                'name' => 'diandra',
                'full_name' => 'Diandra Kanza Billa',
                'email' => 'diandra@sman10.sch.id',
                'gender' => Gender::FEMALE,
            ],
            [
                'name' => 'rizal',
                'full_name' => 'Muhammad Rizal Sawaludin',
                'email' => 'rizal@sman10.sch.id',
                'gender' => Gender::MALE,
            ],
            [
                'name' => 'kaisara',
                'full_name' => 'Kaisara Anindya',
                'email' => 'kaisara@sman10.sch.id',
                'gender' => Gender::FEMALE,
            ],
            [
                'name' => 'rizky',
                'full_name' => 'Rizky Pratama Ramadhan',
                'email' => 'rizky@sman10.sch.id',
                'gender' => Gender::MALE,
            ],
            [
                'name' => 'rafi',
                'full_name' => 'Rafi Prayuga Puadi',
                'email' => 'rafi@sman10.sch.id',
                'gender' => Gender::MALE,
            ],
            [
                'name' => 'syarif',
                'full_name' => 'Syarif Shalosa',
                'email' => 'syarif@sman10.sch.id',
                'gender' => Gender::MALE,
            ],
            [
                'name' => 'eza',
                'full_name' => 'Eza Rendy Wijaya',
                'email' => 'eza@sman10.sch.id',
                'gender' => Gender::MALE,
            ],
            [
                'name' => 'syabilla',
                'full_name' => 'Syabilla Maulidina Putri',
                'email' => 'syabila@sman10.sch.id',
                'gender' => Gender::FEMALE,
            ],
            [
                'name' => 'imran',
                'full_name' => 'Muhammad Imran Sugiarto',
                'email' => 'imran@sman10.sch.id',
                'gender' => Gender::MALE,
            ],
            [
                'name' => 'jahra',
                'full_name' => 'Jahra Damayanthi Gumilar',
                'email' => 'jahra@sman10.sch.id',
                'gender' => Gender::FEMALE,
            ],
            [
                'name' => 'willena',
                'full_name' => 'Willena Reyadillin Clara Ashari',
                'email' => 'wilena@sman10.sch.id',
                'gender' => Gender::FEMALE,
            ],
            [
                'name' => 'akbar',
                'full_name' => 'Muhammad Akbar Ilham Syah',
                'email' => 'akbar@sman10.sch.id',
                'gender' => Gender::MALE,
            ],
            [
                'name' => 'sabila',
                'full_name' => 'Sabila Arsya',
                'email' => 'sabila@sman10.sch.id',
                'gender' => Gender::FEMALE,
            ],
            [
                'name' => 'jihan',
                'full_name' => 'Jihan Zahira Azhar Mulyana',
                'email' => 'jihan@sman10.sch.id',
                'gender' => Gender::FEMALE,
            ],
            [
                'name' => 'dipa',
                'full_name' => 'Dipa Nurfitria',
                'email' => 'dipa@sman10.sch.id',
                'gender' => Gender::FEMALE,
            ],
            [
                'name' => 'quicka',
                'full_name' => 'Enreiquicka Pricillia',
                'email' => 'quicka@sman10.sch.id',
                'gender' => Gender::FEMALE,
            ],
            [
                'name' => 'ahmad',
                'full_name' => 'Ahmad Rayhan Maulana Gunawan',
                'email' => 'ahmad@sman10.sch.id',
                'gender' => Gender::MALE,
            ],
            [
                'name' => 'aulya',
                'full_name' => 'Aulya Ramadhantyani',
                'email' => 'aulya@sman10.sch.id',
                'gender' => Gender::FEMALE,
            ],
        ];

        foreach ($students as $studentData) {
            $user = User::updateOrCreate(
                ['email' => $studentData['email']],
                [
                    'name' => $studentData['name'],
                    'full_name' => $studentData['full_name'],
                    'gender' => $studentData['gender'],
                    'password' => Hash::make('123456'),
                ]
            );

            $user->syncRoles(['student']);
        }

        $this->command->info('Seeded ' . count($students) . ' student users with role "student".');
    }
}
