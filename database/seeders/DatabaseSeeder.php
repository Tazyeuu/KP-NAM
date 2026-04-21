<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Category;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
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
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        $techRole = Role::create(['name' => 'teknisi']);

        $igd = Department::create([
            'name' => 'IGD',
            'latitude' => -0.054321,
            'longitude' => 109.345678,
        ]);
        $poliGigi = Department::create([
            'name' => 'Poli Gigi',
            'latitude' => -0.054322,
            'longitude' => 109.345679,
        ]);
        $farmasi = Department::create([
            'name' => 'Instalasi Farmasi',
            'latitude' => -0.054323,
            'longitude' => 109.345680,
        ]);
        $manajemen = Department::create([
            'name' => 'Ruang Manajemen',
            'latitude' => -0.054324,
            'longitude' => 109.345681,
        ]);

        Category::create(['name' => 'Jaringan']);
        Category::create(['name' => 'Hardware']);
        Category::create(['name' => 'Software']);

        $admin = User::create([
            'name' => 'Admin IT RSUD',
            'email' => 'admin@rsud.com',
            'password' => Hash::make('password'),
            'department_id' => $manajemen->id,
        ]);
        $admin->assignRole($adminRole);

        $staf = User::create([
            'name' => 'Perawat IGD',
            'email' => 'perawat@rsud.com',
            'password' => Hash::make('password'),
            'department_id' => $igd->id,
        ]);
        $staf->assignRole($userRole);

        $teknisi = User::create([
            'name' => 'Teknisi IT',
            'email' => 'teknisi@rsud.com',
            'password' => Hash::make('password'),
            'department_id' => $manajemen->id,
        ]);
        $teknisi->assignRole($techRole);
    }
}
