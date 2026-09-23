<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('accounting_clients', 20)->nullable()->after('sfec_environment');
            $table->string('accounting_ventes', 20)->nullable()->after('accounting_clients');
            $table->string('accounting_tva', 20)->nullable()->after('accounting_ventes');
            $table->string('accounting_caisse', 20)->nullable()->after('accounting_tva');
            $table->string('accounting_banque', 20)->nullable()->after('accounting_caisse');
            $table->string('accounting_assurance', 20)->nullable()->after('accounting_banque');
        });

        \DB::table('company_settings')->update([
            'accounting_clients' => '411',
            'accounting_ventes' => '701',
            'accounting_tva' => '44571',
            'accounting_caisse' => '571',
            'accounting_banque' => '512',
            'accounting_assurance' => '411',
        ]);
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'accounting_clients',
                'accounting_ventes',
                'accounting_tva',
                'accounting_caisse',
                'accounting_banque',
                'accounting_assurance',
            ]);
        });
    }
};