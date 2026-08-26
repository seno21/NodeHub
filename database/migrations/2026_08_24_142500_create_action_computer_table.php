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
        Schema::create('action_computer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remote_action_id')->constrained('remote_actions')->cascadeOnDelete();
            $table->foreignId('computer_id')->constrained('computers')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['remote_action_id', 'computer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_computer');
    }
};
