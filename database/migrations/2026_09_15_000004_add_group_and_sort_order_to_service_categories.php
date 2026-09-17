<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->string('group', 20)->nullable()->after('description');
            $table->unsignedTinyInteger('sort_order')->default(0)->after('group');
        });

        $categories = [
            'Biochimie' => ['medical', 10],
            'Microbiologie' => ['medical', 20],
            'Hématologie' => ['medical', 30],
            'Parasitologie' => ['medical', 40],
            'Imagerie Médicale' => ['medical', 50],
            'Microbiologie Alimentaire' => ['alimentaire', 60],
            'Chimie Alimentaire' => ['alimentaire', 70],
            'Physique Alimentaire' => ['alimentaire', 80],
            'Nutrition et Composition' => ['alimentaire', 90],
        ];

        foreach ($categories as $name => [$group, $order]) {
            DB::table('service_categories')
                ->where('name', $name)
                ->update(['group' => $group, 'sort_order' => $order]);
        }
    }

    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropColumn(['group', 'sort_order']);
        });
    }
};