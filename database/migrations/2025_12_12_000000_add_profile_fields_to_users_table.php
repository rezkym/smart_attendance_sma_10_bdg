<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add profile fields to users table for Student and Teacher features.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name', 100)->nullable()->after('name');
            $table->enum('gender', ['L', 'P'])->nullable()->after('full_name');
            $table->string('birth_place', 100)->nullable()->after('gender');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->text('address')->nullable()->after('birth_date');
            $table->string('phone_number', 20)->nullable()->after('address');

            // Indexes for search and filter
            $table->index('full_name');
            $table->index('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['full_name']);
            $table->dropIndex(['gender']);

            $table->dropColumn([
                'full_name',
                'gender',
                'birth_place',
                'birth_date',
                'address',
                'phone_number',
            ]);
        });
    }
};
