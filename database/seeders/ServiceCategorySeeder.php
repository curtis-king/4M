<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Biochimie', 'description' => 'Analyses biochimiques sanguines et urinaires', 'group' => 'medical', 'sort_order' => 10],
            ['name' => 'Microbiologie', 'description' => 'Analyses bactériologiques, virologiques et parasitologiques', 'group' => 'medical', 'sort_order' => 20],
            ['name' => 'Hématologie', 'description' => 'Numération et formule sanguine, hémostase', 'group' => 'medical', 'sort_order' => 30],
            ['name' => 'Parasitologie', 'description' => 'Détection de parasites sanguins et intestinaux', 'group' => 'medical', 'sort_order' => 40],
            ['name' => 'Imagerie Médicale', 'description' => 'Radiographies, échographies, scanner', 'group' => 'medical', 'sort_order' => 50],
            ['name' => 'Microbiologie Alimentaire', 'description' => 'Contrôle microbien des aliments : Salmonella, E. coli, Listeria, levures et moisissures', 'group' => 'alimentaire', 'sort_order' => 60],
            ['name' => 'Chimie Alimentaire', 'description' => 'Contrôle chimique des aliments : pesticides, métaux lourds, nitrites, aflatoxines', 'group' => 'alimentaire', 'sort_order' => 70],
            ['name' => 'Physique Alimentaire', 'description' => 'Contrôle physique des aliments : pH, humidité, activité de l\'eau (Aw)', 'group' => 'alimentaire', 'sort_order' => 80],
            ['name' => 'Nutrition et Composition', 'description' => 'Composition nutritionnelle : protéines, lipides, glucides, valeur énergétique', 'group' => 'alimentaire', 'sort_order' => 90],
        ];

        foreach ($categories as $cat) {
            ServiceCategory::updateOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
