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
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropForeign('classrooms_homeroom_teacher_id_foreign');
        });

        Schema::table('classrooms', function (Blueprint $table) {
            // Drop unused columns
            $table->dropColumn(['academic_year', 'homeroom_teacher_id']);

            // Modify existing columns to ensure correct type
            $table->string('name', 50)->change();
            $table->unsignedTinyInteger('grade_level')->change();
            $table->unsignedSmallInteger('capacity')->nullable()->change();
            $table->text('description')->nullable()->change();

            // Add new columns
            $table->foreignId('academic_year_id')
                ->after('grade_level')
                ->constrained('academic_years')
                ->restrictOnDelete();
            $table->boolean('is_active')->default(true)->after('description');
        });

        // Add indexes
        Schema::table('classrooms', function (Blueprint $table) {
             $table->unique(['name', 'academic_year_id']);
             $table->index('grade_level');
             $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropIndex(['name', 'academic_year_id']);
            $table->dropIndex(['grade_level']);
            $table->dropIndex(['is_active']);

            $table->dropColumn(['academic_year_id', 'is_active']);

            $table->string('academic_year')->nullable();
            $table->unsignedBigInteger('homeroom_teacher_id')->nullable();
        });
    }
};
