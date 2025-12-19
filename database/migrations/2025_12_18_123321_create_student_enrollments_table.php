<?php

declare(strict_types=1);

use App\Enums\EnrollmentStatus;
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
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->date('enrolled_at');
            $table->date('left_at')->nullable();
            $table->tinyInteger('status')->default(EnrollmentStatus::ACTIVE->value);
            $table->timestamps();

            // Indexes for frequently queried columns
            $table->index('status');
            $table->index('enrolled_at');

            // Unique constraint: student can only have one enrollment per classroom per academic year
            $table->unique(['student_id', 'classroom_id', 'academic_year_id'], 'student_classroom_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
