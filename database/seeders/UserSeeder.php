<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User pertama - akan diupdate jika email sudah ada, atau dibuat baru jika belum ada
        User::updateOrCreate(
            ['email' => 'user@mail.com'], // Kolom yang digunakan untuk mencari
            [
                'name' => 'User',
                'password' => bcrypt('user')
            ]
        );
        
        // Anda bisa menambahkan lebih banyak user dengan cara yang sama
        User::updateOrCreate(
            ['email' => 'mono@mail.com'],
            [
                'name' => 'Mas Mono',
                'password' => bcrypt('mono01')
            ]
        );
        
        User::updateOrCreate(
            ['email' => 'danny@mail.com'],
            [
                'name' => 'Pak Danny',
                'password' => bcrypt('pakdanny01')
            ]
        );

        User::updateOrCreate(
            ['email' => 'alvif@mail.com'],
            [
                'name' => 'Alvif',
                'password' => bcrypt('alvif01')
            ]
        );

    }
}
