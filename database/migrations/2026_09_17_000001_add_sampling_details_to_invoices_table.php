<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('invoice_type');
            $table->string('sample_nature')->nullable()->after('subject');
            $table->string('company_site')->nullable()->after('sample_nature');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['subject', 'sample_nature', 'company_site']);
        });
    }
};
