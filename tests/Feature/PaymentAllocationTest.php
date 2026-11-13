<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Party;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\RepairOrder;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentAllocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_allocates_to_repair_order_and_deducts_balance()
    {
        // Setup permissions
        $perm1 = Permission::firstOrCreate(['name' => 'create payments', 'guard_name' => 'web']);
        $perm2 = Permission::firstOrCreate(['name' => 'view payments', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->givePermissionTo([$perm1, $perm2]);
        $this->actingAs($user);

        $party = Party::create([
            'name' => 'Test Repair Customer',
            'phone' => '03001234567',
            'type' => 'customer',
            'status' => true,
        ]);

        $cashAccount = ChartOfAccount::create([
            'name' => 'Cash',
            'type' => 'Asset',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'status' => 1,
        ]);
        ChartOfAccount::create([
            'name' => 'Accounts Receivable',
            'type' => 'Asset',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'status' => 1,
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'status' => true,
        ]);

        $repair = RepairOrder::create([
            'order_no' => 'REP-TEST-001',
            'party_id' => $party->id,
            'customer_name' => $party->name,
            'customer_phone' => $party->phone,
            'received_date' => now(),
            'total_devices' => 1,
            'total_parts_cost' => 54,
            'total_labor_cost' => 1900,
            'total_amount' => 1954,
            'advance_amount' => 0,
            'balance_amount' => 1954,
            'status' => 'delivered',
        ]);

        // Submit payment of 1900
        $response = $this->post(route('payments.store'), [
            'party_id' => $party->id,
            'warehouse_id' => $warehouse->id,
            'from_account_id' => $cashAccount->id,
            'amount' => 1900,
            'payment_type' => 'received',
            'date' => now()->toDateString(),
        ]);

        $response->assertRedirect();

        $repair->refresh();
        $this->assertEquals(1900.0, (float)$repair->advance_amount);
        $this->assertEquals(54.0, (float)$repair->balance_amount);

        $this->assertDatabaseHas('payment_allocations', [
            'allocatable_type' => RepairOrder::class,
            'allocatable_id' => $repair->id,
            'amount' => 1900,
        ]);

        // Delete payment and ensure balance reverts
        $payment = Payment::latest()->first();
        $permDelete = Permission::firstOrCreate(['name' => 'delete payments', 'guard_name' => 'web']);
        $user->givePermissionTo($permDelete);

        $delResponse = $this->delete(route('payments.destroy', $payment));
        $delResponse->assertRedirect();

        $repair->refresh();
        $this->assertEquals(0.0, (float)$repair->advance_amount);
        $this->assertEquals(1954.0, (float)$repair->balance_amount);

        $this->assertDatabaseMissing('payment_allocations', [
            'payment_id' => $payment->id,
        ]);
    }
}
