<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->decimal('discount_rate', 5, 2)->nullable()->after('notes');
        });

        Schema::table('visites', function (Blueprint $table) {
            $table->string('objet')->nullable()->after('agent_id');
            $table->foreignId('insurance_contract_id')->nullable()->after('objet')
                ->constrained('insurance_contracts')->nullOnDelete();
            $table->enum('discount_type', ['aucun', 'pourcentage', 'montant'])->default('aucun')->after('status');
            $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_value');
            $table->decimal('subtotal', 12, 2)->default(0)->after('discount_amount');
            $table->decimal('total', 12, 2)->default(0)->after('subtotal');
            $table->decimal('insurance_covered', 12, 2)->default(0)->after('total');
            $table->decimal('patient_amount', 12, 2)->default(0)->after('insurance_covered');
            $table->decimal('patient_paid', 12, 2)->default(0)->after('patient_amount');
            $table->index(['visit_date', 'insurance_contract_id'], 'visites_date_contract_idx');
        });

        Schema::table('visit_exams', function (Blueprint $table) {
            $table->decimal('quantity', 8, 2)->default(1)->after('service_id');
            $table->decimal('unit_price', 12, 2)->nullable()->after('quantity');
            $table->enum('discount_type', ['aucun', 'pourcentage', 'montant'])->default('aucun')->after('unit_price');
            $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_value');
            $table->decimal('net_amount', 12, 2)->default(0)->after('discount_amount');
            $table->string('item_type')->default('service')->after('net_amount');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_statement')->default(false)->after('status');
            $table->date('statement_start_date')->nullable()->after('is_statement');
            $table->date('statement_end_date')->nullable()->after('statement_start_date');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('source_visit_id')->nullable()->after('invoice_id')
                ->constrained('visites')->nullOnDelete();
            $table->string('company_name')->nullable()->after('source_visit_id');
            $table->string('assured_name')->nullable()->after('company_name');
            $table->decimal('coverage_rate', 5, 2)->nullable()->after('assured_name');
            $table->decimal('insurance_part', 12, 2)->default(0)->after('coverage_rate');
            $table->index('source_visit_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropIndex('invoice_items_source_visit_id_index');
            $table->dropForeign(['source_visit_id']);
            $table->dropColumn(['source_visit_id', 'company_name', 'assured_name', 'coverage_rate', 'insurance_part']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['is_statement', 'statement_start_date', 'statement_end_date']);
        });

        Schema::table('visit_exams', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit_price', 'discount_type', 'discount_value', 'discount_amount', 'net_amount', 'item_type']);
        });

        Schema::table('visites', function (Blueprint $table) {
            $table->dropIndex('visites_date_contract_idx');
            $table->dropForeign(['insurance_contract_id']);
            $table->dropColumn([
                'objet',
                'insurance_contract_id',
                'discount_type',
                'discount_value',
                'discount_amount',
                'subtotal',
                'total',
                'insurance_covered',
                'patient_amount',
                'patient_paid',
            ]);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('discount_rate');
        });
    }
};