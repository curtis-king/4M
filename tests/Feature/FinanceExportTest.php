<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Insurer;
use App\Models\InsuranceContract;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\User;
use App\Services\Finance\AccountExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    protected function buildClient(): Client
    {
        return Client::create([
            'type' => 'particulier',
            'recipient_type' => 'business',
            'name' => 'Client Test Sage',
            'email' => 'sage@test.local',
            'phone' => '+242 000',
            'city' => 'Brazzaville',
        ]);
    }

    protected function buildInvoice(Client $client, float $unitPrice = 10000, int $qty = 2): Invoice
    {
        $invoice = Invoice::create([
            'client_id' => $client->id,
            'date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'envoyee',
            'recipient_type' => 'business',
            'currency' => 'XAF',
            'tax_rate' => 0,
            'discount_type' => 'aucun',
            'discount_value' => 0,
            'created_by' => $this->user->id,
        ]);

        $invoice->items()->create([
            'service_id' => null,
            'description' => 'Analyse de laboratoire',
            'type' => 'analyse',
            'quantity' => $qty,
            'unit_price' => $unitPrice,
        ]);

        $invoice->refresh()->load('items');
        $invoice->recalculate();

        return $invoice->fresh();
    }

    public function test_sales_export_has_header_and_is_balanced(): void
    {
        $client = $this->buildClient();
        $this->buildInvoice($client, 10000, 2); // total 20000, ht 20000, tva 0

        $csv = (new AccountExportService())->exportSales(
            now()->subDay()->toDateString(),
            now()->addDay()->toDateString()
        );

        $lines = array_values(array_filter(explode(PHP_EOL, $csv)));

        $this->assertSame(
            'Journal;Date;Numéro de pièce;Compte;Libellé;Débit;Crédit;Devise',
            $lines[0]
        );

        $debit = 0.0;
        $credit = 0.0;
        foreach (array_slice($lines, 1) as $line) {
            $cols = str_getcsv($line, ';');
            $this->assertCount(8, $cols);
            $debit += (float) str_replace(',', '.', $cols[5]);
            $credit += (float) str_replace(',', '.', $cols[6]);
        }

        $this->assertEqualsWithDelta($debit, $credit, 0.01);
        $this->assertEqualsWithDelta(20000.0, $debit, 0.01);
    }

    public function test_sales_export_splits_insurance_contract(): void
    {
        Insurer::create(['name' => 'AXA Congo', 'email' => 'axa@test.local', 'phone' => '+242 111', 'city' => 'Brazzaville']);
        $insurer = Insurer::first();

        $client = Client::create([
            'type' => 'particulier',
            'recipient_type' => 'business',
            'name' => 'Assuré Test',
            'email' => 'assure@test.local',
            'phone' => '+242 222',
            'city' => 'Brazzaville',
        ]);

        $contract = InsuranceContract::create([
            'client_id' => $client->id,
            'insurer_id' => $insurer->id,
            'coverage_rate' => 40,
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'partiel',
            'recipient_type' => 'business',
            'currency' => 'XAF',
            'insurance_contract_id' => $contract->id,
            'tax_rate' => 0,
            'discount_type' => 'aucun',
            'discount_value' => 0,
            'created_by' => $this->user->id,
        ]);

        $invoice->items()->create([
            'service_id' => null,
            'description' => 'Analyse de laboratoire',
            'type' => 'analyse',
            'quantity' => 1,
            'unit_price' => 10000,
        ]);

        $invoice->refresh()->load('items');
        $invoice->recalculate();

        $this->assertEqualsWithDelta(4000.0, (float) $invoice->insurance_covered, 0.01);
        $this->assertEqualsWithDelta(6000.0, (float) $invoice->patient_amount, 0.01);

        $csv = (new AccountExportService())->exportSales(
            now()->subDay()->toDateString(),
            now()->addDay()->toDateString()
        );

        $cols = array_map(fn ($l) => str_getcsv($l, ';'), array_filter(explode(PHP_EOL, $csv), fn ($l) => $l !== ''));

        $patientLine = array_values(array_filter($cols, fn ($c) => str_contains($c[4], 'part patient')));
        $assuranceLine = array_values(array_filter($cols, fn ($c) => str_contains($c[4], 'part assureur')));

        $this->assertNotEmpty($patientLine);
        $this->assertNotEmpty($assuranceLine);
        $this->assertSame('6000,00', $patientLine[0][5]);
        $this->assertSame('4000,00', $assuranceLine[0][5]);
    }

    public function test_receipts_export_is_balanced(): void
    {
        $client = $this->buildClient();
        $invoice = $this->buildInvoice($client, 5000, 1);

        Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => 5000,
            'payment_date' => now(),
            'method' => 'especes',
            'reference' => 'REG-001',
            'payer' => 'patient',
        ]);

        $csv = (new AccountExportService())->exportReceipts(
            now()->subDay()->toDateString(),
            now()->addDay()->toDateString()
        );

        $lines = array_values(array_filter(explode(PHP_EOL, $csv)));

        $debit = 0.0;
        $credit = 0.0;
        foreach (array_slice($lines, 1) as $line) {
            $cols = str_getcsv($line, ';');
            $this->assertCount(8, $cols);
            $this->assertSame('BQ', $cols[0]);
            $debit += (float) str_replace(',', '.', $cols[5]);
            $credit += (float) str_replace(',', '.', $cols[6]);
        }

        $this->assertEqualsWithDelta(5000.0, $debit, 0.01);
        $this->assertEqualsWithDelta($debit, $credit, 0.01);
    }
}