<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Removed: use Illuminate\Support\Facades\DB; // Not needed for Option C

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            // --- ADD NEW COLUMNS FOR DYNAMIC WORKFLOW ---
            $table->foreignId('leave_type_id')
                  ->nullable() // Nullable allows flexibility during data migration / initial setup
                  ->after('student_id') // Ensure 'student_id' column exists from your create_leaves_table
                  ->constrained('leave_types')
                  ->onDelete('set null'); // Or 'restrict' if a LeaveType should not be deletable if in use

            $table->unsignedInteger('current_step_number')->nullable()->after('leave_type_id');
            $table->string('current_approver_role')->nullable()->after('current_step_number');

            // The default here is a fallback. The controller will set the specific initial status.
            $table->string('overall_status', 50)->default('awaiting_hod_approval')->after('reason');

            $table->text('final_remarks')->nullable()->after('overall_status');

            // Ensure 'number_of_days' column exists or add it
            if (!Schema::hasColumn('leaves', 'number_of_days')) {
                // Try to place it logically, e.g., after 'end_date' or 'reason'
                if (Schema::hasColumn('leaves', 'end_date')) {
                    $table->integer('number_of_days')->nullable()->after('end_date');
                } elseif (Schema::hasColumn('leaves', 'reason')) {
                     $table->integer('number_of_days')->nullable()->after('reason');
                } else {
                     // If common anchors aren't found, add it without specific order (usually at the end)
                    $table->integer('number_of_days')->nullable();
                }
            }

            // --- OPTION C: Drop Old Enum Columns ---
            // Ensure these columns actually exist from your 'create_leaves_table' migration before dropping
            if (Schema::hasColumn('leaves', 'leave_type')) {
                $table->dropColumn('leave_type');
            }
            if (Schema::hasColumn('leaves', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            // --- REVERSE ADDING NEW COLUMNS ---
            if (Schema::hasColumn('leaves', 'leave_type_id')) {
                // Need to check for and drop foreign key constraint before dropping the column
                // Laravel's convention for foreign key names is tablename_columnname_foreign
                // However, using getDoctrineSchemaManager is more robust if unsure.
                try {
                    $foreignKeys = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('leaves');
                    $hasForeignKey = false;
                    foreach ($foreignKeys as $foreignKey) {
                        if (in_array('leave_type_id', $foreignKey->getLocalColumns())) {
                            $hasForeignKey = true;
                            break;
                        }
                    }
                    if ($hasForeignKey) {
                        $table->dropForeign(['leave_type_id']);
                    }
                } catch (\Exception $e) {
                    // Log error or handle if schema manager isn't available / fails
                    // For simplicity, we assume it works or the foreign key might not exist if up() failed partially
                }
                $table->dropColumn('leave_type_id');
            }
            if (Schema::hasColumn('leaves', 'current_step_number')) {
                $table->dropColumn('current_step_number');
            }
            if (Schema::hasColumn('leaves', 'current_approver_role')) {
                $table->dropColumn('current_approver_role');
            }
            if (Schema::hasColumn('leaves', 'overall_status')) {
                $table->dropColumn('overall_status');
            }
            if (Schema::hasColumn('leaves', 'final_remarks')) {
                $table->dropColumn('final_remarks');
            }
            // Note: Be cautious about dropping 'number_of_days' here.
            // Only drop it if you are CERTAIN this migration's 'up()' method ADDED it
            // AND it wasn't part of your original 'create_leaves_table' migration.
            // If 'number_of_days' was in your original 'create_leaves_table', do NOT drop it in this 'down()' method.


            // --- OPTION C Reversal: Re-add Old Enum Columns ---
            // Check if they don't exist before trying to add them back
            if (!Schema::hasColumn('leaves', 'leave_type')) {
                 $table->enum('leave_type', ['emergency', 'regular'])
                       ->after('student_id'); // Adjust 'after' based on your original schema
            }
            if (!Schema::hasColumn('leaves', 'status')) {
                $table->enum('status', [
                    'awaiting_hod_approval', 'awaiting_dsa_approval', 'awaiting_sso_approval',
                    'approved', 'rejected_by_hod', 'rejected_by_dsa', 'rejected_by_sso', 'cancelled'
                ])->default('awaiting_hod_approval')
                  ->after('reason'); // Adjust 'after' based on your original schema
            }
        });
    }
};