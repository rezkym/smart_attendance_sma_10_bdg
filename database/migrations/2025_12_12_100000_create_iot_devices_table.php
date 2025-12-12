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
        Schema::create('iot_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Device name (e.g., "ESP32 Ruang 101")');
            $table->string('device_code', 50)->unique()->comment('Unique device identifier');
            $table->string('api_key', 64)->unique()->comment('API key for authentication');
            $table->text('description')->nullable();
            $table->string('location', 255)->nullable()->comment('Physical location of device');
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->timestamp('last_seen_at')->nullable()->comment('Last successful API call');
            $table->string('ip_address', 45)->nullable()->comment('Last known IP address');
            $table->string('firmware_version', 20)->nullable();
            $table->timestamps();

            $table->index('api_key');
            $table->index('device_code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iot_devices');
    }
};
