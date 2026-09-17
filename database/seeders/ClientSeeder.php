<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\InsuranceContract;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        // Assureurs
        $sgam = Client::create([
            'type' => 'assureur',
            'recipient_type' => 'business',
            'name' => 'SGAM',
            'address' => 'Avenue de la Paix, Brazzaville',
            'phone' => '+242 05 555 0001',
            'email' => 'contact@sgam.cg',
            'contact_name' => 'M. Dupont',
            'niu' => '0000000000000001',
            'rccm' => 'RC-SGAM-0001',
            'is_taxable' => true,
            'discount_rate' => 15.00,
        ]);

        $nassima = Client::create([
            'type' => 'assureur',
            'recipient_type' => 'business',
            'name' => 'NASSIMA',
            'address' => 'Boulevard du Général de Gaulle, Brazzaville',
            'phone' => '+242 05 555 0002',
            'email' => 'contact@nassima.cg',
            'contact_name' => 'Mme. Koné',
            'niu' => '0000000000000002',
            'rccm' => 'RC-NASSIMA-0001',
            'is_taxable' => true,
            'discount_rate' => 20.00,
        ]);

        Client::create([
            'type' => 'assureur',
            'recipient_type' => 'business',
            'name' => 'FENALCO',
            'address' => 'Avenue des Martyrs, Brazzaville',
            'phone' => '+242 05 555 0003',
            'email' => 'contact@fenalco.cg',
            'contact_name' => 'M. Ouedraogo',
            'niu' => '0000000000000003',
            'is_taxable' => true,
            'discount_rate' => 10.00,
        ]);

        Client::create([
            'type' => 'assureur',
            'recipient_type' => 'business',
            'name' => 'NSIA',
            'address' => 'Immeuble NSIA, Plateau',
            'phone' => '+242 05 555 0004',
            'email' => 'contact@nsia.cg',
            'contact_name' => 'Mme. Traoré',
            'niu' => '0000000000000004',
            'is_taxable' => true,
            'discount_rate' => 12.00,
        ]);

        // Entreprises
        $entreMoko = Client::create([
            'type' => 'entreprise',
            'recipient_type' => 'business',
            'name' => 'Entreprise MOKO',
            'phone' => '+242 06 111 2233',
            'address' => 'Quartier OCH, Brazzaville',
            'city' => 'Brazzaville',
            'company_name' => 'SARL MOKO Industries',
            'company_nif' => 'NIF-MOKO-001',
            'company_rcs' => 'RC-MOKO-001',
            'niu' => '12345678901234',
            'rccm' => 'RCCM-001',
            'is_taxable' => true,
        ]);

        $entreTressor = Client::create([
            'type' => 'entreprise',
            'recipient_type' => 'business',
            'name' => 'Entreprise TRESSOR',
            'phone' => '+242 06 444 5566',
            'address' => 'Quartier Poto-Poto, Brazzaville',
            'city' => 'Brazzaville',
            'company_name' => 'SAS TRESSOR Services',
            'company_nif' => 'NIF-TRESSOR-002',
            'company_rcs' => 'RC-TRESSOR-002',
            'niu' => '98765432101234',
            'rccm' => 'RCCM-002',
            'is_taxable' => true,
        ]);

        // Particuliers
        Client::create([
            'type' => 'particulier',
            'recipient_type' => 'individual',
            'name' => 'Ngoma Pierre',
            'phone' => '+242 06 777 8899',
            'address' => 'Quartier Bacongo, Brazzaville',
            'city' => 'Brazzaville',
            'is_taxable' => false,
        ]);

        Client::create([
            'type' => 'particulier',
            'recipient_type' => 'individual',
            'name' => 'Luyeye Marie',
            'phone' => '+242 05 123 4567',
            'city' => 'Pointe-Noire',
            'is_taxable' => false,
        ]);

        // Particuliers assurés (couverts par un contrat d'assurance souscrit auprès d'une compagnie)
        $assure1 = Client::create([
            'type' => 'particulier',
            'recipient_type' => 'individual',
            'name' => 'Kabongo Jacques',
            'phone' => '+242 06 999 0011',
            'address' => 'Quartier Moungali, Brazzaville',
            'city' => 'Brazzaville',
            'niu' => '11223344556677',
            'is_taxable' => false,
            'contact_name' => 'SGAM - Service Sinistres',
            'contact_phone' => '+242 05 555 1010',
        ]);

        $assure2 = Client::create([
            'type' => 'particulier',
            'recipient_type' => 'individual',
            'name' => 'Bolongo Sarah',
            'phone' => '+242 05 222 3344',
            'city' => 'Brazzaville',
            'niu' => '77665544332211',
            'is_taxable' => false,
            'contact_name' => 'NASSIMA - Direction Clients',
            'contact_phone' => '+242 05 666 2020',
        ]);

        // Contrats d'assurance
        InsuranceContract::create([
            'client_id' => $assure1->id,
            'insurer_id' => $sgam->id,
            'contract_number' => 'CTR-2026-0001',
            'coverage_rate' => 80.00,
            'start_date' => '2026-01-01',
            'is_active' => true,
        ]);

        InsuranceContract::create([
            'client_id' => $assure2->id,
            'insurer_id' => $nassima->id,
            'contract_number' => 'CTR-2026-0002',
            'coverage_rate' => 70.00,
            'start_date' => '2026-03-01',
            'is_active' => true,
        ]);

        // Contrats d'entreprise (le contrat est souscrit par la société auprès de l'assureur)
        InsuranceContract::create([
            'client_id' => $entreMoko->id,
            'insurer_id' => $sgam->id,
            'contract_number' => 'CTR-2026-0003',
            'coverage_rate' => 80.00,
            'start_date' => '2026-01-01',
            'is_active' => true,
        ]);

        InsuranceContract::create([
            'client_id' => $entreTressor->id,
            'insurer_id' => $nassima->id,
            'contract_number' => 'CTR-2026-0004',
            'coverage_rate' => 70.00,
            'start_date' => '2026-01-01',
            'is_active' => true,
        ]);

        // Agents pour entreprises
        \App\Models\Agent::create(['client_id' => $entreMoko->id, 'name' => 'MOKO Jean', 'matricule' => 'MOKO-001', 'phone' => '+242 06 111 2201']);
        \App\Models\Agent::create(['client_id' => $entreMoko->id, 'name' => 'MOKO Alice', 'matricule' => 'MOKO-002', 'phone' => '+242 06 111 2202']);
        \App\Models\Agent::create(['client_id' => $entreMoko->id, 'name' => 'MOKO Robert', 'matricule' => 'MOKO-003', 'phone' => '+242 06 111 2203']);

        \App\Models\Agent::create(['client_id' => $entreTressor->id, 'name' => 'TRESSOR Paul', 'matricule' => 'TRES-001', 'phone' => '+242 06 444 5501']);
        \App\Models\Agent::create(['client_id' => $entreTressor->id, 'name' => 'TRESSOR Claire', 'matricule' => 'TRES-002', 'phone' => '+242 06 444 5502']);
    }
}
