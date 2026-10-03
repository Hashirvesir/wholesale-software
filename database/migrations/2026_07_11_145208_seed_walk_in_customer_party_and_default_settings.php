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
        $walkInParty = \Illuminate\Support\Facades\DB::table('parties')->where('name', 'Walk In Customer')->first();
        if (!$walkInParty) {
            $walkInPartyId = \Illuminate\Support\Facades\DB::table('parties')->insertGetId([
                'name' => 'Walk In Customer',
                'type' => 'customer',
                'opening_balance' => 0,
                'balance_type' => 'debit',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $walkInPartyId = $walkInParty->id;
        }

        $shopWarehouseId = \Illuminate\Support\Facades\DB::table('warehouses')->where('name', 'SAM ENTERPRISES')->value('id');

        \Illuminate\Support\Facades\DB::table('settings')->update([
            'walk_in_customer_id' => $walkInPartyId,
            'shop_warehouse_id' => $shopWarehouseId,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
