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
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('maintenance_type'); // e.g., "Routine Check", "Repair", "Upgrade"
            $table->text('description');
            $table->decimal('cost', 10, 2)->nullable(); // Cost of the maintenance
            $table->foreignId('performed_by')->constrained('users')->onDelete('set null'); // User who performed the maintenance
            $table->dateTime('performed_at'); // Date and time when the maintenance was performed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
    }
};
