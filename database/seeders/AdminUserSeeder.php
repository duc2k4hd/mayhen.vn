<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@mayhen.vn'],
            [
                'name' => 'Quản Trị Viên Mây Hẹn',
                'phone' => '0900000000',
                'password' => Hash::make('ducnobi2004'),
                'role' => 'admin',
            ]
        );
    }
}
