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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->unsignedTinyInteger('type'); // 1=Odd (Ganjil), 2=Even (Genap)
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            // Each academic year can only have one semester of each type
            $table->unique(['academic_year_id', 'type'], 'semesters_academic_year_type_unique');

            // Indexes for frequently queried columns
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
