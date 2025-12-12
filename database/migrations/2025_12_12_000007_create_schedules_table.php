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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1=Monday, 6=Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Prevent schedule conflicts: same classroom, same academic year, same day, same start time
            $table->unique(
                ['classroom_id', 'academic_year_id', 'day_of_week', 'start_time'],
                'schedules_no_time_conflict_unique'
            );

            // Indexes for frequently queried columns
            $table->index('classroom_id');
            $table->index('subject_id');
            $table->index('teacher_id');
            $table->index('academic_year_id');
            $table->index('day_of_week');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
