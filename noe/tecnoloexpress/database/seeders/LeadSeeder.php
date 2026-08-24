<?php

namespace Database\Seeders;

use App\Enums\ActivityType;
use App\Enums\ContactType;
use App\Enums\LeadSource;
use App\Enums\LeadStage;
use App\Enums\PropertyStatus;
use App\Enums\TaskPriority;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Property;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        if (Lead::exists()) {
            return;
        }

        $buyers = Contact::where('type', '!=', ContactType::Propietario)->get();
        $properties = Property::whereIn('status', [
            PropertyStatus::Disponible,
            PropertyStatus::Reservado,
        ])->get();

        // Distribución realista: muchos leads arriba del embudo, pocos abajo.
        $distribution = [
            [LeadStage::Nuevo, 6],
            [LeadStage::Contactado, 5],
            [LeadStage::Calificado, 4],
            [LeadStage::Visita, 3],
            [LeadStage::Oferta, 2],
            [LeadStage::Negociacion, 2],
            [LeadStage::Ganado, 3],
            [LeadStage::Perdido, 3],
        ];

        $sources = LeadSource::cases();
        $i = 0;

        foreach ($distribution as [$stage, $count]) {
            for ($n = 0; $n < $count; $n++, $i++) {
                $contact = $buyers[$i % $buyers->count()];
                $property = $i % 5 === 4 ? null : $properties[$i % $properties->count()];
                $createdAt = now()->subDays(random_int(3, 120));
                $stageChangedAt = $createdAt->copy()->addDays(random_int(0, 20));

                $lead = new Lead([
                    'title' => $property
                        ? "Interés en {$property->title}"
                        : 'Consulta general de '.$contact->full_name,
                    'contact_id' => $contact->id,
                    'property_id' => $property?->id,
                    'assigned_agent_id' => $contact->assigned_agent_id,
                    'source' => $sources[$i % count($sources)],
                    'stage' => $stage,
                    'expected_value' => $property?->price ?? random_int(80, 250) * 1000,
                    'probability' => $stage->defaultProbability(),
                    'expected_close_date' => $createdAt->copy()->addDays(random_int(30, 90)),
                    'lost_reason' => $stage === LeadStage::Perdido
                        ? collect(['Precio fuera de presupuesto', 'Compró con otra agencia', 'Dejó de responder'])->random()
                        : null,
                    'stage_changed_at' => $stageChangedAt->isFuture() ? now() : $stageChangedAt,
                ]);

                $lead->created_at = $createdAt;
                $lead->updated_at = $createdAt;
                $lead->save();

                $this->seedActivities($lead);
                $this->seedTasks($lead);
            }
        }
    }

    protected function seedActivities(Lead $lead): void
    {
        $templates = [
            [ActivityType::Llamada, 'Primer contacto telefónico', 'Se presentó la agencia y se confirmó el interés.'],
            [ActivityType::Whatsapp, 'Envío de ficha del inmueble', 'Se compartieron fotos y precio por WhatsApp.'],
            [ActivityType::Email, 'Envío de propuesta', 'Se remitió la propuesta comercial con condiciones de pago.'],
            [ActivityType::Reunion, 'Reunión en oficina', 'Revisión de opciones de financiamiento.'],
        ];

        $count = match (true) {
            $lead->stage === LeadStage::Nuevo => 0,
            in_array($lead->stage, [LeadStage::Contactado, LeadStage::Calificado], true) => 1,
            default => random_int(2, 4),
        };

        for ($i = 0; $i < $count; $i++) {
            [$type, $subject, $notes] = $templates[$i % count($templates)];

            $lead->activities()->create([
                'type' => $type,
                'subject' => $subject,
                'notes' => $notes,
                'user_id' => $lead->assigned_agent_id,
                'occurred_at' => $lead->created_at->copy()->addDays($i * 3 + 1),
            ]);
        }
    }

    protected function seedTasks(Lead $lead): void
    {
        if (! $lead->stage->isOpen() || random_int(1, 3) === 1) {
            return;
        }

        $lead->tasks()->create([
            'title' => match ($lead->stage) {
                LeadStage::Nuevo => 'Llamar para calificar el lead',
                LeadStage::Contactado => 'Enviar opciones que encajen con su presupuesto',
                LeadStage::Calificado => 'Agendar visita al inmueble',
                LeadStage::Visita => 'Pedir retroalimentación de la visita',
                LeadStage::Oferta => 'Dar seguimiento a la oferta enviada',
                default => 'Cerrar condiciones con el propietario',
            },
            'due_at' => now()->addDays(random_int(-5, 10))->setTime(random_int(9, 17), 0),
            'priority' => collect(TaskPriority::cases())->random(),
            'user_id' => $lead->assigned_agent_id,
        ]);
    }
}
