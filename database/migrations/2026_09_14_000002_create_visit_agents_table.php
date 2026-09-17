<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visites')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['visit_id', 'agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_agents');
    }
};