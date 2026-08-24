<?php

namespace Database\Seeders;

use App\Enums\ContactType;
use App\Enums\LeadSource;
use App\Enums\PropertyOperation;
use App\Enums\PropertyType;
use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $agents = User::where('role', UserRole::Agente)->pluck('id')->all();

        // Propietarios: aportan el inventario de la agencia.
        $owners = [
            ['Carmen', 'Villacís', 'carmen.villacis@example.com'],
            ['Roberto', 'Andrade', 'roberto.andrade@example.com'],
            ['Patricia', 'Zambrano', 'patricia.zambrano@example.com'],
            ['Fernando', 'Ríos', 'fernando.rios@example.com'],
            ['Gabriela', 'Moreno', 'gabriela.moreno@example.com'],
            ['Inversiones Delta S.A.', '', 'contacto@delta.example.com'],
        ];

        foreach ($owners as $i => [$first, $last, $email]) {
            Contact::updateOrCreate(['email' => $email], [
                'type' => ContactType::Propietario,
                'first_name' => $first,
                'last_name' => $last,
                'phone' => '+593 98 '.str_pad((string) (100 + $i), 3, '0', STR_PAD_LEFT).' '.random_int(1000, 9999),
                'whatsapp' => '+5939'.random_int(10000000, 99999999),
                'document_type' => 'cedula',
                'document_number' => (string) random_int(1700000000, 1799999999),
                'city' => 'Quito',
                'source' => LeadSource::Referido,
                'assigned_agent_id' => $agents[$i % count($agents)],
            ]);
        }

        // Compradores y arrendatarios con preferencias de búsqueda.
        $buyers = [
            ['Diego', 'Salinas', 'diego.salinas@example.com', ContactType::Comprador, PropertyOperation::Venta, PropertyType::Apartamento, 'Quito', 90000, 150000, 2],
            ['María José', 'Terán', 'mariajose.teran@example.com', ContactType::Comprador, PropertyOperation::Venta, PropertyType::Casa, 'Cumbayá', 200000, 350000, 3],
            ['Esteban', 'Guerrero', 'esteban.guerrero@example.com', ContactType::Arrendatario, PropertyOperation::Alquiler, PropertyType::Apartamento, 'Quito', 400, 800, 2],
            ['Sofía', 'Naranjo', 'sofia.naranjo@example.com', ContactType::Comprador, PropertyOperation::Venta, PropertyType::Apartamento, 'Quito', 70000, 120000, 1],
            ['Luis', 'Chávez', 'luis.chavez@example.com', ContactType::Inversor, PropertyOperation::Venta, PropertyType::Local, 'Quito', 100000, 400000, null],
            ['Andrea', 'Vásquez', 'andrea.vasquez@example.com', ContactType::Comprador, PropertyOperation::Venta, PropertyType::Casa, 'Tumbaco', 180000, 280000, 3],
            ['Pablo', 'Jaramillo', 'pablo.jaramillo@example.com', ContactType::Arrendatario, PropertyOperation::Alquiler, PropertyType::Oficina, 'Quito', 600, 1500, null],
            ['Daniela', 'Cevallos', 'daniela.cevallos@example.com', ContactType::Comprador, PropertyOperation::Venta, PropertyType::Apartamento, 'Cumbayá', 150000, 220000, 2],
            ['Marcelo', 'Ortiz', 'marcelo.ortiz@example.com', ContactType::Inversor, PropertyOperation::Ambos, PropertyType::Terreno, 'Tumbaco', 50000, 200000, null],
            ['Verónica', 'Espinoza', 'veronica.espinoza@example.com', ContactType::Comprador, PropertyOperation::Venta, PropertyType::Casa, 'Quito', 120000, 200000, 3],
        ];

        $sources = LeadSource::cases();

        foreach ($buyers as $i => [$first, $last, $email, $type, $operation, $propertyType, $city, $min, $max, $bedrooms]) {
            Contact::updateOrCreate(['email' => $email], [
                'type' => $type,
                'first_name' => $first,
                'last_name' => $last,
                'phone' => '+593 99 '.str_pad((string) (200 + $i), 3, '0', STR_PAD_LEFT).' '.random_int(1000, 9999),
                'whatsapp' => '+5939'.random_int(10000000, 99999999),
                'document_type' => 'cedula',
                'document_number' => (string) random_int(1700000000, 1799999999),
                'city' => $city,
                'source' => $sources[$i % count($sources)],
                'assigned_agent_id' => $agents[$i % count($agents)],
                'pref_operation' => $operation,
                'pref_property_type' => $propertyType,
                'pref_city' => $city,
                'pref_bedrooms_min' => $bedrooms,
                'budget_min' => $min,
                'budget_max' => $max,
            ]);
        }
    }
}
