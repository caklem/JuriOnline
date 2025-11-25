<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin_owner',
                'display_name' => 'Admin Owner',
                'description' => 'Super admin yang dapat melihat semua event dan transaksi sebagai pengawas'
            ],
            [
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Admin yang dapat mengelola semua event dan memverifikasi pembayaran'
            ],
            [
                'name' => 'organizer',
                'display_name' => 'Organizer',
                'description' => 'Penyelenggara event yang dapat membuat dan mengelola event'
            ],
            [
                'name' => 'participant',
                'display_name' => 'Peserta',
                'description' => 'Peserta yang dapat mendaftar event dan melihat status pendaftaran'
            ],
            [
                'name' => 'jury',
                'display_name' => 'Juri',
                'description' => 'Juri yang memberikan penilaian kepada peserta'
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
