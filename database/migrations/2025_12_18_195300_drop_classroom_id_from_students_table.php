<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase G: Drop the legacy classroom_id column from students table.
 * Class assignment is now handled via student_enrollments table.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Drop foreign key first if exists
            $table->dropForeign(['classroom_id']);
            $table->dropColumn('classroom_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('classroom_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();
        });
    }
};
