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
        Schema::create('rfid_cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_uid', 50);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('status')->default(1); // 1=ACTIVE, 2=LOST, 3=BLOCKED, 4=EXPIRED
            $table->date('issued_at');
            $table->date('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique constraint on card_uid
            $table->unique('card_uid', 'rfid_cards_card_uid_unique');

            // Indexes for frequently queried columns
            $table->index('user_id', 'rfid_cards_user_id_index');
            $table->index('status', 'rfid_cards_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfid_cards');
    }
};
