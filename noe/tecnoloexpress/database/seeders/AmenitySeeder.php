<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            'Piscina' => 'heroicon-o-sparkles',
            'Ascensor' => 'heroicon-o-arrows-up-down',
            'Gimnasio' => 'heroicon-o-bolt',
            'Seguridad 24h' => 'heroicon-o-shield-check',
            'Terraza' => 'heroicon-o-sun',
            'Jardín' => 'heroicon-o-globe-americas',
            'Amoblado' => 'heroicon-o-home-modern',
            'Acepta mascotas' => 'heroicon-o-heart',
            'Aire acondicionado' => 'heroicon-o-cloud',
            'Bodega' => 'heroicon-o-archive-box',
            'Área BBQ' => 'heroicon-o-fire',
            'Sala comunal' => 'heroicon-o-users',
        ];

        foreach ($amenities as $name => $icon) {
            Amenity::updateOrCreate(['name' => $name], ['icon' => $icon]);
        }
    }
}
