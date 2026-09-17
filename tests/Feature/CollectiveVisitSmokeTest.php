<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\InsuranceContract;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Visit;
use App\Services\SfecException;
use App\Services\SfecService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CollectiveVisitSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::where('email', 'admin@example.com')->first();
    }

    public function test_views_render(): void
    {
        $this->seed();

        $visit = Visit::first();

        $this->actingAs($this->admin())
            ->get(route('visits.index'))
            ->assertOk()
            ->assertSee('MOKO Jean, MOKO Alice');

        $this->actingAs($this->admin())->get(route('visits.create'))->assertOk();
        $this->actingAs($this->admin())->get(route('visits.show', $visit))->assertOk();
        $this->actingAs($this->admin())->get(route('visits.edit', $visit))->assertOk();
        $this->actingAs($this->admin())->get(route('invoices.statement.create'))->assertOk();
    }

    public function test_collective_grouping(): void
    {
        $this->seed();

        $contractMoko = InsuranceContract::where('contract_number', 'CTR-2026-0003')->first();
        Invoice::where('is_statement', true)->where('client_id', $contractMoko->insurer_id)->delete();

        $options = app(\App\Services\StatementService::class)->options($contractMoko->insurer, $contractMoko->client_id, null);

        $groups = collect($options['agents']);
        $companyGroup = $groups->first(fn ($g) => $g['id'] === null);

        $this->assertNotNull($companyGroup, 'Collective visit must be grouped under the company.');
        $this->assertSame('Entreprise MOKO', $companyGroup['name']);
        $this->assertSame('MOKO Jean, MOKO Alice', $companyGroup['visits'][0]['assured_name']);
        $this->assertCount(1, $companyGroup['visits']);
    }

    public function test_options_all_scope(): void
    {
        $this->seed();

        $contractMoko = InsuranceContract::where('contract_number', 'CTR-2026-0003')->first();
        Invoice::where('is_statement', true)->where('client_id', $contractMoko->insurer_id)->delete();

        $options = app(\App\Services\StatementService::class)->options($contractMoko->insurer, 'all', null);

        $this->assertCount(2, $options['companies']);
        $this->assertGreaterThanOrEqual(2, $options['agents']);
        $this->assertSame('Entreprise MOKO — MOKO Alice', collect($options['agents'])->last(fn ($g) => $g['id'] === 2)['name']);
    }

    public function test_statement_store_creates_invoice(): void
    {
        $this->seed();

        $contractMoko = InsuranceContract::where('contract_number', 'CTR-2026-0003')->first();
        Invoice::where('is_statement', true)->where('client_id', $contractMoko->insurer_id)->delete();

        $visits = Visit::whereHas('insuranceContract', fn ($q) => $q->where('insurer_id', $contractMoko->insurer_id))
            ->where('status', 'realisee')
            ->whereDoesntHave('statementItems')
            ->pluck('id')
            ->all();

        $this->assertNotEmpty($visits);

        $this->actingAs($this->admin())
            ->post(route('invoices.statement.store'), [
                'insurer_id' => $contractMoko->insurer_id,
                'month' => now()->format('Y-m'),
                'visit_ids' => $visits,
                'discount_value' => 15,
            ])
            ->assertRedirect();

        $statement = Invoice::where('is_statement', true)->where('client_id', $contractMoko->insurer_id)->first();
        $this->assertNotNull($statement);
        $this->assertCount(count($visits), $statement->items);
    }

    public function test_statement_certify_requires_insurer_niu(): void
    {
        $this->seed();
        Http::fake();

        \App\Models\CompanySetting::instance()->update(['sfec_api_key_sandbox' => 'test-key']);

        $statement = Invoice::where('is_statement', true)->first();
        $statement->update(['status' => 'envoyee', 'due_date' => now()->addDays(30)]);
        $statement->payments()->create([
            'amount' => $statement->total,
            'payment_date' => now(),
            'method' => 'especes',
            'payer' => 'patient',
        ]);

        $statement->client->update(['niu' => null]);

        $this->expectException(SfecException::class);
        $this->expectExceptionMessage('NIU manquant');

        app(SfecService::class)->certify($statement->refresh());
    }

    public function test_statement_certify_sends_insurer_niu(): void
    {
        $this->seed();

        \App\Models\CompanySetting::instance()->update(['sfec_api_key_sandbox' => 'test-key']);

        Http::fake(['https://sandbox.api.sfec.gouv.cg/*' => Http::response([
            'certification_number' => 'CERT-TEST-001',
            'signature' => 'sig',
            'qr_code' => 'data:image/png;base64,AAAA',
        ], 200)]);

        $statement = Invoice::where('is_statement', true)->first();
        $statement->update(['status' => 'envoyee', 'due_date' => now()->addDays(30)]);
        $statement->payments()->create([
            'amount' => $statement->total,
            'payment_date' => now(),
            'method' => 'especes',
            'payer' => 'patient',
        ]);

        $data = app(SfecService::class)->certify($statement->refresh());

        $this->assertSame('CERT-TEST-001', $data['certification_number']);

        $sent = Http::recorded();
        $this->assertNotEmpty($sent);
        $payload = $sent[0][0]->data();
        $this->assertSame('business', $payload['recipient_type']);
        $this->assertSame('0000000000000001', $payload['recipient_niu']);
    }

    public function test_controle_alimentaire_invoice_flow(): void
    {
        $this->seed();

        $client = \App\Models\Client::where('type', 'entreprise')->first();
        $service = \App\Models\Service::whereHas('category', fn ($q) => $q->where('name', 'like', '%Alimentaire%'))->first();
        $this->assertNotNull($service);

        $this->actingAs($this->admin())->post(route('invoices.store'), [
            'client_id' => $client->id,
            'recipient_type' => 'business',
            'currency' => 'XAF',
            'tax_rate' => 18,
            'discount_type' => 'aucun',
            'discount_value' => 0,
            'invoice_type' => 'controle_alimentaire',
            'date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'items' => json_encode([[
                'service_id' => $service->id,
                'description' => $service->name,
                'type' => 'analyse',
                'quantity' => 1,
                'unit_price' => $service->price,
                'discount_type' => 'aucun',
                'discount_value' => 0,
            ]]),
        ])->assertSessionHasNoErrors()
            ->assertRedirect();

        $invoice = Invoice::latest('id')->first();
        $this->assertSame('controle_alimentaire', $invoice->invoice_type);
        $this->assertTrue($invoice->is_controle_alimentaire);
        $this->assertSame('Contrôle Alimentaire', $invoice->type_label);

        $this->actingAs($this->admin())->get(route('invoices.show', $invoice))->assertOk()->assertSee('Contrôle Alimentaire');
        $this->actingAs($this->admin())->get(route('invoices.print', $invoice))->assertOk()->assertSee('FACTURE CONTRÔLE ALIMENTAIRE');
        $this->actingAs($this->admin())->get(route('invoices.index'))->assertOk()->assertSee('Contrôle Alim.');
        $this->actingAs($this->admin())->get(route('invoices.index', ['controle_alimentaire' => 1]))->assertOk();
        $this->actingAs($this->admin())->get(route('invoices.create'))->assertOk();

        $invoice->update(['status' => 'brouillon']);
        $this->actingAs($this->admin())->get(route('invoices.edit', $invoice))->assertOk();
    }

    public function test_service_catalogue_group_and_order(): void
    {
        $this->seed();

        $categories = \App\Models\ServiceCategory::ordered()->get();
        $this->assertSame('Biochimie', $categories->first()->name);
        $this->assertSame('Nutrition et Composition', $categories->last()->name);
        $this->assertTrue($categories->firstWhere('name', 'Microbiologie Alimentaire')->is_alimentaire);
        $this->assertFalse($categories->firstWhere('name', 'Hématologie')->is_alimentaire);

        $alimService = \App\Models\Service::where('code', 'ALI001')->first();
        $medicalService = \App\Models\Service::where('code', 'BIO001')->first();
        $this->assertNotNull($alimService);
        $this->assertNotNull($medicalService);
        $this->assertTrue($alimService->is_alimentaire);
        $this->assertFalse($medicalService->is_alimentaire);

        $this->actingAs($this->admin())->get(route('services.index'))->assertOk()->assertSee('Catalogue des examens');
        $this->actingAs($this->admin())->get(route('services.index', ['group' => 'medical']))->assertOk();
        $this->actingAs($this->admin())->get(route('services.index', ['group' => 'alimentaire']))
            ->assertOk()
            ->assertSee('Visites de contrôle alimentaire récentes')
            ->assertSee('Microbiologie Alimentaire');
        $this->actingAs($this->admin())->get(route('services.create'))->assertOk();
    }

    public function test_store_with_multiple_agents(): void
    {
        $this->seed();

        $moko = \App\Models\Client::where('name', 'Entreprise MOKO')->first();
        $contract = InsuranceContract::where('contract_number', 'CTR-2026-0003')->first();
        $agents = Agent::where('client_id', $moko->id)->pluck('id')->all();

        $this->actingAs($this->admin())->post(route('visits.store'), [
            'client_id' => $moko->id,
            'insurance_contract_id' => $contract->id,
            'objet' => 'Visite collective test',
            'visit_date' => '2026-09-10',
            'status' => 'realisee',
            'agent_ids' => $agents,
            'exam_items' => [],
        ])->assertSessionHasNoErrors()
            ->assertRedirect();

        $visit = Visit::where('objet', 'Visite collective test')->first();
        $this->assertNotNull($visit);
        $this->assertCount(count($agents), $visit->agents);
        $this->assertSame($agents[0], $visit->agent_id);
    }
}
