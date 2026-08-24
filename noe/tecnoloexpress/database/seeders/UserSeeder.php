<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Ricardo Erazo', 'email' => 'admin@tecnolocrm.test', 'role' => UserRole::Admin, 'commission_percent' => 0],
            ['name' => 'Lucía Mendoza', 'email' => 'gerencia@tecnolocrm.test', 'role' => UserRole::Gerente, 'commission_percent' => 10],
            ['name' => 'Andrés Cabrera', 'email' => 'andres@tecnolocrm.test', 'role' => UserRole::Agente, 'commission_percent' => 30],
            ['name' => 'Valeria Ponce', 'email' => 'valeria@tecnolocrm.test', 'role' => UserRole::Agente, 'commission_percent' => 30],
            ['name' => 'Jorge Salazar', 'email' => 'jorge@tecnolocrm.test', 'role' => UserRole::Agente, 'commission_percent' => 25],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [...$data, 'password' => 'password', 'active' => true, 'phone' => '+593 99 '.random_int(100, 999).' '.random_int(1000, 9999)],
            );
        }
    }
}
