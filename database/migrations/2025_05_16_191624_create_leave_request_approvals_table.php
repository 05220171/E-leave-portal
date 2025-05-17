<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_request_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_id')->constrained('leaves')->onDelete('cascade'); // Link to the main leave request
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // User who took the action
            $table->unsignedInteger('workflow_step_number'); // The step_number from leave_workflows this action corresponds to
            $table->string('acted_as_role');         // The role the user acted in (e.g., 'hod', 'dsa')
            $table->enum('action_taken', ['approved', 'rejected', 'recorded']); // What action was performed
            $table->text('remarks')->nullable();                 // Remarks from the approver/actor
            $table->timestamp('action_at')->useCurrent();        // When the action was taken
            $table->timestamps(); // created_at and updated_at for the record itself
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request_approvals');
    }
};