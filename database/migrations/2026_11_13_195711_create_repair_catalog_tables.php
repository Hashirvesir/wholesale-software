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
        Schema::create('repair_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('logo')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('repair_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_brand_id')->constrained('repair_brands')->cascadeOnDelete();
            $table->string('name');
            $table->string('series')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['repair_brand_id', 'name']);
        });

        Schema::create('repair_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('General');
            $table->decimal('default_labor_cost', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        if (Schema::hasTable('repair_devices') && !Schema::hasColumn('repair_devices', 'services')) {
            Schema::table('repair_devices', function (Blueprint $table) {
                $table->json('services')->nullable()->after('problem_description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('repair_devices') && Schema::hasColumn('repair_devices', 'services')) {
            Schema::table('repair_devices', function (Blueprint $table) {
                $table->dropColumn('services');
            });
        }

        Schema::dropIfExists('repair_services');
        Schema::dropIfExists('repair_models');
        Schema::dropIfExists('repair_brands');
    }
};
