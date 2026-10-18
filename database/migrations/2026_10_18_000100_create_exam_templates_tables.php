<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Templates fixos de prova (N1/N2/N3). Um template NAO e um contest: e a
 * receita (nome, nivel, duracao, questoes em ordem) que o agendamento copia
 * para um contest real. Editar o template nao altera contests ja criados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 200);
            $table->unsignedTinyInteger('level')->unique();
            $table->unsignedInteger('duration')->default(180)->comment('Minutes');
            $table->unsignedInteger('penalty')->default(20)->comment('Minutes per wrong submission');
            $table->unsignedInteger('freeze_minutes')->default(60)->comment('Minutes before end to freeze');
            $table->timestamps();
        });

        Schema::create('exam_template_problems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_template_id')->constrained('exam_templates')->cascadeOnDelete();
            $table->foreignId('problem_bank_id')->constrained('problem_bank')->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->timestamps();

            $table->unique(['exam_template_id', 'position']);
            $table->unique(['exam_template_id', 'problem_bank_id']);
        });

        Schema::table('contests', function (Blueprint $table) {
            $table->foreignId('exam_template_id')->nullable()->constrained('exam_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exam_template_id');
        });
        Schema::dropIfExists('exam_template_problems');
        Schema::dropIfExists('exam_templates');
    }
};
