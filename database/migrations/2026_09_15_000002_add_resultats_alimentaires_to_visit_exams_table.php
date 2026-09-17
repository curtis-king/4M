<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visit_exams', function (Blueprint $table) {
            $table->string('resultat_valeur')->nullable()->after('result');
            $table->string('unite_mesure')->nullable()->after('resultat_valeur');
            $table->string('valeur_limite')->nullable()->after('unite_mesure');
            $table->boolean('est_conforme')->nullable()->after('valeur_limite');
        });
    }

    public function down(): void
    {
        Schema::table('visit_exams', function (Blueprint $table) {
            $table->dropColumn(['resultat_valeur', 'unite_mesure', 'valeur_limite', 'est_conforme']);
        });
    }
};