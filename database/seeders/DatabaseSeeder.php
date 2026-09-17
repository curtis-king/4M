<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CompanySettingSeeder::class,
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
            ClientSeeder::class,
            InvoiceSeeder::class,
            VisitSeeder::class,
            StatementSeeder::class,
            ReagentSeeder::class,
        ]);
    }
}
