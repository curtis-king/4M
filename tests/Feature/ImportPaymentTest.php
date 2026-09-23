<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\Import\PaymentImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    protected function buildInvoice(float $unitPrice = 10000): Invoice
    {
        $client = Client::create([
            'type' => 'particulier',
            'recipient_type' => 'business',
            'name' => 'Client Import Test',
            'email' => 'payeur@test.local',
            'phone' => '+242 333',
            'city' => 'Brazzaville',
        ]);

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
            'description' => 'Analyse',
            'type' => 'analyse',
            'quantity' => 1,
            'unit_price' => $unitPrice,
        ]);

        $invoice->refresh()->load('items');
        $invoice->recalculate();

        return $invoice->fresh();
    }

    private function labels(): array
    {
        return array_keys((new PaymentImporter())->columns());
    }

    private function row(array $assoc): array
    {
        return array_map(fn ($label) => $assoc[$label] ?? null, $this->labels());
    }

    public function test_valid_payment_marks_invoice_until_paid(): void
    {
        $invoice = $this->buildInvoice(10000);
        $importer = new PaymentImporter();

        $res = $importer->importFile(
            $this->labels(),
            [$this->row([
                'N° facture' => $invoice->number,
                'Date' => now()->format('d/m/Y'),
                'Montant' => 10000,
                'Moyen de paiement' => 'Especes',
                'Référence' => 'REG-IMP',
                'Payeur' => 'Patient',
                'Notes' => 'Test',
            ])]
        );

        $this->assertSame(1, $res->imported);
        $this->assertSame(1, $res->created);
        $this->assertEmpty($res->errors);

        $invoice->refresh();
        $this->assertEqualsWithDelta(10000.0, (float) $invoice->paid_amount, 0.01);
        $this->assertSame('payee', $invoice->status);
    }

    public function test_overpayment_is_rejected(): void
    {
        $invoice = $this->buildInvoice(10000);
        $importer = new PaymentImporter();

        $res = $importer->importFile(
            $this->labels(),
            [$this->row([
                'N° facture' => $invoice->number,
                'Date' => now()->format('d/m/Y'),
                'Montant' => 20000,
                'Moyen de paiement' => 'Especes',
                'Référence' => '',
                'Payeur' => 'Patient',
                'Notes' => '',
            ])]
        );

        $this->assertSame(0, $res->imported);
        $this->assertCount(1, $res->errors);
        $this->assertStringContainsString('supérieur au reste', $res->errors[0]['message']);
        $this->assertSame(0, Payment::count());
    }

    public function test_unknown_invoice_is_reported(): void
    {
        $importer = new PaymentImporter();

        $res = $importer->importFile(
            $this->labels(),
            [$this->row([
                'N° facture' => 'F-4M-2099-999999',
                'Date' => now()->format('d/m/Y'),
                'Montant' => 1000,
                'Moyen de paiement' => 'Virement',
                'Référence' => '',
                'Payeur' => 'Assurance',
                'Notes' => '',
            ])]
        );

        $this->assertSame(0, $res->imported);
        $this->assertStringContainsString('introuvable', $res->errors[0]['message']);
    }
}