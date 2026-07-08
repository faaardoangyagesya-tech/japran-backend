<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Game;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'username' => 'superadmin',
            'password' => Hash::make('japranadmin123'),
            'role' => 'superadmin',
        ]);

        Game::create(['name' => 'Free Fire']);
        Game::create(['name' => 'Mobile Legends']);
        Game::create(['name' => 'PUBG Mobile']);
    }
}
