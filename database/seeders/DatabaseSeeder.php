<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $administrator = User::updateOrCreate(['email' => 'admin@ngebadmintonyuk.com'], [
            'name' => 'Admin NgeKas',
            'password' => 'password',
        ]);
        $administrator->forceFill(['role' => 'admin'])->save();

        foreach (['Iuran Main', 'Donasi', 'Lainnya'] as $name) {
            Category::firstOrCreate(compact('name') + ['type' => 'income']);
        }
        foreach (['Lapangan', 'Shuttlecock', 'Konsumsi', 'Lainnya'] as $name) {
            Category::firstOrCreate(compact('name') + ['type' => 'expense']);
        }
    }
}
