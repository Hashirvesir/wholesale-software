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
        Schema::create('repair_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique()->index();
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->dateTime('received_date');
            $table->dateTime('expected_delivery_date')->nullable();
            $table->unsignedInteger('total_devices')->default(1);
            $table->decimal('total_parts_cost', 12, 2)->default(0);
            $table->decimal('total_labor_cost', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('advance_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2)->default(0);
            $table->string('payment_method')->default('cash');
            $table->string('status')->default('pending')->index(); // pending, in_progress, partially_completed, completed, delivered, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('repair_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_order_id')->constrained('repair_orders')->cascadeOnDelete();
            $table->string('device_tracking_no')->index();
            $table->string('brand')->nullable();
            $table->string('model');
            $table->string('imei')->nullable()->index();
            $table->string('color')->nullable();
            $table->string('security_type')->default('none'); // pattern, pin, password, none
            $table->string('security_code')->nullable();
            $table->text('problem_description')->nullable();
            $table->string('accessories_received')->nullable();
            $table->json('condition_checklist')->nullable();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending')->index(); // pending, diagnosing, waiting_parts, completed, delivered, unrepairable
            $table->decimal('labor_cost', 12, 2)->default(0);
            $table->decimal('parts_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->unsignedInteger('warranty_days')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('repair_device_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_device_id')->constrained('repair_devices')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->boolean('is_deducted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_device_parts');
        Schema::dropIfExists('repair_devices');
        Schema::dropIfExists('repair_orders');
    }
};
