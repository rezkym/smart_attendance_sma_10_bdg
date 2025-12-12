<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Gender;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Seed the teachers table with user accounts.
     */
    public function run(): void
    {
        $teachers = [
            [
                'name' => 'budi_santoso',
                'full_name' => 'Budi Santoso, S.Pd.',
                'email' => 'budi.santoso@sman10.sch.id',
                'gender' => Gender::MALE,
                'nip' => '198501152010011001',
            ],
            [
                'name' => 'siti_nurhaliza',
                'full_name' => 'Siti Nurhaliza, M.Pd.',
                'email' => 'siti.nurhaliza@sman10.sch.id',
                'gender' => Gender::FEMALE,
                'nip' => '198702202011012002',
            ],
            [
                'name' => 'ahmad_hidayat',
                'full_name' => 'Ahmad Hidayat, S.Si.',
                'email' => 'ahmad.hidayat@sman10.sch.id',
                'gender' => Gender::MALE,
                'nip' => '198903252012011003',
            ],
            [
                'name' => 'dewi_lestari',
                'full_name' => 'Dewi Lestari, S.Pd.',
                'email' => 'dewi.lestari@sman10.sch.id',
                'gender' => Gender::FEMALE,
                'nip' => '199004302013012004',
            ],
            [
                'name' => 'eko_prasetyo',
                'full_name' => 'Eko Prasetyo, M.Si.',
                'email' => 'eko.prasetyo@sman10.sch.id',
                'gender' => Gender::MALE,
                'nip' => '198605152014011005',
            ],
            [
                'name' => 'fitri_handayani',
                'full_name' => 'Fitri Handayani, S.Pd.',
                'email' => 'fitri.handayani@sman10.sch.id',
                'gender' => Gender::FEMALE,
                'nip' => '199106202015012006',
            ],
            [
                'name' => 'gunawan_wijaya',
                'full_name' => 'Gunawan Wijaya, S.Kom.',
                'email' => 'gunawan.wijaya@sman10.sch.id',
                'gender' => Gender::MALE,
                'nip' => '198807252016011007',
            ],
            [
                'name' => 'hana_pertiwi',
                'full_name' => 'Hana Pertiwi, S.Pd.',
                'email' => 'hana.pertiwi@sman10.sch.id',
                'gender' => Gender::FEMALE,
                'nip' => '199208302017012008',
            ],
        ];

        foreach ($teachers as $teacherData) {
            // Create user first
            $user = User::updateOrCreate(
                ['email' => $teacherData['email']],
                [
                    'name' => $teacherData['name'],
                    'full_name' => $teacherData['full_name'],
                    'gender' => $teacherData['gender'],
                    'password' => Hash::make('123456'),
                ]
            );

            $user->syncRoles(['teacher']);

            // Create teacher profile
            Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => $teacherData['nip'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Seeded ' . count($teachers) . ' teachers with user accounts.');
    }
}
