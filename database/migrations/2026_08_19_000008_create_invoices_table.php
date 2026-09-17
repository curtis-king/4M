<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->cascadeOnDelete();
            $table->string('walk_in_name')->nullable();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->string('number')->unique();
            $table->string('voucher_number')->nullable()->unique();
            $table->date('date');
            $table->date('due_date');
            $table->enum('status', ['brouillon', 'envoyee', 'payee', 'partiel', 'annulee'])->default('brouillon');
            $table->foreignId('insurance_contract_id')->nullable()->constrained('insurance_contracts')->nullOnDelete();
            $table->string('pec_number')->nullable();
            $table->enum('recipient_type', ['business', 'individual', 'government', 'foreign'])->default('individual');
            $table->enum('currency', ['XAF', 'USD'])->default('XAF');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(18);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->enum('discount_type', ['aucun', 'pourcentage', 'montant'])->default('aucun');
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('insurance_covered', 12, 2)->default(0);
            $table->decimal('patient_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('sfec_certified')->default(false);
            $table->string('sfec_certification_number')->nullable();
            $table->text('sfec_signature')->nullable();
            $table->string('sfec_short_signature')->nullable();
            $table->text('sfec_qr_code')->nullable();
            $table->dateTime('sfec_certification_date')->nullable();
            $table->string('sfec_identifier')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
