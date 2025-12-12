<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Seed the academic years table.
     */
    public function run(): void
    {
        $academicYears = [
            [
                'name' => '2023/2024',
                'start_date' => '2023-07-17',
                'end_date' => '2024-06-15',
                'is_active' => false,
                'description' => 'Tahun Akademik 2023/2024',
            ],
            [
                'name' => '2024/2025',
                'start_date' => '2024-07-15',
                'end_date' => '2025-06-14',
                'is_active' => true,
                'description' => 'Tahun Akademik 2024/2025 (Aktif)',
            ],
            [
                'name' => '2025/2026',
                'start_date' => '2025-07-14',
                'end_date' => '2026-06-13',
                'is_active' => false,
                'description' => 'Tahun Akademik 2025/2026',
            ],
        ];

        $created = 0;
        foreach ($academicYears as $yearData) {
            AcademicYear::updateOrCreate(
                ['name' => $yearData['name']],
                $yearData
            );
            $created++;
        }

        $this->command->info("Seeded/Updated {$created} academic years.");
    }
}
