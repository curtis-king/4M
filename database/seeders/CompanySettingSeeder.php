<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        CompanySetting::create([
            'name' => 'Laboratoire 4M',
            'niu' => '1234567890',
            'address' => 'Brazzaville, République du Congo',
            'phone' => '+242 06 123 4567',
            'email' => 'contact@labo4m.cg',
            'nif' => 'NIF123456',
            'rc' => 'RC789012',
            'patente' => 'PAT345678',
            'cnss' => 'CNSS112233',
            'bank_name' => 'BGFI Bank Congo',
            'bank_rib' => 'CG12 3456 7890 1234 5678 9012 345',
            'sfec_environment' => 'sandbox',
        ]);
    }
}
