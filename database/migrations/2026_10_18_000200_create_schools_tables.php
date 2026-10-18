<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Escolas, seus times permanentes (cada um com nivel N1/N2/N3) e o registro
 * de participacao em provas. A conta `users` do time e criada por prova;
 * `exam_participations` liga o time permanente a cada uma delas e, via
 * `phase`, impede duas provas na mesma fase e ja comporta a fase 02.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->unique();
            $table->string('city', 120)->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('school_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name', 200);
            $table->unsignedTinyInteger('level')->comment('1=N1, 2=N2, 3=N3');
            $table->timestamps();

            $table->unique(['school_id', 'name']);
            $table->index(['school_id', 'level']);
        });

        Schema::create('exam_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_team_id')->constrained('school_teams')->cascadeOnDelete();
            $table->foreignId('contest_id')->constrained('contests')->cascadeOnDelete();
            // users.user_id is an unsigned INT primary key, not a bigint.
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('phase')->default(1);
            $table->timestamps();

            $table->unique(['school_team_id', 'contest_id']);
            $table->unique(['school_team_id', 'phase']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_participations');
        Schema::dropIfExists('school_teams');
        Schema::dropIfExists('schools');
    }
};
