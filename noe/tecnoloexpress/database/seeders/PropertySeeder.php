<?php

namespace Database\Seeders;

use App\Enums\ContactType;
use App\Enums\PropertyOperation;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\UserRole;
use App\Models\Amenity;
use App\Models\Contact;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $agents = User::where('role', UserRole::Agente)->pluck('id')->all();
        $owners = Contact::where('type', ContactType::Propietario)->pluck('id')->all();
        $amenities = Amenity::pluck('id')->all();

        foreach ($this->definitions() as $i => $data) {
            $property = Property::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($data['title'])],
                [
                    ...$data,
                    'owner_id' => $owners[$i % count($owners)],
                    'agent_id' => $agents[$i % count($agents)],
                    'currency' => 'USD',
                    'commission_percent' => $data['operation'] === PropertyOperation::Alquiler ? 8 : 3,
                    'exclusive' => $i % 3 === 0,
                    'captured_at' => now()->subDays(random_int(10, 240)),
                    'views_count' => random_int(0, 350),
                ],
            );

            $property->amenities()->sync(
                collect($amenities)->shuffle()->take(random_int(3, 6))->all(),
            );

            $this->seedImages($property);
        }
    }

    /**
     * Genera portadas SVG de marcador de posición para que la galería y el
     * portal tengan contenido visible sin depender de archivos externos.
     */
    protected function seedImages(Property $property): void
    {
        if ($property->images()->exists()) {
            return;
        }

        $palettes = [
            ['#6366f1', '#a855f7'],
            ['#0ea5e9', '#22d3ee'],
            ['#f59e0b', '#ef4444'],
            ['#22c55e', '#14b8a6'],
            ['#ec4899', '#f97316'],
        ];

        $rooms = ['Fachada', 'Sala', 'Cocina'];

        foreach ($rooms as $index => $room) {
            [$from, $to] = $palettes[($property->id + $index) % count($palettes)];

            $svg = <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600">
              <defs>
                <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
                  <stop offset="0%" stop-color="{$from}"/>
                  <stop offset="100%" stop-color="{$to}"/>
                </linearGradient>
              </defs>
              <rect width="800" height="600" fill="url(#g)"/>
              <text x="400" y="290" font-family="system-ui,sans-serif" font-size="44" font-weight="700"
                    fill="#ffffff" text-anchor="middle">{$property->code}</text>
              <text x="400" y="345" font-family="system-ui,sans-serif" font-size="28"
                    fill="#ffffff" fill-opacity="0.85" text-anchor="middle">{$room}</text>
            </svg>
            SVG;

            $path = "propiedades/{$property->code}-{$index}.svg";
            Storage::disk('public')->put($path, $svg);

            $property->images()->create([
                'path' => $path,
                'caption' => $room,
                'sort_order' => $index,
                'is_cover' => $index === 0,
            ]);
        }
    }

    protected function definitions(): array
    {
        return [
            [
                'title' => 'Departamento moderno en González Suárez',
                'description' => 'Departamento de 3 dormitorios con vista al valle, acabados de primera, cocina equipada y dos parqueaderos. Edificio con seguridad 24 horas y áreas comunales renovadas.',
                'type' => PropertyType::Apartamento, 'operation' => PropertyOperation::Venta, 'status' => PropertyStatus::Disponible,
                'price' => 189000, 'maintenance_fee' => 120,
                'bedrooms' => 3, 'bathrooms' => 2, 'parking_spaces' => 2, 'area_built' => 142, 'year_built' => 2019, 'floor' => '7',
                'address' => 'Av. González Suárez N27-142', 'neighborhood' => 'González Suárez', 'city' => 'Quito', 'state' => 'Pichincha',
                'latitude' => -0.2027, 'longitude' => -78.4863, 'published' => true, 'featured' => true,
            ],
            [
                'title' => 'Casa familiar con jardín en Cumbayá',
                'description' => 'Casa independiente de dos plantas en urbanización privada. Amplio jardín, sala de estar con chimenea, cuarto de servicio y bodega. Cerca de colegios y centros comerciales.',
                'type' => PropertyType::Casa, 'operation' => PropertyOperation::Venta, 'status' => PropertyStatus::Disponible,
                'price' => 295000,
                'bedrooms' => 4, 'bathrooms' => 3, 'parking_spaces' => 2, 'area_built' => 260, 'area_lot' => 420, 'year_built' => 2015,
                'address' => 'Urbanización La Primavera, casa 12', 'neighborhood' => 'La Primavera', 'city' => 'Cumbayá', 'state' => 'Pichincha',
                'latitude' => -0.2050, 'longitude' => -78.4300, 'published' => true, 'featured' => true,
            ],
            [
                'title' => 'Suite amoblada en La Carolina',
                'description' => 'Suite completamente amoblada lista para habitar, ideal para ejecutivos. Incluye alícuota, internet y acceso a gimnasio del edificio.',
                'type' => PropertyType::Apartamento, 'operation' => PropertyOperation::Alquiler, 'status' => PropertyStatus::Disponible,
                'rent_price' => 650, 'maintenance_fee' => 80,
                'bedrooms' => 1, 'bathrooms' => 1, 'parking_spaces' => 1, 'area_built' => 58, 'year_built' => 2021, 'floor' => '11',
                'address' => 'Av. Amazonas y Naciones Unidas', 'neighborhood' => 'La Carolina', 'city' => 'Quito', 'state' => 'Pichincha',
                'latitude' => -0.1780, 'longitude' => -78.4870, 'published' => true, 'featured' => true,
            ],
            [
                'title' => 'Local comercial en avenida principal',
                'description' => 'Local en planta baja con vitrina a la avenida, alto flujo peatonal. Cuenta con baño, bodega interna y medio piso adicional.',
                'type' => PropertyType::Local, 'operation' => PropertyOperation::Venta, 'status' => PropertyStatus::Disponible,
                'price' => 165000,
                'bathrooms' => 1, 'area_built' => 95, 'year_built' => 2010,
                'address' => 'Av. 6 de Diciembre N34-210', 'neighborhood' => 'Iñaquito', 'city' => 'Quito', 'state' => 'Pichincha',
                'latitude' => -0.1750, 'longitude' => -78.4820, 'published' => true,
            ],
            [
                'title' => 'Terreno urbanizable en Tumbaco',
                'description' => 'Terreno plano con todos los servicios básicos, apto para proyecto de vivienda. Escrituras al día y sin gravámenes.',
                'type' => PropertyType::Terreno, 'operation' => PropertyOperation::Venta, 'status' => PropertyStatus::Disponible,
                'price' => 98000,
                'area_lot' => 780,
                'address' => 'Vía a Churoloma km 2', 'neighborhood' => 'Churoloma', 'city' => 'Tumbaco', 'state' => 'Pichincha',
                'latitude' => -0.2100, 'longitude' => -78.4000, 'published' => true,
            ],
            [
                'title' => 'Oficina implementada en Iñaquito',
                'description' => 'Oficina con divisiones de vidrio, sala de reuniones y recepción. Edificio corporativo con parqueadero de visitas.',
                'type' => PropertyType::Oficina, 'operation' => PropertyOperation::Alquiler, 'status' => PropertyStatus::Disponible,
                'rent_price' => 1200, 'maintenance_fee' => 200,
                'bathrooms' => 2, 'parking_spaces' => 3, 'area_built' => 130, 'year_built' => 2017, 'floor' => '5',
                'address' => 'Av. República del Salvador N36-84', 'neighborhood' => 'Iñaquito', 'city' => 'Quito', 'state' => 'Pichincha',
                'latitude' => -0.1790, 'longitude' => -78.4790, 'published' => true,
            ],
            [
                'title' => 'Departamento con terraza en Bellavista',
                'description' => 'Penthouse de dos dormitorios con terraza privada de 40 m² y vista panorámica de la ciudad.',
                'type' => PropertyType::Apartamento, 'operation' => PropertyOperation::Venta, 'status' => PropertyStatus::Reservado,
                'price' => 172000, 'maintenance_fee' => 95,
                'bedrooms' => 2, 'bathrooms' => 2, 'parking_spaces' => 1, 'area_built' => 118, 'year_built' => 2020, 'floor' => '9',
                'address' => 'Calle Los Cipreses E10-40', 'neighborhood' => 'Bellavista', 'city' => 'Quito', 'state' => 'Pichincha',
                'latitude' => -0.1900, 'longitude' => -78.4700, 'published' => true,
            ],
            [
                'title' => 'Casa de campo en Puembo',
                'description' => 'Casa de una planta rodeada de jardines, con piscina y área de BBQ. Clima cálido todo el año.',
                'type' => PropertyType::Casa, 'operation' => PropertyOperation::Venta, 'status' => PropertyStatus::Vendido,
                'price' => 340000,
                'bedrooms' => 4, 'bathrooms' => 4, 'parking_spaces' => 3, 'area_built' => 310, 'area_lot' => 1200, 'year_built' => 2012,
                'address' => 'Camino a Chiche s/n', 'neighborhood' => 'Puembo Centro', 'city' => 'Puembo', 'state' => 'Pichincha',
                'latitude' => -0.1830, 'longitude' => -78.3500, 'published' => false,
            ],
            [
                'title' => 'Departamento económico en El Inca',
                'description' => 'Departamento de dos dormitorios, ideal para primera vivienda. Cerca de transporte público y mercados.',
                'type' => PropertyType::Apartamento, 'operation' => PropertyOperation::Ambos, 'status' => PropertyStatus::Disponible,
                'price' => 86000, 'rent_price' => 420, 'maintenance_fee' => 45,
                'bedrooms' => 2, 'bathrooms' => 1, 'parking_spaces' => 1, 'area_built' => 72, 'year_built' => 2014, 'floor' => '3',
                'address' => 'Calle Isla Floreana N44-12', 'neighborhood' => 'El Inca', 'city' => 'Quito', 'state' => 'Pichincha',
                'latitude' => -0.1580, 'longitude' => -78.4770, 'published' => true,
            ],
            [
                'title' => 'Bodega industrial en Calderón',
                'description' => 'Bodega con altura libre de 8 metros, oficinas internas y patio de maniobras para camiones.',
                'type' => PropertyType::Bodega, 'operation' => PropertyOperation::Alquiler, 'status' => PropertyStatus::Alquilado,
                'rent_price' => 2800,
                'bathrooms' => 2, 'area_built' => 640, 'area_lot' => 900, 'year_built' => 2016,
                'address' => 'Panamericana Norte km 12', 'neighborhood' => 'Carapungo', 'city' => 'Quito', 'state' => 'Pichincha',
                'latitude' => -0.0950, 'longitude' => -78.4200, 'published' => false,
            ],
            [
                'title' => 'Departamento nuevo en Quito Tenis',
                'description' => 'Estreno en edificio boutique de 12 unidades. Acabados premium, cocina italiana y bodega incluida.',
                'type' => PropertyType::Apartamento, 'operation' => PropertyOperation::Venta, 'status' => PropertyStatus::Disponible,
                'price' => 215000, 'maintenance_fee' => 130,
                'bedrooms' => 3, 'bathrooms' => 3, 'parking_spaces' => 2, 'area_built' => 155, 'year_built' => 2025, 'floor' => '4',
                'address' => 'Calle Julio Zaldumbide y Av. Brasil', 'neighborhood' => 'Quito Tenis', 'city' => 'Quito', 'state' => 'Pichincha',
                'latitude' => -0.1720, 'longitude' => -78.4950, 'published' => true, 'featured' => true,
            ],
            [
                'title' => 'Casa adosada en Tumbaco',
                'description' => 'Conjunto cerrado con áreas verdes, casa de tres dormitorios y patio posterior. Ideal para familias jóvenes.',
                'type' => PropertyType::Casa, 'operation' => PropertyOperation::Venta, 'status' => PropertyStatus::Disponible,
                'price' => 198000,
                'bedrooms' => 3, 'bathrooms' => 3, 'parking_spaces' => 2, 'area_built' => 175, 'area_lot' => 210, 'year_built' => 2018,
                'address' => 'Conjunto Los Nogales, casa 7', 'neighborhood' => 'Collaloma', 'city' => 'Tumbaco', 'state' => 'Pichincha',
                'latitude' => -0.2150, 'longitude' => -78.4050, 'published' => true,
            ],
        ];
    }
}
