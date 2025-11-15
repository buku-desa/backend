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
            'password' => Hash::make('sekdes112345'),
            'role' => 'sekdes',
        ]);
        User::create([
            'id' => (string) Str::uuid(),
            'username' => 'sekdes2',
            'name' => 'Sekretaris Desa 2',
            'email' => 'sekdes2@example.com',
            'password' => Hash::make('sekdes212345'),
            'role' => 'sekdes',
        ]);
        User::create([
            'id' => (string) Str::uuid(),
            'username' => 'sekdes3',
            'name' => 'Sekretaris Desa 3',
            'email' => 'sekdes3@example.com',
            'password' => Hash::make('sekdes312345'),
            'role' => 'sekdes',
        ]);

        //kepdes
        User::create([
            'id' => (string) Str::uuid(),
            'username' => 'kepdes1',
            'name' => 'Kepala Desa 1',
            'email' => 'kepdes1@example.com',
            'password' => Hash::make('kepdes12345'),
            'role' => 'kepdes',
        ]);
    }
}
