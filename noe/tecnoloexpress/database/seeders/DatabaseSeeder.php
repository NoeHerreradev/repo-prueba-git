<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            AmenitySeeder::class,
            UserSeeder::class,
            ContactSeeder::class,
            PropertySeeder::class,
            LeadSeeder::class,
            VisitSeeder::class,
            ContractSeeder::class,
            CustomFormSeeder::class,
        ]);
    }
}
