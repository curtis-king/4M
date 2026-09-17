<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visites', function (Blueprint $table) {
            $table->string('produit_alimentaire')->nullable()->after('objet');
            $table->string('numero_lot')->nullable()->after('produit_alimentaire');
            $table->string('origine_produit')->nullable()->after('numero_lot');
            $table->date('date_prelevement')->nullable()->after('origine_produit');
            $table->enum('type_controle', ['routine', 'surveillance', 'plainte', 'certification'])->nullable()->after('date_prelevement');
            $table->enum('statut_resultat', ['en_attente', 'conforme', 'non_conforme'])->nullable()->after('type_controle');
        });
    }

    public function down(): void
    {
        Schema::table('visites', function (Blueprint $table) {
            $table->dropColumn([
                'produit_alimentaire',
                'numero_lot',
                'origine_produit',
                'date_prelevement',
                'type_controle',
                'statut_resultat',
            ]);
        });
    }
};