<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('niu');
            $table->string('logo')->nullable();
            $table->text('address');
            $table->string('phone');
            $table->string('email');
            $table->string('nif');
            $table->string('rc');
            $table->string('patente')->nullable();
            $table->string('cnss')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_rib')->nullable();
            $table->string('sfec_api_key')->nullable();
            $table->string('sfec_api_key_sandbox')->nullable();
            $table->enum('sfec_environment', ['production', 'sandbox'])->default('sandbox');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
