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
use App\Http\Controllers\PosController;
use App\Http\Controllers\QuotationController;
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

Route::get('/downloads/{platform}', [\App\Http\Controllers\AppDownloadController::class, 'download'])->name('downloads.app');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard/quick-stats', [\App\Http\Controllers\DashboardController::class, 'quickStats'])->middleware(['auth', 'verified'])->name('dashboard.quick-stats');

Route::middleware('auth')->group(function () {
    // Offline Mode & Background Sync Routes
    Route::get('/api/offline/ping', [\App\Http\Controllers\OfflineSyncController::class, 'ping'])->name('offline.ping');
    Route::get('/api/offline/master-data', [\App\Http\Controllers\OfflineSyncController::class, 'masterData'])->name('offline.master-data');
    Route::post('/api/offline/sync', [\App\Http\Controllers\OfflineSyncController::class, 'sync'])->name('offline.sync');
    Route::view('/offline', 'offline')->name('offline');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('units', UnitController::class)->middleware('permission:view units');
    Route::resource('categories', CategoryController::class)->middleware('permission:view categories');
    Route::resource('brands', BrandController::class)->middleware('permission:view brands');
    Route::resource('warehouses', WarehouseController::class)->middleware('permission:view warehouses');
    Route::get('/products/print-labels', [ProductController::class, 'printLabels'])->name('products.print-labels')->middleware('permission:print barcodes');
    Route::get('/products/print-barcodes', [ProductController::class, 'printLabels'])->name('products.print_barcodes')->middleware('permission:print barcodes');
    Route::get('/products/{product}/get-barcode', [ProductController::class, 'getBarcode'])->name('products.get-barcode')->middleware('permission:view products');
    Route::get('/products/export/pdf', [ProductController::class, 'exportPdf'])->name('products.export.pdf')->middleware('permission:export data');
    Route::get('/products/export/excel', [ProductController::class, 'exportExcel'])->name('products.export.excel')->middleware('permission:export data');
    Route::resource('products', ProductController::class)->middleware('permission:view products');
    Route::get('/opening-stocks/export/pdf', [OpeningStockController::class, 'exportPdf'])->name('opening-stocks.export.pdf')->middleware('permission:export data');
    Route::get('/opening-stocks/export/excel', [OpeningStockController::class, 'exportExcel'])->name('opening-stocks.export.excel')->middleware('permission:export data');
    Route::resource('opening-stocks', OpeningStockController::class)->middleware('permission:view opening stocks');
    Route::get('/parties/export/pdf', [PartyController::class, 'exportPdf'])->name('parties.export.pdf')->middleware('permission:export data');
    Route::get('/parties/export/excel', [PartyController::class, 'exportExcel'])->name('parties.export.excel')->middleware('permission:export data');
    Route::resource('parties', PartyController::class)->middleware('permission:view parties');
    Route::resource('chart-of-accounts', ChartOfAccountController::class)->middleware('permission:view chart of accounts');
    Route::resource('purchases', PurchaseController::class)->middleware('permission:view purchases');
    Route::post('/purchase-orders/{purchase_order}/receive', [\App\Http\Controllers\PurchaseOrderController::class, 'receiveGoods'])->name('purchase-orders.receive')->middleware('permission:receive purchase orders');
    Route::resource('purchase-orders', \App\Http\Controllers\PurchaseOrderController::class)->middleware('permission:view purchase orders');
    Route::get('/sales/customer-last-price', [SaleController::class, 'customerLastPrice'])->name('sales.customer-last-price');
    Route::resource('sales', SaleController::class)->middleware('permission:view sales');
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index')->middleware('permission:view pos');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store')->middleware('permission:create pos sale');
    Route::post('/pos/quick-add-customer', [PosController::class, 'quickAddCustomer'])->name('pos.quick-add-customer')->middleware('permission:create pos sale');
    Route::get('/quotations/{quotation}/convert', [QuotationController::class, 'convertToSale'])->name('quotations.convert')->middleware('permission:convert quotations');
    Route::post('/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('quotations.status')->middleware('permission:edit quotations');
    Route::get('/quotations/{quotation}/pdf', [QuotationController::class, 'downloadPdf'])->name('quotations.pdf')->middleware('permission:view quotations');
    Route::resource('quotations', QuotationController::class)->middleware('permission:view quotations');
    Route::resource('cargo-charges', CargoChargeController::class)->middleware('permission:view cargo charges');
    Route::get('/payments/party-pending-invoices', [PaymentController::class, 'partyPendingInvoices'])->name('payments.party-pending-invoices')->middleware('permission:view payments');
    Route::resource('payments', PaymentController::class)->middleware('permission:view payments');
    Route::post('cheques/{cheque}/deposit', [\App\Http\Controllers\ChequeController::class, 'deposit'])->name('cheques.deposit')->middleware('permission:manage cheques');
    Route::post('cheques/{cheque}/clear', [\App\Http\Controllers\ChequeController::class, 'clear'])->name('cheques.clear')->middleware('permission:manage cheques');
    Route::post('cheques/{cheque}/bounce', [\App\Http\Controllers\ChequeController::class, 'bounce'])->name('cheques.bounce')->middleware('permission:manage cheques');
    Route::resource('cheques', \App\Http\Controllers\ChequeController::class)->middleware('permission:view cheques');
    Route::resource('account-transfers', AccountTransferController::class)->middleware('permission:view account transfers');
    Route::resource('stock-transfers', StockTransferController::class)->middleware('permission:view stock transfers');
    Route::resource('stock-adjustments', \App\Http\Controllers\StockAdjustmentController::class)->middleware('permission:view stock adjustments');
    Route::resource('sales-returns', \App\Http\Controllers\SalesReturnController::class)->middleware('permission:view sales returns');
    Route::resource('purchase-returns', \App\Http\Controllers\PurchaseReturnController::class)->middleware('permission:view purchase returns');
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class)->middleware('permission:view expenses');
    Route::resource('expense-categories', \App\Http\Controllers\ExpenseCategoryController::class)->middleware('permission:manage expense categories');
    Route::resource('activity-logs', \App\Http\Controllers\ActivityLogController::class)->only(['index', 'show'])->middleware('permission:view activity logs');

    // Mobile Repair Management & Dedicated Catalog CRUD
    Route::resource('repair-services', \App\Http\Controllers\RepairServiceController::class)->middleware('permission:view repair services');
    Route::resource('repair-brands', \App\Http\Controllers\RepairBrandController::class)->middleware('permission:view repair brands');
    Route::post('/repair-brands/{brand}/models', [\App\Http\Controllers\RepairBrandController::class, 'storeModel'])->name('repair-brands.models.store')->middleware('permission:manage repair brands');
    Route::delete('/repair-models/{model}', [\App\Http\Controllers\RepairBrandController::class, 'destroyModel'])->name('repair-models.destroy')->middleware('permission:manage repair brands');

    Route::get('/repair-catalog', [\App\Http\Controllers\RepairController::class, 'catalog'])->name('repairs.catalog')->middleware('permission:manage repair catalog');
    Route::post('/repair-catalog/service', [\App\Http\Controllers\RepairController::class, 'storeService'])->name('repairs.service.store')->middleware('permission:manage repair catalog');
    Route::put('/repair-catalog/service/{service}', [\App\Http\Controllers\RepairController::class, 'updateService'])->name('repairs.service.update')->middleware('permission:manage repair catalog');
    Route::delete('/repair-catalog/service/{service}', [\App\Http\Controllers\RepairController::class, 'destroyService'])->name('repairs.service.destroy')->middleware('permission:manage repair catalog');
    Route::post('/repair-catalog/brand', [\App\Http\Controllers\RepairController::class, 'storeBrand'])->name('repairs.brand.store')->middleware('permission:manage repair catalog');
    Route::delete('/repair-catalog/brand/{brand}', [\App\Http\Controllers\RepairController::class, 'destroyBrand'])->name('repairs.brand.destroy')->middleware('permission:manage repair catalog');
    Route::post('/repair-catalog/model', [\App\Http\Controllers\RepairController::class, 'storeModel'])->name('repairs.model.store')->middleware('permission:manage repair catalog');
    Route::delete('/repair-catalog/model/{model}', [\App\Http\Controllers\RepairController::class, 'destroyModel'])->name('repairs.model.destroy')->middleware('permission:manage repair catalog');
    Route::get('/repairs/{repair}/print-combo', [\App\Http\Controllers\RepairController::class, 'printCombo'])->name('repairs.print-combo')->middleware('permission:print repairs');
    Route::get('/repairs/{repair}/stickers', [\App\Http\Controllers\RepairController::class, 'printStickers'])->name('repairs.stickers')->middleware('permission:print repairs');
    Route::get('/repairs/{repair}/receipt', [\App\Http\Controllers\RepairController::class, 'printReceipt'])->name('repairs.receipt')->middleware('permission:print repairs');
    Route::get('/repairs/{repair}/invoice', [\App\Http\Controllers\RepairController::class, 'printInvoice'])->name('repairs.invoice')->middleware('permission:print repairs');
    Route::post('/repairs/devices/{device}/status', [\App\Http\Controllers\RepairController::class, 'updateDeviceStatus'])->name('repairs.devices.status')->middleware('permission:edit repairs');
    Route::post('/repairs/{repair}/deliver', [\App\Http\Controllers\RepairController::class, 'deliver'])->name('repairs.deliver')->middleware('permission:deliver repairs');
    Route::resource('repairs', \App\Http\Controllers\RepairController::class)->middleware('permission:view repairs');


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
    Route::get('/reports/salesman-performance', [ReportController::class, 'salesmanPerformance'])->name('reports.salesman-performance')->middleware('permission:view sales');

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
    Route::get('/settings/backup-download', [\App\Http\Controllers\SettingController::class, 'downloadBackup'])->name('settings.backup-download')->middleware('permission:download backups');

    // StockPOS AI Intelligence Endpoints
    Route::prefix('ai')->name('ai.')->middleware('permission:access ai assistant')->group(function () {
        Route::post('/chat', [\App\Http\Controllers\AiController::class, 'chat'])->name('chat');
        Route::post('/scan-bill', [\App\Http\Controllers\AiController::class, 'scanBill'])->name('scan-bill');
        Route::post('/create-scanned-products', [\App\Http\Controllers\AiController::class, 'createScannedProducts'])->name('create-scanned-products');
        Route::post('/recovery-message', [\App\Http\Controllers\AiController::class, 'recoveryMessage'])->name('recovery-message');
        Route::get('/daily-briefing', [\App\Http\Controllers\AiController::class, 'dailyBriefing'])->name('daily-briefing');
        Route::get('/usage-stats', [\App\Http\Controllers\AiController::class, 'usageStats'])->name('usage-stats');
    });
});

// Public, signed-URL invoice links: no login required, but the URL is cryptographically
// signed (Laravel's `signed` middleware) so only a link generated by the app itself is valid.
// Meant to be shared with customers/vendors directly (e.g. via WhatsApp).
Route::get('/public/sales/{sale}/pdf', [SaleController::class, 'publicPdf'])->name('sales.public-pdf')->middleware('signed');
Route::get('/public/purchases/{purchase}/pdf', [PurchaseController::class, 'publicPdf'])->name('purchases.public-pdf')->middleware('signed');
Route::get('/public/payments/{payment}/pdf', [PaymentController::class, 'publicPdf'])->name('payments.public-pdf')->middleware('signed');
Route::get('/public/quotations/{quotation}/pdf', [QuotationController::class, 'publicPdf'])->name('quotations.public-pdf')->middleware('signed');
Route::get('/public/party-ledger/pdf', [ReportController::class, 'publicPartyLedgerPdf'])->name('reports.party-ledger.public-pdf')->middleware('signed');

require __DIR__.'/auth.php';
