<?php

declare(strict_types=1);

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
            $table->foreignId('homeroom_teacher_id')
                ->nullable()
                ->after('academic_year_id')
                ->constrained('teachers')
                ->nullOnDelete();

            $table->index('homeroom_teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropForeign(['homeroom_teacher_id']);
            $table->dropIndex(['homeroom_teacher_id']);
            $table->dropColumn('homeroom_teacher_id');
        });
    }
};
