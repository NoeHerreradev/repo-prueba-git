<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Enums\ContractType;
use App\Enums\LeadSource;
use App\Enums\LeadStage;
use App\Enums\PropertyStatus;
use App\Models\Contact;
use App\Models\Contract;
use App\Models\Lead;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadCaptureTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_a_portal_inquiry_creates_a_contact_and_a_lead(): void
    {
        $property = Property::published()->firstOrFail();

        $this->post('/consulta', [
            'name' => 'Camila Rosero',
            'email' => 'camila.rosero@example.test',
            'message' => 'Quiero agendar una visita.',
            'property_id' => $property->id,
        ])->assertRedirect();

        $contact = Contact::where('email', 'camila.rosero@example.test')->firstOrFail();
        $lead = Lead::where('contact_id', $contact->id)->firstOrFail();

        $this->assertSame('Camila', $contact->first_name);
        $this->assertSame('Rosero', $contact->last_name);
        $this->assertSame(LeadSource::Web, $lead->source);
        $this->assertSame(LeadStage::Nuevo, $lead->stage);
        $this->assertSame($property->id, $lead->property_id);
        // El lead se asigna al agente captador del inmueble.
        $this->assertSame($property->agent_id, $lead->assigned_agent_id);
        $this->assertSame(1, $lead->activities()->count());
    }

    public function test_a_repeat_inquiry_reuses_the_existing_contact(): void
    {
        $property = Property::published()->firstOrFail();
        $payload = ['name' => 'Camila Rosero', 'email' => 'camila.rosero@example.test'];

        $this->post('/consulta', $payload + ['property_id' => $property->id]);
        $this->post('/consulta', $payload);

        $this->assertSame(1, Contact::where('email', 'camila.rosero@example.test')->count());
        $this->assertSame(2, Lead::whereRelation('contact', 'email', 'camila.rosero@example.test')->count());
    }

    public function test_an_inquiry_needs_an_email_or_a_phone(): void
    {
        $this->post('/consulta', ['name' => 'Sin contacto'])
            ->assertSessionHasErrors(['email', 'phone']);
    }

    public function test_the_honeypot_field_blocks_bots(): void
    {
        $this->post('/consulta', [
            'name' => 'Bot',
            'email' => 'bot@example.test',
            'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, Contact::where('email', 'bot@example.test')->count());
    }

    public function test_activating_a_sale_contract_updates_the_property_and_the_lead(): void
    {
        $lead = Lead::open()->whereNotNull('property_id')->firstOrFail();
        $property = $lead->property;

        $contract = Contract::create([
            'type' => ContractType::Venta,
            'property_id' => $property->id,
            'lead_id' => $lead->id,
            'client_contact_id' => $lead->contact_id,
            'owner_contact_id' => $property->owner_id,
            'agent_id' => $lead->assigned_agent_id,
            'amount' => 200000,
            'commission_percent' => 3,
            'status' => ContractStatus::Activo,
        ]);

        $this->assertSame(6000.0, (float) $contract->commission_amount);
        $this->assertSame(PropertyStatus::Vendido, $property->fresh()->status);
        $this->assertSame(LeadStage::Ganado, $lead->fresh()->stage);
        $this->assertSame(6000.0, (float) $contract->commissions()->sum('amount'));
    }

    public function test_a_rental_commission_is_based_on_the_annual_rent(): void
    {
        $property = Property::whereNotNull('rent_price')->firstOrFail();

        $contract = Contract::create([
            'type' => ContractType::Alquiler,
            'property_id' => $property->id,
            'client_contact_id' => Contact::first()->id,
            'agent_id' => $property->agent_id,
            'monthly_rent' => 1000,
            'commission_percent' => 8,
            'status' => ContractStatus::Borrador,
        ]);

        // 1000 × 12 meses × 8 %
        $this->assertSame(960.0, (float) $contract->commission_amount);
    }

    public function test_changing_a_lead_stage_is_recorded_in_its_history(): void
    {
        $lead = Lead::open()->firstOrFail();
        $before = $lead->stageHistories()->count();

        $lead->update(['stage' => LeadStage::Oferta]);

        $this->assertSame($before + 1, $lead->stageHistories()->count());
        $this->assertSame(LeadStage::Oferta, $lead->stageHistories()->latest('id')->first()->to_stage);
    }
}
