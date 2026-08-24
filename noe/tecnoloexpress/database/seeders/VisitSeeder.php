<?php

namespace Database\Seeders;

use App\Enums\LeadStage;
use App\Enums\VisitStatus;
use App\Models\Lead;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class VisitSeeder extends Seeder
{
    public function run(): void
    {
        if (Visit::exists()) {
            return;
        }

        // Solo los leads que llegaron al menos a la etapa de visita generan visitas.
        $advanced = [LeadStage::Visita, LeadStage::Oferta, LeadStage::Negociacion, LeadStage::Ganado, LeadStage::Perdido];

        $leads = Lead::whereIn('stage', $advanced)
            ->whereNotNull('property_id')
            ->with('property')
            ->get();

        foreach ($leads as $lead) {
            Visit::create([
                'property_id' => $lead->property_id,
                'lead_id' => $lead->id,
                'contact_id' => $lead->contact_id,
                'agent_id' => $lead->assigned_agent_id,
                'scheduled_at' => $lead->created_at->copy()->addDays(random_int(3, 15))->setTime(random_int(9, 17), 0),
                'duration_minutes' => 60,
                'status' => VisitStatus::Realizada,
                'interest_rating' => $lead->stage === LeadStage::Perdido ? random_int(1, 2) : random_int(3, 5),
                'feedback' => $lead->stage === LeadStage::Perdido
                    ? 'El cliente considera que el precio está por encima del mercado.'
                    : 'Buena impresión general, consultará el financiamiento.',
            ]);
        }

        // Agenda futura: visitas de los leads que siguen abiertos.
        $upcoming = Lead::open()->whereNotNull('property_id')->inRandomOrder()->limit(6)->get();

        foreach ($upcoming as $index => $lead) {
            Visit::create([
                'property_id' => $lead->property_id,
                'lead_id' => $lead->id,
                'contact_id' => $lead->contact_id,
                'agent_id' => $lead->assigned_agent_id,
                'scheduled_at' => now()->addDays($index + 1)->setTime(random_int(9, 17), 30),
                'duration_minutes' => 45,
                'status' => $index % 2 === 0 ? VisitStatus::Confirmada : VisitStatus::Programada,
            ]);
        }
    }
}
