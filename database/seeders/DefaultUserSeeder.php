<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            'admin' => [
                'name' => 'Admin',
                'email' => 'admin@a.com',
            ],
        ];

        foreach ($users as $role => $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('123456'),
                ]
            );

            $user->syncRoles([$role]);
        }
    }
}
