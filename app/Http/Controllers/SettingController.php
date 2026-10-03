<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::first();
        $customers = \App\Models\Party::where('type', 'customer')->where('status', true)->orderBy('name')->get();
        $warehouses = \App\Models\Warehouse::where('status', true)->orderBy('name')->get();
        return view('settings.edit', compact('setting', 'customers', 'warehouses'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_address' => 'nullable|string',
            'timezone' => 'required|string',
            'currency_symbol' => 'required|string|max:10',
            'invoice_footer_note' => 'nullable|string',
            'whatsapp_sale_template' => 'nullable|string',
            'whatsapp_purchase_template' => 'nullable|string',
            'whatsapp_ledger_template' => 'nullable|string',
            'walk_in_customer_id' => 'nullable|exists:parties,id',
            'shop_warehouse_id' => 'nullable|exists:warehouses,id',
            'invoice_prefix' => 'required|string|max:20',
            'low_stock_threshold' => 'required|integer|min:0',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $setting = Setting::first();
        $data = $request->except('company_logo');
        $data['show_sale_time'] = $request->boolean('show_sale_time');

        if ($request->hasFile('company_logo')) {
            // Delete old logo if exists in public/logo
            if ($setting->company_logo && file_exists(public_path($setting->company_logo))) {
                @unlink(public_path($setting->company_logo));
            }
            
            $file = $request->file('company_logo');
            $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            
            // Ensure public/logo directory exists
            if (!file_exists(public_path('logo'))) {
                mkdir(public_path('logo'), 0755, true);
            }
            
            $file->move(public_path('logo'), $filename);
            $data['company_logo'] = 'logo/' . $filename;
        }

        $setting->update($data);

        // Automatically clear cache after updating settings
        try {
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        } catch (\Exception $e) {
            // Log or ignore if failed
        }

        return redirect()->back()->with('success', 'Settings updated and system cache cleared successfully.');
    }

    public function clearCache()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            return redirect()->back()->with('success', 'System cache cleared successfully. Everything is now fresh.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    public function downloadBackup()
    {
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');
        
        $filename = "backup-" . $dbName . "-" . date('Y-m-d-H-i-s') . ".sql";
        $tempPath = storage_path("app/" . $filename);

        // Command to run mysqldump. On windows, we might need to specify the path if not in env
        // but usually it works if mysql is installed.
        $passwordPart = $dbPass ? "-p\"" . $dbPass . "\"" : "";
        $command = "mysqldump -h {$dbHost} -u {$dbUser} {$passwordPart} {$dbName} > \"{$tempPath}\"";

        try {
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                return redirect()->back()->with('error', 'Database backup failed. Please ensure mysqldump is installed and accessible.');
            }

            return response()->download($tempPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }
}
