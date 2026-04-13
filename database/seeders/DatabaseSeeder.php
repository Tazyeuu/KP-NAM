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
        // 1. Buat Role (Menggunakan Spatie)
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        $techRole = Role::create(['name' => 'teknisi']);

        // 2. Buat Data Ruangan / Department RSUD
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

        // 3. Buat Data Kategori Kendala IT
        Category::create(['name' => 'Jaringan / Internet (WiFi/LAN)']);
        Category::create(['name' => 'Hardware (PC/Printer Rusak)']);
        Category::create(['name' => 'Software / SIMRS Error']);
        Category::create(['name' => 'Lainnya']);

        // 4. Buat Akun Admin (Tim IT)
        $admin = User::create([
            'name' => 'Admin IT RSUD',
            'email' => 'admin@rsud.com',
            'password' => Hash::make('password'), // Password login: password
            'department_id' => $manajemen->id,
        ]);
        $admin->assignRole($adminRole); // Berikan hak akses admin

        // 5. Buat Akun Staf / User Biasa
        $staf = User::create([
            'name' => 'Perawat IGD',
            'email' => 'perawat@rsud.com',
            'password' => Hash::make('password'), // Password login: password
            'department_id' => $igd->id,
        ]);
        $staf->assignRole($userRole); // Berikan hak akses user

        $teknisi = User::create([
            'name' => 'Teknisi IT',
            'email' => 'teknisi@rsud.com',
            'password' => Hash::make('password'),
            'department_id' => $manajemen->id,
        ]);
        $teknisi->assignRole($techRole);
    }
}
