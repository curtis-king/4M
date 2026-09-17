<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $biochimie = ServiceCategory::where('name', 'Biochimie')->first();
        $microbio = ServiceCategory::where('name', 'Microbiologie')->first();
        $hematologie = ServiceCategory::where('name', 'Hématologie')->first();
        $parasito = ServiceCategory::where('name', 'Parasitologie')->first();
        $imagerie = ServiceCategory::where('name', 'Imagerie Médicale')->first();

        $microbioAlim = ServiceCategory::where('name', 'Microbiologie Alimentaire')->first();
        $chimieAlim = ServiceCategory::where('name', 'Chimie Alimentaire')->first();
        $physiqueAlim = ServiceCategory::where('name', 'Physique Alimentaire')->first();
        $nutrition = ServiceCategory::where('name', 'Nutrition et Composition')->first();

        $services = [
            // Biochimie
            ['category_id' => $biochimie->id, 'name' => 'Glycémie à jeun', 'code' => 'BIO001', 'classification_code' => 'C01', 'price' => 5000],
            ['category_id' => $biochimie->id, 'name' => 'Cholestérol total', 'code' => 'BIO002', 'classification_code' => 'C02', 'price' => 6000],
            ['category_id' => $biochimie->id, 'name' => 'Triglycérides', 'code' => 'BIO003', 'classification_code' => 'C03', 'price' => 6000],
            ['category_id' => $biochimie->id, 'name' => 'ASAT (TGO)', 'code' => 'BIO004', 'classification_code' => 'C04', 'price' => 5500],
            ['category_id' => $biochimie->id, 'name' => 'ALAT (TGP)', 'code' => 'BIO005', 'classification_code' => 'C05', 'price' => 5500],
            ['category_id' => $biochimie->id, 'name' => 'Créatinine', 'code' => 'BIO006', 'classification_code' => 'C06', 'price' => 5000],
            ['category_id' => $biochimie->id, 'name' => 'Urée', 'code' => 'BIO007', 'classification_code' => 'C07', 'price' => 4500],
            ['category_id' => $biochimie->id, 'name' => 'Acide urique', 'code' => 'BIO008', 'classification_code' => 'C08', 'price' => 5000],
            ['category_id' => $biochimie->id, 'name' => 'Bilirubine totale', 'code' => 'BIO009', 'classification_code' => 'C09', 'price' => 5500],
            ['category_id' => $biochimie->id, 'name' => 'TP', 'code' => 'BIO010', 'classification_code' => 'C10', 'price' => 7000],
            ['category_id' => $biochimie->id, 'name' => 'INR', 'code' => 'BIO011', 'classification_code' => 'C11', 'price' => 7500],
            ['category_id' => $biochimie->id, 'name' => 'Ferritine', 'code' => 'BIO012', 'classification_code' => 'C12', 'price' => 12000],

            // Microbiologie
            ['category_id' => $microbio->id, 'name' => 'Examen cytobactériologique des crachats', 'code' => 'MIC001', 'classification_code' => 'S01', 'price' => 10000],
            ['category_id' => $microbio->id, 'name' => 'Antibiogramme', 'code' => 'MIC002', 'classification_code' => 'S02', 'price' => 15000],
            ['category_id' => $microbio->id, 'name' => 'Culture urinaire', 'code' => 'MIC003', 'classification_code' => 'S03', 'price' => 12000],
            ['category_id' => $microbio->id, 'name' => 'Bactériologie selles', 'code' => 'MIC004', 'classification_code' => 'S04', 'price' => 10000],
            ['category_id' => $microbio->id, 'name' => 'Sérologie COVID-19', 'code' => 'MIC005', 'classification_code' => 'S05', 'price' => 20000],
            ['category_id' => $microbio->id, 'name' => 'Sérologie hépatite B', 'code' => 'MIC006', 'classification_code' => 'S06', 'price' => 18000],
            ['category_id' => $microbio->id, 'name' => 'Sérologie hépatite C', 'code' => 'MIC007', 'classification_code' => 'S07', 'price' => 18000],
            ['category_id' => $microbio->id, 'name' => 'ELISA VIH 1+2', 'code' => 'MIC008', 'classification_code' => 'S08', 'price' => 15000],

            // Hématologie
            ['category_id' => $hematologie->id, 'name' => 'NFS complète', 'code' => 'HEM001', 'classification_code' => 'H01', 'price' => 8000],
            ['category_id' => $hematologie->id, 'name' => 'Vitesse de sédimentation', 'code' => 'HEM002', 'classification_code' => 'H02', 'price' => 3000],
            ['category_id' => $hematologie->id, 'name' => 'Groupe sanguin + Rhésus', 'code' => 'HEM003', 'classification_code' => 'H03', 'price' => 5000],
            ['category_id' => $hematologie->id, 'name' => 'Hémoglobine glyquée (HbA1c)', 'code' => 'HEM004', 'classification_code' => 'H04', 'price' => 15000],
            ['category_id' => $hematologie->id, 'name' => 'Retrig', 'code' => 'HEM005', 'classification_code' => 'H05', 'price' => 7000],
            ['category_id' => $hematologie->id, 'name' => 'Fibrinogène', 'code' => 'HEM006', 'classification_code' => 'H06', 'price' => 10000],
            ['category_id' => $hematologie->id, 'name' => 'VS', 'code' => 'HEM007', 'classification_code' => 'H07', 'price' => 3000],

            // Parasitologie
            ['category_id' => $parasito->id, 'name' => 'Goutte épaisse (Paludisme)', 'code' => 'PAR001', 'classification_code' => 'P01', 'price' => 5000],
            ['category_id' => $parasito->id, 'name' => 'TDR Paludisme', 'code' => 'PAR002', 'classification_code' => 'P02', 'price' => 5000],
            ['category_id' => $parasito->id, 'name' => 'Coproparasitoscopie', 'code' => 'PAR003', 'classification_code' => 'P03', 'price' => 5000],
            ['category_id' => $parasito->id, 'name' => 'Sang dacrème (Filariose)', 'code' => 'PAR004', 'classification_code' => 'P04', 'price' => 8000],

            // Imagerie
            ['category_id' => $imagerie->id, 'name' => 'Radiographie thorax', 'code' => 'IMG001', 'classification_code' => 'I01', 'price' => 15000],
            ['category_id' => $imagerie->id, 'name' => 'Échographie abdominale', 'code' => 'IMG002', 'classification_code' => 'I02', 'price' => 25000],
            ['category_id' => $imagerie->id, 'name' => 'Échographie pelvienne', 'code' => 'IMG003', 'classification_code' => 'I03', 'price' => 25000],
            ['category_id' => $imagerie->id, 'name' => 'Échographie ostéo-articulaire', 'code' => 'IMG004', 'classification_code' => 'I04', 'price' => 20000],
            ['category_id' => $imagerie->id, 'name' => 'Scanner (TDM)', 'code' => 'IMG005', 'classification_code' => 'I05', 'price' => 80000],

            // Microbiologie Alimentaire
            ['category_id' => $microbioAlim->id, 'name' => 'Recherche Salmonella', 'code' => 'ALI001', 'classification_code' => 'F01', 'price' => 25000],
            ['category_id' => $microbioAlim->id, 'name' => 'Dénombrement E. coli', 'code' => 'ALI002', 'classification_code' => 'F02', 'price' => 20000],
            ['category_id' => $microbioAlim->id, 'name' => 'Recherche Listeria monocytogenes', 'code' => 'ALI003', 'classification_code' => 'F03', 'price' => 30000],
            ['category_id' => $microbioAlim->id, 'name' => 'Dénombrement coliformes totaux', 'code' => 'ALI004', 'classification_code' => 'F04', 'price' => 15000],
            ['category_id' => $microbioAlim->id, 'name' => 'Levures et moisissures (flore fongique)', 'code' => 'ALI005', 'classification_code' => 'F05', 'price' => 18000],
            ['category_id' => $microbioAlim->id, 'name' => 'Staphylocoques à coagulase positive', 'code' => 'ALI006', 'classification_code' => 'F06', 'price' => 20000],

            // Chimie Alimentaire
            ['category_id' => $chimieAlim->id, 'name' => 'Résidus de pesticides', 'code' => 'ALI007', 'classification_code' => 'F07', 'price' => 45000],
            ['category_id' => $chimieAlim->id, 'name' => 'Métaux lourds (plomb, cadmium, mercure)', 'code' => 'ALI008', 'classification_code' => 'F08', 'price' => 40000],
            ['category_id' => $chimieAlim->id, 'name' => 'Nitrites / nitrates', 'code' => 'ALI009', 'classification_code' => 'F09', 'price' => 20000],
            ['category_id' => $chimieAlim->id, 'name' => 'Dosage des aflatoxines', 'code' => 'ALI010', 'classification_code' => 'F10', 'price' => 35000],

            // Physique Alimentaire
            ['category_id' => $physiqueAlim->id, 'name' => 'Mesure du pH', 'code' => 'ALI011', 'classification_code' => 'F11', 'price' => 5000],
            ['category_id' => $physiqueAlim->id, 'name' => 'Taux d\'humidité', 'code' => 'ALI012', 'classification_code' => 'F12', 'price' => 8000],
            ['category_id' => $physiqueAlim->id, 'name' => 'Activité de l\'eau (Aw)', 'code' => 'ALI013', 'classification_code' => 'F13', 'price' => 10000],

            // Nutrition et Composition
            ['category_id' => $nutrition->id, 'name' => 'Protéines totales', 'code' => 'ALI014', 'classification_code' => 'F14', 'price' => 15000],
            ['category_id' => $nutrition->id, 'name' => 'Lipides (matières grasses)', 'code' => 'ALI015', 'classification_code' => 'F15', 'price' => 15000],
            ['category_id' => $nutrition->id, 'name' => 'Glucides', 'code' => 'ALI016', 'classification_code' => 'F16', 'price' => 12000],
            ['category_id' => $nutrition->id, 'name' => 'Valeur énergétique (calories)', 'code' => 'ALI017', 'classification_code' => 'F17', 'price' => 10000],
        ];

        foreach ($services as $service) {
            $code = $service['code'];

            if ($code && Service::where('code', $code)->exists()) {
                continue;
            }

            Service::create($service);
        }
    }
}
