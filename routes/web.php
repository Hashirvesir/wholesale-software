<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OpeningStockController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CargoChargeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AccountTransferController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\ReportController;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard/quick-stats', [\App\Http\Controllers\DashboardController::class, 'quickStats'])->middleware(['auth', 'verified'])->name('dashboard.quick-stats');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('units', UnitController::class)->middleware('permission:view units');
    Route::resource('categories', CategoryController::class)->middleware('permission:view categories');
    Route::resource('brands', BrandController::class)->middleware('permission:view brands');
    Route::resource('warehouses', WarehouseController::class)->middleware('permission:view warehouses');
    Route::get('/products/print-labels', [ProductController::class, 'printLabels'])->name('products.print-labels')->middleware('permission:view products');
    Route::get('/products/{product}/get-barcode', [ProductController::class, 'getBarcode'])->name('products.get-barcode')->middleware('permission:view products');
    Route::get('/products/export/pdf', [ProductController::class, 'exportPdf'])->name('products.export.pdf')->middleware('permission:view products');
    Route::get('/products/export/excel', [ProductController::class, 'exportExcel'])->name('products.export.excel')->middleware('permission:view products');
    Route::resource('products', ProductController::class)->middleware('permission:view products');
    Route::get('/opening-stocks/export/pdf', [OpeningStockController::class, 'exportPdf'])->name('opening-stocks.export.pdf')->middleware('permission:view opening stocks');
    Route::get('/opening-stocks/export/excel', [OpeningStockController::class, 'exportExcel'])->name('opening-stocks.export.excel')->middleware('permission:view opening stocks');
    Route::resource('opening-stocks', OpeningStockController::class)->middleware('permission:view opening stocks');
    Route::get('/parties/export/pdf', [PartyController::class, 'exportPdf'])->name('parties.export.pdf')->middleware('permission:view parties');
    Route::get('/parties/export/excel', [PartyController::class, 'exportExcel'])->name('parties.export.excel')->middleware('permission:view parties');
    Route::resource('parties', PartyController::class)->middleware('permission:view parties');
    Route::resource('chart-of-accounts', ChartOfAccountController::class)->middleware('permission:view chart of accounts');
    Route::resource('purchases', PurchaseController::class)->middleware('permission:view purchases');
    Route::resource('sales', SaleController::class)->middleware('permission:view sales');
    Route::resource('cargo-charges', CargoChargeController::class)->middleware('permission:view cargo charges');
    Route::resource('payments', PaymentController::class)->middleware('permission:view payments');
    Route::resource('account-transfers', AccountTransferController::class)->middleware('permission:view account transfers');
    Route::resource('stock-transfers', StockTransferController::class)->middleware('permission:view stock transfers');
    Route::resource('stock-adjustments', \App\Http\Controllers\StockAdjustmentController::class)->middleware('permission:view stock adjustments');
    Route::resource('sales-returns', \App\Http\Controllers\SalesReturnController::class)->middleware('permission:view sales');
    Route::resource('purchase-returns', \App\Http\Controllers\PurchaseReturnController::class)->middleware('permission:view purchases');
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class)->middleware('permission:view expenses');
    Route::resource('expense-categories', \App\Http\Controllers\ExpenseCategoryController::class)->middleware('permission:view expenses');
    Route::resource('activity-logs', \App\Http\Controllers\ActivityLogController::class)->only(['index', 'show'])->middleware('permission:view activity logs');


    // PDF Routes
    Route::get('/sales/{sale}/return', [\App\Http\Controllers\SalesReturnController::class, 'createFromSale'])->name('sales.return');
    Route::post('/sales/{sale}/return', [\App\Http\Controllers\SalesReturnController::class, 'storeFromSale'])->name('sales.return.store');
    
    Route::get('/purchases/{purchase}/return', [\App\Http\Controllers\PurchaseReturnController::class, 'createFromPurchase'])->name('purchases.return');
    Route::post('/purchases/{purchase}/return', [\App\Http\Controllers\PurchaseReturnController::class, 'storeFromPurchase'])->name('purchases.return.store');

    Route::get('/purchases/{purchase}/pdf', [PurchaseController::class, 'downloadPdf'])->name('purchases.pdf');
    Route::get('/sales/{sale}/pdf', [SaleController::class, 'downloadPdf'])->name('sales.pdf');
    Route::get('/sales/{sale}/thermal', [SaleController::class, 'thermalPrint'])->name('sales.thermal');
    Route::get('/payments/{payment}/pdf', [PaymentController::class, 'downloadPdf'])->name('payments.pdf');
    Route::get('/payments/{payment}/thermal', [PaymentController::class, 'thermalPrint'])->name('payments.thermal');


    // Reports
    Route::get('/reports/account-ledger', [ReportController::class, 'accountLedger'])->name('reports.account-ledger')->middleware('permission:view account ledger');
    Route::get('/reports/party-ledger', [ReportController::class, 'partyLedger'])->name('reports.party-ledger')->middleware('permission:view party ledger');
    Route::get('/reports/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial-balance')->middleware('permission:view trial balance');
    Route::get('/reports/profit-loss', [ReportController::class, 'profitAndLoss'])->name('reports.profit-loss')->middleware('permission:view profit loss');
    Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet')->middleware('permission:view balance sheet');
    Route::get('/reports/stock-history/{product}', [ReportController::class, 'stockHistory'])->name('reports.stock-history')->middleware('permission:view stock history');
    Route::get('/reports/product-profit-loss/{product}', [ReportController::class, 'productProfitLoss'])->name('reports.product-profit-loss')->middleware('permission:view product profit loss');
    Route::get('/reports/dead-stock', [ReportController::class, 'deadStock'])->name('reports.dead-stock')->middleware('permission:view dead stock');
    Route::get('/reports/stock-alert', [ReportController::class, 'stockAlert'])->name('reports.stock-alert')->middleware('permission:view stock alert');
    Route::get('/reports/products-summary', [ReportController::class, 'productsSummary'])->name('reports.products-summary')->middleware('permission:view products summary');
    Route::get('/reports/aging', [ReportController::class, 'agingReport'])->name('reports.aging')->middleware('permission:view aging report');
    Route::get('/reports/daily-collection', [ReportController::class, 'dailyCollection'])->name('reports.daily-collection')->middleware('permission:view daily collection report');
    Route::get('/reports/expenses', [ReportController::class, 'expenseReport'])->name('reports.expenses')->middleware('permission:view expense report');
    Route::get('/reports/sales-analysis', [ReportController::class, 'salesAnalysis'])->name('reports.sales-analysis')->middleware('permission:view sales analysis report');
    Route::get('/reports/returns-summary', [ReportController::class, 'returnsSummary'])->name('reports.returns-summary')->middleware('permission:view returns summary');
    Route::get('/reports/cargo-charges', [ReportController::class, 'cargoChargesReport'])->name('reports.cargo-charges')->middleware('permission:view cargo charges report');

    // Quick Add Routes (Returning JSON for Alpine.js)
    Route::post('/quick-add-category', function (Request $request) {
        $category = Category::create($request->validate(['name' => 'required|string|max:255']));
        return response()->json($category);
    })->name('categories.quick-add');

    Route::post('/quick-add-brand', function (Request $request) {
        $brand = Brand::create($request->validate(['name' => 'required|string|max:255', 'status' => 'required|boolean']));
        return response()->json($brand);
    })->name('brands.quick-add');

    // Settings
    Route::resource('users', \App\Http\Controllers\UserController::class)->middleware('permission:manage users');
    Route::resource('roles', \App\Http\Controllers\RoleController::class)->middleware('permission:manage roles');
    Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'edit'])->name('settings.edit')->middleware('permission:view settings');
    Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update')->middleware('permission:manage settings');
    Route::post('/settings/clear-cache', [\App\Http\Controllers\SettingController::class, 'clearCache'])->name('settings.clear-cache')->middleware('permission:manage settings');
    Route::get('/settings/backup-download', [\App\Http\Controllers\SettingController::class, 'downloadBackup'])->name('settings.backup-download')->middleware('permission:manage settings');
});

// Public, signed-URL invoice links: no login required, but the URL is cryptographically
// signed (Laravel's `signed` middleware) so only a link generated by the app itself is valid.
// Meant to be shared with customers/vendors directly (e.g. via WhatsApp).
Route::get('/public/sales/{sale}/pdf', [SaleController::class, 'publicPdf'])->name('sales.public-pdf')->middleware('signed');
Route::get('/public/purchases/{purchase}/pdf', [PurchaseController::class, 'publicPdf'])->name('purchases.public-pdf')->middleware('signed');
Route::get('/public/party-ledger/pdf', [ReportController::class, 'publicPartyLedgerPdf'])->name('reports.party-ledger.public-pdf')->middleware('signed');

require __DIR__.'/auth.php';
