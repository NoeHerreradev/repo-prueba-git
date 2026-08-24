<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Enums\ContactType;
use App\Enums\LeadSource;
use App\Enums\LeadStage;
use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Convierte una consulta del portal público en contacto + oportunidad.
 */
class LeadCapture
{
    /**
     * @param  array{name: string, email: ?string, phone: ?string, message: ?string}  $data
     */
    public function capture(array $data, ?Property $property = null, LeadSource $source = LeadSource::Web): Lead
    {
        return DB::transaction(function () use ($data, $property, $source) {
            $contact = $this->findOrCreateContact($data, $property, $source);
            $agentId = $property?->agent_id ?? $contact->assigned_agent_id ?? $this->nextAgentId();

            if (! $contact->assigned_agent_id) {
                $contact->update(['assigned_agent_id' => $agentId]);
            }

            $lead = Lead::create([
                'title' => $property
                    ? "Consulta web · {$property->code}"
                    : 'Consulta web general',
                'contact_id' => $contact->id,
                'property_id' => $property?->id,
                'assigned_agent_id' => $agentId,
                'source' => $source,
                'stage' => LeadStage::Nuevo,
                'expected_value' => $property?->price,
                'probability' => LeadStage::Nuevo->defaultProbability(),
                'notes' => $data['message'] ?? null,
            ]);

            $lead->activities()->create([
                'type' => ActivityType::Nota,
                'subject' => 'Consulta recibida desde el portal',
                'notes' => $data['message'] ?? 'Sin mensaje.',
                'user_id' => null,
            ]);

            return $lead;
        });
    }

    /**
     * Reutiliza el contacto si ya existe por email o teléfono, para no duplicar
     * la base de datos cuando alguien consulta por varios inmuebles.
     */
    protected function findOrCreateContact(array $data, ?Property $property, LeadSource $source): Contact
    {
        $contact = Contact::query()
            ->when(filled($data['email'] ?? null), fn ($q) => $q->orWhere('email', $data['email']))
            ->when(filled($data['phone'] ?? null), fn ($q) => $q->orWhere('phone', $data['phone']))
            ->first();

        if ($contact) {
            return $contact;
        }

        $parts = preg_split('/\s+/', trim($data['name']), 2);

        return Contact::create([
            'type' => ContactType::Comprador,
            'first_name' => $parts[0],
            'last_name' => $parts[1] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'whatsapp' => $data['phone'] ?? null,
            'source' => $source,
            'pref_operation' => $property?->operation,
            'pref_property_type' => $property?->type,
            'pref_city' => $property?->city,
        ]);
    }

    /**
     * Reparto round-robin entre agentes activos: se asigna al que menos
     * leads abiertos tenga en ese momento.
     */
    protected function nextAgentId(): ?int
    {
        return User::query()
            ->where('active', true)
            ->where('role', UserRole::Agente)
            ->withCount(['leads as open_leads_count' => fn ($q) => $q->open()])
            ->orderBy('open_leads_count')
            ->value('id');
    }
}
