<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Client;
use App\Models\InsuranceContract;
use App\Models\Service;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class VisitSeeder extends Seeder
{
    public function run(): void
    {
        $entreMoko = Client::where('name', 'Entreprise MOKO')->first();
        $entreTressor = Client::where('name', 'Entreprise TRESSOR')->first();
        $kabongo = Client::where('name', 'Kabongo Jacques')->first();

        $contractMoko = InsuranceContract::where('contract_number', 'CTR-2026-0003')->first();
        $contractTressor = InsuranceContract::where('contract_number', 'CTR-2026-0004')->first();
        $contractKabongo = InsuranceContract::where('contract_number', 'CTR-2026-0001')->first();

        $agentMoko1 = Agent::where('matricule', 'MOKO-001')->first();
        $agentMoko2 = Agent::where('matricule', 'MOKO-002')->first();
        $agentTressor1 = Agent::where('matricule', 'TRES-001')->first();

        $nfs = Service::where('code', 'HEM001')->first();
        $glycemie = Service::where('code', 'BIO001')->first();
        $cholesterol = Service::where('code', 'BIO002')->first();
        $paludisme = Service::where('code', 'PAR001')->first();
        $vih = Service::where('code', 'MIC008')->first();
        $echographie = Service::where('code', 'IMG002')->first();

        $day = now();

        $offset = fn (int $d) => min($d, max(0, now()->day - 1));

        // Visite 1 : MOKO / SGAM — réalisée, ticket modérateur payé
        $v1 = Visit::create([
            'client_id' => $entreMoko->id,
            'agent_id' => $agentMoko1->id,
            'objet' => 'Bilan biologique annuel',
            'insurance_contract_id' => $contractMoko->id,
            'visit_date' => $day->copy()->startOfMonth()->addDays($offset(10))->toDateString(),
            'status' => 'realisee',
        ]);
        $this->attachLines($v1, [
            [$nfs->id, 1, $nfs->price, 0],
            [$glycemie->id, 1, $glycemie->price, 0],
            [$cholesterol->id, 1, $cholesterol->price, 5],
        ]);
        $v1->recalculate();
        $v1->update(['patient_paid' => $v1->patient_amount]);

        // Visite collective : le bilan annuel couvre MOKO-001 et MOKO-002
        $v1->agents()->sync([$agentMoko1->id, $agentMoko2->id]);

        // Visite 2 : MOKO / SGAM — réalisée
        $v2 = Visit::create([
            'client_id' => $entreMoko->id,
            'agent_id' => $agentMoko2->id,
            'objet' => 'Contrôle paludisme',
            'insurance_contract_id' => $contractMoko->id,
            'visit_date' => $day->copy()->startOfMonth()->addDays($offset(5))->toDateString(),
            'status' => 'realisee',
        ]);
        $this->attachLines($v2, [
            [$nfs->id, 1, $nfs->price, 0],
            [$paludisme->id, 1, $paludisme->price, 0],
        ]);
        $v2->recalculate();

        // Visite 3 : TRESSOR / NASSIMA — réalisée (sans agent, l'assuré est la société)
        $v3 = Visit::create([
            'client_id' => $entreTressor->id,
            'agent_id' => $agentTressor1->id,
            'objet' => 'Visite médicale du personnel',
            'insurance_contract_id' => $contractTressor->id,
            'visit_date' => $day->copy()->startOfMonth()->addDays($offset(3))->toDateString(),
            'status' => 'realisee',
        ]);
        $this->attachLines($v3, [
            [$glycemie->id, 1, $glycemie->price, 0],
            [$echographie->id, 1, $echographie->price, 0],
        ]);
        $v3->recalculate();

        // Visite 4 : assuré individuel Kabongo / SGAM — réalisée
        $v4 = Visit::create([
            'client_id' => $kabongo->id,
            'objet' => 'Suivi diabète',
            'insurance_contract_id' => $contractKabongo->id,
            'visit_date' => $day->copy()->startOfMonth()->addDays($offset(6))->toDateString(),
            'status' => 'realisee',
        ]);
        $this->attachLines($v4, [
            [$glycemie->id, 1, $glycemie->price, 0],
            [$vih->id, 1, $vih->price, 0],
        ]);
        $v4->recalculate();

        // Visites futures / non facturables
        Visit::create(['client_id' => $entreTressor->id, 'agent_id' => $agentTressor1->id, 'visit_date' => $day->copy()->addDays(3)->toDateString(), 'status' => 'planifiee']);
        Visit::create(['client_id' => $entreMoko->id, 'agent_id' => $agentMoko2->id, 'visit_date' => $day->copy()->addDays(7)->toDateString(), 'status' => 'planifiee']);
        Visit::create(['client_id' => $entreTressor->id, 'agent_id' => $agentTressor1->id, 'visit_date' => $day->copy()->subDays(2)->toDateString(), 'status' => 'absent']);
    }

    protected function attachLines(Visit $visit, array $lines): void
    {
        foreach ($lines as [$serviceId, $quantity, $unitPrice, $discountPct]) {
            $visit->examLines()->create([
                'service_id' => $serviceId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_type' => $discountPct > 0 ? 'pourcentage' : 'aucun',
                'discount_value' => $discountPct,
                'item_type' => 'service',
            ]);
        }
    }
}