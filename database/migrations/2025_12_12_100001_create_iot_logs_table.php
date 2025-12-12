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
        Schema::create('iot_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iot_device_id')->constrained()->cascadeOnDelete();
            $table->enum('log_type', ['request', 'response', 'error']);
            $table->string('endpoint', 255);
            $table->string('method', 10)->comment('HTTP method: GET, POST, etc.');
            $table->json('request_payload')->nullable()->comment('Request body/params');
            $table->json('response_payload')->nullable()->comment('Response body');
            $table->integer('response_code')->nullable()->comment('HTTP status code');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->integer('duration_ms')->nullable()->comment('Request duration in milliseconds');
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['iot_device_id', 'created_at']);
            $table->index('log_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iot_logs');
    }
};
