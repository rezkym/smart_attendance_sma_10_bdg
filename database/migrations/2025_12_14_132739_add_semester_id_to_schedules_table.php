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
        Schema::table('schedules', function (Blueprint $table) {
            // Drop old unique constraint first
            $table->dropUnique('schedules_no_time_conflict_unique');

            // Add semester_id column after academic_year_id
            $table->foreignId('semester_id')
                ->after('academic_year_id')
                ->constrained('semesters')
                ->restrictOnDelete();

            // Add new unique constraint including semester_id
            $table->unique(
                ['classroom_id', 'semester_id', 'day_of_week', 'start_time'],
                'schedules_no_time_conflict_unique'
            );

            // Index for semester filtering
            $table->index('semester_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Drop new unique constraint
            $table->dropUnique('schedules_no_time_conflict_unique');

            // Drop foreign key and column
            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');

            // Restore old unique constraint
            $table->unique(
                ['classroom_id', 'academic_year_id', 'day_of_week', 'start_time'],
                'schedules_no_time_conflict_unique'
            );
        });
    }
};
