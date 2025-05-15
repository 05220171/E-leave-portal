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
        Schema::table('leaves', function (Blueprint $table) {
            // Add the new column, e.g., after the 'reason' column
            // It's an integer, and can be nullable if old records won't have it immediately,
            // or you can provide a default. For new leaves, it will be calculated.
            $table->integer('number_of_days')->nullable()->after('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('number_of_days');
        });
    }
};