<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //sekdes
        User::create([
            'id' => (string) Str::uuid(),
            'username' => 'sekdes1',
            'name' => 'Sekretaris Desa 1',
            'email' => 'sekdes1@example.com',
            'password' => Hash::make('123'),
            'role' => 'sekdes',
        ]);

        //kepdes
        User::create([
            'id' => (string) Str::uuid(),
            'username' => 'kepdes1',
            'name' => 'Kepala Desa 1',
            'email' => 'kepdes1@example.com',
            'password' => Hash::make('123'),
            'role' => 'kepdes',
        ]);
    }
}
