<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('polygon_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->nullable()->constrained('contests')->nullOnDelete();
            $table->string('name', 200);
            $table->string('short_name', 10);
            $table->string('basename', 100);
            $table->integer('time_limit')->default(1);
            $table->integer('memory_limit')->default(256);
            $table->integer('output_limit')->default(1024);
            $table->longText('description')->nullable();
            $table->longText('input_description')->nullable();
            $table->longText('output_description')->nullable();
            $table->longText('notes')->nullable();
            $table->string('color_name', 50)->nullable();
            $table->string('color_hex', 7)->nullable();

            // Generator and test scripts
            $table->longText('generator_source')->nullable();
            $table->longText('generator_script')->nullable();
            $table->json('manual_tests')->nullable();

            // Solution sources for calibration
            $table->longText('solution_accept')->nullable();
            $table->longText('solution_tle')->nullable();
            $table->longText('solution_wrong')->nullable();
            $table->json('additional_solutions')->nullable();

            // Status and calibration results
            $table->enum('status', ['draft', 'generated', 'calibrated', 'published'])->default('draft');
            $table->json('calibration_results')->nullable();
            $table->foreignId('published_problem_id')->nullable()->constrained('problems')->nullOnDelete();
            $table->string('published_bank_code', 50)->nullable();

            // users.user_id is an unsignedInteger (increments), not bigIncrements.
            // In MySQL, unsignedBigInteger would fail foreign key constraint with error 3780.
            $table->unsignedInteger('created_by');
            $table->foreign('created_by')->references('user_id')->on('users')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polygon_exercises');
    }
};
