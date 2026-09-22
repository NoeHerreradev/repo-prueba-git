<?php

namespace Database\Seeders;

use App\Models\CustomForm;
use Illuminate\Database\Seeder;

class CustomFormSeeder extends Seeder
{
    public function run(): void
    {
        foreach (CustomForm::FORMS as $key => $info) {
            CustomForm::firstOrCreate(
                ['key' => $key],
                [
                    'name' => $info['name'],
                    'description' => $info['description'],
                    'blocks' => CustomForm::defaultBlocksFor($key),
                    'is_active' => true,
                ]
            );
        }
    }
}
