<?php

namespace Database\Seeders;

use App\Models\Reagent;
use Illuminate\Database\Seeder;

class ReagentSeeder extends Seeder
{
    public function run(): void
    {
        $reagents = [
            ['name' => 'Réactif Glycémie (GOD-POP)', 'reference' => 'REF-GLY-001', 'unit' => 'kit', 'quantity' => 25.00, 'min_quantity' => 10.00, 'max_quantity' => 50.00],
            ['name' => 'Réactif Cholestérol', 'reference' => 'REF-CHOL-001', 'unit' => 'kit', 'quantity' => 20.00, 'min_quantity' => 8.00, 'max_quantity' => 40.00],
            ['name' => 'Colorant May-Grünwald', 'reference' => 'REF-MG-001', 'unit' => 'ml', 'quantity' => 500.00, 'min_quantity' => 200.00, 'max_quantity' => 1000.00],
            ['name' => 'Colorant Giemsa', 'reference' => 'REF-GIE-001', 'unit' => 'ml', 'quantity' => 500.00, 'min_quantity' => 200.00, 'max_quantity' => 1000.00],
            ['name' => 'TDR Paludisme (SD Bioline)', 'reference' => 'REF-TDR-001', 'unit' => 'kit', 'quantity' => 5.00, 'min_quantity' => 10.00, 'max_quantity' => 100.00],
            ['name' => 'ELISA VIH 1/2', 'reference' => 'REF-VIH-001', 'unit' => 'kit', 'quantity' => 12.00, 'min_quantity' => 5.00, 'max_quantity' => 30.00],
            ['name' => 'Milieu de culture chromogène', 'reference' => 'REF-CHROM-001', 'unit' => 'kit', 'quantity' => 8.00, 'min_quantity' => 5.00, 'max_quantity' => 20.00],
            ['name' => 'Tube EDTA', 'reference' => 'REF-EDTA-001', 'unit' => 'pièce', 'quantity' => 200.00, 'min_quantity' => 100.00, 'max_quantity' => 500.00],
            ['name' => 'Tube Citrate (TP)', 'reference' => 'REF-CIT-001', 'unit' => 'pièce', 'quantity' => 150.00, 'min_quantity' => 80.00, 'max_quantity' => 300.00],
            ['name' => 'Lame microscope', 'reference' => 'REF-LAM-001', 'unit' => 'boîte', 'quantity' => 3.00, 'min_quantity' => 5.00, 'max_quantity' => 20.00],
            ['name' => 'Gel douche ultrasound', 'reference' => 'REF-GEL-001', 'unit' => 'tube', 'quantity' => 2.00, 'min_quantity' => 3.00, 'max_quantity' => 10.00],
            ['name' => 'Réactif Créatinine', 'reference' => 'REF-CRE-001', 'unit' => 'kit', 'quantity' => 18.00, 'min_quantity' => 8.00, 'max_quantity' => 40.00],
        ];

        foreach ($reagents as $reagent) {
            Reagent::create($reagent);
        }
    }
}
