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
        Schema::create('leave_workflows', function (Blueprint $table) {
            $table->id();
            // Foreign key linking to the leave_types table
            $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('cascade');
            $table->unsignedInteger('step_number'); // e.g., 1, 2, 3 for order
            $table->string('approver_role');       // e.g., 'hod', 'dsa', 'daa'
            $table->enum('action_type', ['approval', 'record_keeping'])->default('approval');
            $table->timestamps();

            // Unique constraints to ensure data integrity
            $table->unique(['leave_type_id', 'step_number']);        // A leave type can only have one step 1, one step 2 etc.
            $table->unique(['leave_type_id', 'approver_role']);     // A role should appear only once per workflow for approval
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_workflows');
    }
};