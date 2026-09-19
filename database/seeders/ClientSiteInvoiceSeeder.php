<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientSite;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSiteInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $entreMoko = Client::where('name', 'Entreprise MOKO')->first();

        if (! $admin || ! $entreMoko) {
            return;
        }

        $siegeSocial = ClientSite::create([
            'client_id' => $entreMoko->id,
            'name' => 'Siège social',
            'city' => 'Brazzaville',
            'sort_order' => 0,
        ]);

        ClientSite::create([
            'client_id' => $entreMoko->id,
            'name' => 'Usine de production',
            'city' => 'Pointe-Noire',
            'sort_order' => 1,
        ]);

        ClientSite::create([
            'client_id' => $entreMoko->id,
            'name' => 'Entrepôt logistique',
            'city' => 'Dolisie',
            'sort_order' => 2,
        ]);

        $service = Service::where('code', 'BIO001')->first() ?? Service::first();

        $invoice = Invoice::create([
            'client_id' => $entreMoko->id,
            'recipient_type' => 'business',
            'invoice_type' => 'standard',
            'status' => 'brouillon',
            'currency' => 'XAF',
            'tax_rate' => 18,
            'discount_type' => 'aucun',
            'discount_value' => 0,
            'date' => now(),
            'due_date' => now()->addDays(30),
            'created_by' => $admin->id,
            'subject' => 'Contrôle sanitaire annuel du personnel',
            'sample_nature' => 'Sang, urine',
            'company_site' => $siegeSocial->name,
            'site_id' => $siegeSocial->id,
        ]);

        $invoice->items()->create([
            'service_id' => $service->id,
            'description' => $service->name,
            'type' => 'analyse',
            'quantity' => 5,
            'unit_price' => $service->price,
            'discount_type' => 'aucun',
            'discount_value' => 0,
        ]);

        $invoice->recalculate();
    }
}
