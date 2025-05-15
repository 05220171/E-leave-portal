<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->enum('leave_type', ['emergency', 'regular']);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->string('document')->nullable(); // Path to uploaded file

            $table->enum('status', [
                'awaiting_hod_approval',
                'awaiting_dsa_approval',
                'awaiting_sso_approval',
                'approved',
                'rejected_by_hod',
                'rejected_by_dsa',
                'rejected_by_sso',
                'cancelled'
            ])->default('awaiting_hod_approval');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
