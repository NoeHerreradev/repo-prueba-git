<?php

namespace Database\Seeders;

use App\Enums\CommissionStatus;
use App\Enums\ContractStatus;
use App\Enums\ContractType;
use App\Enums\LeadStage;
use App\Enums\PropertyOperation;
use App\Models\Contract;
use App\Models\Lead;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    public function run(): void
    {
        if (Contract::exists()) {
            return;
        }

        $wonLeads = Lead::where('stage', LeadStage::Ganado)
            ->whereNotNull('property_id')
            ->with('property')
            ->get();

        foreach ($wonLeads as $index => $lead) {
            $property = $lead->property;
            $isRental = $property->operation === PropertyOperation::Alquiler;
            $signedAt = $lead->created_at->copy()->addDays(random_int(25, 70));

            $contract = new Contract([
                'type' => $isRental ? ContractType::Alquiler : ContractType::Venta,
                'property_id' => $property->id,
                'lead_id' => $lead->id,
                'client_contact_id' => $lead->contact_id,
                'owner_contact_id' => $property->owner_id,
                'agent_id' => $lead->assigned_agent_id,
                'amount' => $isRental ? 0 : (float) $property->price,
                'monthly_rent' => $isRental ? (float) $property->rent_price : null,
                'deposit' => $isRental ? (float) $property->rent_price : round((float) $property->price * 0.1, 2),
                'currency' => $property->currency,
                'commission_percent' => (float) $property->commission_percent,
                'signed_at' => $signedAt->isFuture() ? now() : $signedAt,
                'start_date' => $signedAt->isFuture() ? now() : $signedAt,
                'end_date' => $isRental ? $signedAt->copy()->addYear() : null,
                'status' => ContractStatus::Activo,
                'notes' => 'Contrato generado desde el CRM.',
            ]);

            $contract->created_at = $signedAt->isFuture() ? now() : $signedAt;
            $contract->save();

            // Las primeras comisiones ya se liquidaron; el resto sigue pendiente.
            if ($index === 0) {
                $contract->commissions()->update([
                    'status' => CommissionStatus::Pagada,
                    'paid_at' => $contract->signed_at?->copy()->addDays(15),
                ]);
            } elseif ($index === 1) {
                $contract->commissions()->update(['status' => CommissionStatus::Aprobada]);
            }
        }

        // Un par de contratos en borrador para mostrar el flujo previo a la firma.
        $pending = Lead::whereIn('stage', [LeadStage::Negociacion, LeadStage::Oferta])
            ->whereNotNull('property_id')
            ->with('property')
            ->limit(2)
            ->get();

        foreach ($pending as $lead) {
            $property = $lead->property;

            Contract::create([
                'type' => ContractType::Reserva,
                'property_id' => $property->id,
                'lead_id' => $lead->id,
                'client_contact_id' => $lead->contact_id,
                'owner_contact_id' => $property->owner_id,
                'agent_id' => $lead->assigned_agent_id,
                'amount' => (float) ($property->price ?? 0),
                'deposit' => 2000,
                'currency' => $property->currency,
                'commission_percent' => (float) $property->commission_percent,
                'status' => ContractStatus::PendienteFirma,
                'signed_at' => null,
                'notes' => 'Pendiente de firma del propietario.',
            ]);
        }
    }
}
