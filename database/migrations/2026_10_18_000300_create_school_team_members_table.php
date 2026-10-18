<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Participantes / alunos de cada time escolar.
 * Guarda os dados de cada integrante (Nome, RA, E-mail, Celular, Escolaridade e Período).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_team_id')->constrained('school_teams')->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('academic_registry', 50)->nullable()->comment('RA');
            $table->string('email', 200)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('education_level', 100)->nullable();
            $table->string('college_period', 50)->nullable();
            $table->timestamps();

            $table->index('school_team_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_team_members');
    }
};
