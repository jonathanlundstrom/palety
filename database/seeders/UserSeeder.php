<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        User::factory()->create([
            'name' => 'Palety Developer',
            'email' => 'developer@palety.se',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
        ]);
    }
}
