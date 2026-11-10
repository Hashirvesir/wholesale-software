<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Party;
use App\Models\Warehouse;
use App\Models\ChartOfAccount;
use App\Models\Cheque;
use App\Models\Payment;
use App\Models\LedgerEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ChequeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Party $customer;
    protected Party $vendor;
    protected Warehouse $warehouse;
    protected ChartOfAccount $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'view payments']);
        Permission::firstOrCreate(['name' => 'create payments']);
        Permission::firstOrCreate(['name' => 'delete payments']);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->givePermissionTo(Permission::all());

        $this->user = User::factory()->create();
        $this->user->assignRole($role);

        $this->warehouse = Warehouse::create(['name' => 'Main Shop', 'status' => true]);

        $this->bankAccount = ChartOfAccount::create([
            'name' => 'Meezan Bank Limited',
            'type' => 'Asset',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'status' => true,
        ]);

        $this->customer = Party::create([
            'name' => 'Lahore Super Store',
            'type' => 'customer',
            'phone' => '03001234567',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'status' => true,
        ]);

        $this->vendor = Party::create([
            'name' => 'Factory Mill Supplier',
            'type' => 'vendor',
            'phone' => '03007654321',
            'opening_balance' => 0,
            'balance_type' => 'credit',
            'status' => true,
        ]);
    }

    public function test_cheque_index_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('cheques.index'));
        $response->assertOk();
        $response->assertSee('Cheque & PDC Management', false);
    }

    public function test_can_register_received_and_issued_cheques(): void
    {
        $response = $this->actingAs($this->user)->post(route('cheques.store'), [
            'type' => 'received',
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'cheque_number' => 'CHQ-998877',
            'drawer_bank' => 'Allied Bank',
            'amount' => 50000.00,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'note' => 'Test customer cheque',
        ]);

        $response->assertRedirect(route('cheques.index'));
        $this->assertDatabaseHas('cheques', [
            'cheque_number' => 'CHQ-998877',
            'amount' => 50000.00,
            'status' => 'pending',
            'type' => 'received',
        ]);
    }

    public function test_can_deposit_cheque_to_bank(): void
    {
        $cheque = Cheque::create([
            'type' => 'received',
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'cheque_number' => 'CHQ-112233',
            'amount' => 30000.00,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->post(route('cheques.deposit', $cheque), [
            'bank_account_id' => $this->bankAccount->id,
            'deposited_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $cheque->refresh();
        $this->assertEquals('deposited', $cheque->status);
        $this->assertEquals($this->bankAccount->id, $cheque->bank_account_id);
    }

    public function test_clearing_cheque_posts_payment_and_double_entry_ledger(): void
    {
        $cheque = Cheque::create([
            'type' => 'received',
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'bank_account_id' => $this->bankAccount->id,
            'cheque_number' => 'CHQ-CLEAR-1',
            'drawer_bank' => 'HBL',
            'amount' => 75000.00,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'status' => 'deposited',
        ]);

        $clearResponse = $this->actingAs($this->user)->post(route('cheques.clear', $cheque), [
            'bank_account_id' => $this->bankAccount->id,
            'cleared_date' => now()->toDateString(),
        ]);

        $clearResponse->assertRedirect();
        $cheque->refresh();

        $this->assertEquals('cleared', $cheque->status);
        $this->assertNotNull($cheque->payment_id);

        // Verify Payment record was generated
        $this->assertDatabaseHas('payments', [
            'id' => $cheque->payment_id,
            'party_id' => $this->customer->id,
            'amount' => 75000.00,
            'payment_type' => 'received',
        ]);

        // Verify double-entry ledger entries (Bank Debited, Customer Party Credited)
        $this->assertDatabaseHas('ledger_entries', [
            'reference_type' => 'payment',
            'reference_id' => $cheque->payment_id,
            'account_id' => $this->bankAccount->id,
            'debit' => 75000.00,
            'credit' => 0.00,
        ]);

        $this->assertDatabaseHas('ledger_entries', [
            'reference_type' => 'payment',
            'reference_id' => $cheque->payment_id,
            'party_id' => $this->customer->id,
            'debit' => 0.00,
            'credit' => 75000.00,
        ]);
    }

    public function test_bouncing_cheque_reverses_ledger_if_cleared(): void
    {
        $cheque = Cheque::create([
            'type' => 'received',
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'bank_account_id' => $this->bankAccount->id,
            'cheque_number' => 'CHQ-BOUNCE-1',
            'amount' => 20000.00,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $bounceResponse = $this->actingAs($this->user)->post(route('cheques.bounce', $cheque), [
            'bounced_date' => now()->toDateString(),
            'bounce_reason' => 'Insufficient funds in customer account',
        ]);

        $bounceResponse->assertRedirect();
        $cheque->refresh();

        $this->assertEquals('bounced', $cheque->status);
        $this->assertEquals('Insufficient funds in customer account', $cheque->bounce_reason);
    }
}
