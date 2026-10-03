<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Setting::create([
            'company_name' => 'My Wholesale Business',
            'company_phone' => '0300-1234567',
            'company_email' => 'info@example.com',
            'company_address' => 'Pakistan',
            'timezone' => 'Asia/Karachi',
            'currency_symbol' => 'Rs.',
            'invoice_prefix' => 'INV-',
            'invoice_footer_note' => 'Thank you for your business!',
            'low_stock_threshold' => 10,
        ]);
    }
}
