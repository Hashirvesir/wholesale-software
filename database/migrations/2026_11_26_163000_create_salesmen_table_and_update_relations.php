<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create salesmen table if it doesn't already exist
        if (!Schema::hasTable('salesmen')) {
            Schema::create('salesmen', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->decimal('commission_rate', 5, 2)->default(0.00);
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        // 2. Add salesman_id to parties table if not exists
        if (Schema::hasTable('parties') && !Schema::hasColumn('parties', 'salesman_id')) {
            Schema::table('parties', function (Blueprint $table) {
                $table->foreignId('salesman_id')->nullable()->after('type')->constrained('salesmen')->nullOnDelete();
            });
        }

        // 3. Migrate any existing users referenced in sales or payments into salesmen table
        try {
            $salesUserIds = DB::table('sales')->whereNotNull('salesman_id')->pluck('salesman_id');
            $paymentUserIds = DB::table('payments')->whereNotNull('salesman_id')->pluck('salesman_id');
            $userIds = $salesUserIds->merge($paymentUserIds)->unique();

            foreach ($userIds as $uid) {
                $user = DB::table('users')->where('id', $uid)->first();
                if ($user) {
                    $exists = DB::table('salesmen')->where('id', $user->id)->exists();
                    if (!$exists) {
                        DB::table('salesmen')->insert([
                            'id' => $user->id,
                            'name' => $user->name,
                            'phone' => $user->phone ?? null,
                            'email' => $user->email ?? null,
                            'commission_rate' => 0.00,
                            'status' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore if query fails
        }

        // Clean up any orphan IDs that still don't exist in salesmen so foreign key won't fail
        try {
            DB::statement("UPDATE sales SET salesman_id = NULL WHERE salesman_id IS NOT NULL AND salesman_id NOT IN (SELECT id FROM salesmen)");
            DB::statement("UPDATE payments SET salesman_id = NULL WHERE salesman_id IS NOT NULL AND salesman_id NOT IN (SELECT id FROM salesmen)");
        } catch (\Throwable $e) {
        }

        // 4. Update foreign key on sales
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'salesman_id')) {
            try {
                Schema::table('sales', function (Blueprint $table) {
                    $table->dropForeign(['salesman_id']);
                });
            } catch (\Throwable $e) {
                // Foreign key may already have been dropped
            }

            try {
                Schema::table('sales', function (Blueprint $table) {
                    $table->foreign('salesman_id')->references('id')->on('salesmen')->nullOnDelete();
                });
            } catch (\Throwable $e) {
            }
        }

        // 5. Update foreign key on payments
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'salesman_id')) {
            try {
                Schema::table('payments', function (Blueprint $table) {
                    $table->dropForeign(['salesman_id']);
                });
            } catch (\Throwable $e) {
                // Foreign key may already have been dropped
            }

            try {
                Schema::table('payments', function (Blueprint $table) {
                    $table->foreign('salesman_id')->references('id')->on('salesmen')->nullOnDelete();
                });
            } catch (\Throwable $e) {
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'salesman_id')) {
            try {
                Schema::table('payments', function (Blueprint $table) {
                    $table->dropForeign(['salesman_id']);
                    $table->foreign('salesman_id')->references('id')->on('users')->nullOnDelete();
                });
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'salesman_id')) {
            try {
                Schema::table('sales', function (Blueprint $table) {
                    $table->dropForeign(['salesman_id']);
                    $table->foreign('salesman_id')->references('id')->on('users')->nullOnDelete();
                });
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasTable('parties') && Schema::hasColumn('parties', 'salesman_id')) {
            try {
                Schema::table('parties', function (Blueprint $table) {
                    $table->dropForeign(['salesman_id']);
                    $table->dropColumn('salesman_id');
                });
            } catch (\Throwable $e) {
            }
        }

        Schema::dropIfExists('salesmen');
    }
};
