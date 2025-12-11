<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Alter teachers table to match new schema from fase2_schema.md
     */
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            // Rename existing columns
            $table->renameColumn('teacher_number', 'nip');
            $table->renameColumn('phone_number', 'phone');
        });

        Schema::table('teachers', function (Blueprint $table) {
            // Modify column sizes
            $table->string('nip', 30)->nullable()->change();
            $table->string('phone', 20)->nullable()->change();

            // Drop unused columns
            $table->dropColumn(['date_of_birth', 'gender', 'specialization']);

            // Add new columns
            $table->boolean('is_active')->default(true)->after('address');
        });

        // Add indexes after all changes
        Schema::table('teachers', function (Blueprint $table) {
            $table->unique('nip');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropUnique(['nip']);
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('is_active');

            $table->string('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('specialization')->nullable();
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->renameColumn('nip', 'teacher_number');
            $table->renameColumn('phone', 'phone_number');
        });
    }
};
