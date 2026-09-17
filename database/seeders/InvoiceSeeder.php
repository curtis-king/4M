<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Agent;
use App\Models\InsuranceContract;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = \App\Models\User::where('email', 'admin@example.com')->first();

        $entreMoko = Client::where('name', 'Entreprise MOKO')->first();
        $entreTressor = Client::where('name', 'Entreprise TRESSOR')->first();
        $ngoma = Client::where('name', 'Ngoma Pierre')->first();
        $luyeye = Client::where('name', 'Luyeye Marie')->first();
        $kabongo = Client::where('name', 'Kabongo Jacques')->first();
        $bolongo = Client::where('name', 'Bolongo Sarah')->first();

        $contractKabongo = InsuranceContract::where('contract_number', 'CTR-2026-0001')->first();
        $contractBolongo = InsuranceContract::where('contract_number', 'CTR-2026-0002')->first();

        $nfs = Service::where('code', 'HEM001')->first();
        $glycemie = Service::where('code', 'BIO001')->first();
        $cholesterol = Service::where('code', 'BIO002')->first();
        $paludisme = Service::where('code', 'PAR001')->first();
        $echographie = Service::where('code', 'IMG002')->first();
        $cr = Service::where('code', 'BIO006')->first();
        $hba1c = Service::where('code', 'HEM004')->first();
        $vih = Service::where('code', 'MIC008')->first();
        $radiographie = Service::where('code', 'IMG001')->first();
        $hb = Service::where('code', 'HEM001')->first();
        $tgo = Service::where('code', 'BIO004')->first();
        $tgp = Service::where('code', 'BIO005')->first();
        $urine = Service::where('code', 'MIC003')->first();
        $stool = Service::where('code', 'MIC004')->first();
        $ts = Service::where('code', 'HEM003')->first();

        $agentMoko1 = Agent::where('matricule', 'MOKO-001')->first();
        $agentMoko2 = Agent::where('matricule', 'MOKO-002')->first();
        $agentTressor1 = Agent::where('matricule', 'TRES-001')->first();

        $invoices = [
            // Facture 1: Assuré Kabongo — payée
            ['client_id' => $kabongo->id, 'agent_id' => null, 'insurance_contract_id' => $contractKabongo->id, 'status' => 'payee', 'recipient_type' => 'individual', 'items' => [
                ['service_id' => $nfs->id, 'description' => 'NFS complète', 'quantity' => 1, 'unit_price' => 8000],
                ['service_id' => $glycemie->id, 'description' => 'Glycémie à jeun', 'quantity' => 1, 'unit_price' => 5000],
            ], 'payer' => 'assurance', 'paid' => true],

            // Facture 2: Assuré Bolongo — partielle
            ['client_id' => $bolongo->id, 'agent_id' => null, 'insurance_contract_id' => $contractBolongo->id, 'status' => 'partiel', 'recipient_type' => 'individual', 'items' => [
                ['service_id' => $cholesterol->id, 'description' => 'Cholestérol total', 'quantity' => 1, 'unit_price' => 6000],
                ['service_id' => $echographie->id, 'description' => 'Échographie abdominale', 'quantity' => 1, 'unit_price' => 25000],
            ], 'payer' => 'patient', 'paid' => false],

            // Facture 3: Entreprise MOKO — envoyée
            ['client_id' => $entreMoko->id, 'agent_id' => $agentMoko1->id, 'insurance_contract_id' => null, 'status' => 'envoyee', 'recipient_type' => 'business', 'items' => [
                ['service_id' => $nfs->id, 'description' => 'NFS complète', 'quantity' => 3, 'unit_price' => 8000],
                ['service_id' => $glycemie->id, 'description' => 'Glycémie à jeun', 'quantity' => 3, 'unit_price' => 5000],
                ['service_id' => $paludisme->id, 'description' => 'TDR Paludisme', 'quantity' => 3, 'unit_price' => 5000],
            ], 'payer' => null, 'paid' => false],

            // Facture 4: Particulier Ngoma — brouillon
            ['client_id' => $ngoma->id, 'agent_id' => null, 'insurance_contract_id' => null, 'status' => 'brouillon', 'recipient_type' => 'individual', 'items' => [
                ['service_id' => $cr->id, 'description' => 'Créatinine', 'quantity' => 1, 'unit_price' => 5000],
                ['service_id' => $cholesterol->id, 'description' => 'Cholestérol total', 'quantity' => 1, 'unit_price' => 6000],
                ['service_id' => $tgo->id, 'description' => 'ASAT (TGO)', 'quantity' => 1, 'unit_price' => 5500],
            ], 'payer' => null, 'paid' => false],

            // Facture 5: Particulier Luyeye — envoyée
            ['client_id' => $luyeye->id, 'agent_id' => null, 'insurance_contract_id' => null, 'status' => 'envoyee', 'recipient_type' => 'individual', 'items' => [
                ['service_id' => $paludisme->id, 'description' => 'TDR Paludisme', 'quantity' => 1, 'unit_price' => 5000],
                ['service_id' => $vih->id, 'description' => 'ELISA VIH 1+2', 'quantity' => 1, 'unit_price' => 15000],
            ], 'payer' => null, 'paid' => false],

            // Facture 6: Entreprise TRESSOR — payée
            ['client_id' => $entreTressor->id, 'agent_id' => $agentTressor1->id, 'insurance_contract_id' => null, 'status' => 'payee', 'recipient_type' => 'business', 'items' => [
                ['service_id' => $nfs->id, 'description' => 'NFS complète', 'quantity' => 2, 'unit_price' => 8000],
                ['service_id' => $echographie->id, 'description' => 'Échographie abdominale', 'quantity' => 2, 'unit_price' => 25000],
            ], 'payer' => null, 'paid' => true],

            // Facture 7: Assuré Kabongo — annulée
            ['client_id' => $kabongo->id, 'agent_id' => null, 'insurance_contract_id' => $contractKabongo->id, 'status' => 'annulee', 'recipient_type' => 'individual', 'items' => [
                ['service_id' => $radiographie->id, 'description' => 'Radiographie thorax', 'quantity' => 1, 'unit_price' => 15000],
            ], 'payer' => null, 'paid' => false],

            // Facture 8: Entreprise MOKO — payée
            ['client_id' => $entreMoko->id, 'agent_id' => $agentMoko2->id, 'insurance_contract_id' => null, 'status' => 'payee', 'recipient_type' => 'business', 'items' => [
                ['service_id' => $hba1c->id, 'description' => 'Hémoglobine glyquée', 'quantity' => 1, 'unit_price' => 15000],
                ['service_id' => $hba1c->id, 'description' => 'Hémoglobine glyquée', 'quantity' => 1, 'unit_price' => 15000],
            ], 'payer' => null, 'paid' => true],
        ];

        foreach ($invoices as $data) {
            $items = $data['items'];
            unset($data['items']);
            $payer = $data['payer'] ?? null;
            $shouldPay = $data['paid'] ?? false;
            unset($data['payer'], $data['paid']);

            $data['created_by'] = $admin->id;
            $data['date'] = now()->subDays(rand(0, 15));
            $data['due_date'] = now()->addDays(30);
            $invoice = Invoice::create($data);

            foreach ($items as $item) {
                $invoice->items()->create($item);
            }

            $invoice->recalculate();

            if ($shouldPay) {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->total,
                    'payment_date' => now()->subDays(rand(1, 10)),
                    'method' => 'especes',
                    'payer' => $payer ?? 'patient',
                ]);
            }
        }
    }
}
