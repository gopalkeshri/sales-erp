<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\OpportunityController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\TerritoryController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| Sales ERP API Routes
|--------------------------------------------------------------------------
*/

// 1. Authentication Endpoints
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('api.auth.refresh');
        Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::put('/password', [AuthController::class, 'updatePassword'])->name('api.auth.password');
    });
});

// Authenticated ERP Modules
Route::middleware(['auth:sanctum,web', 'activity.log'])->group(function () {

    // 2. Leads Module
    Route::prefix('leads')->group(function () {
        Route::get('/', [LeadController::class, 'index']);
        Route::post('/', [LeadController::class, 'store']);
        Route::get('/{id}', [LeadController::class, 'show']);
        Route::put('/{id}', [LeadController::class, 'update']);
        Route::delete('/{id}', [LeadController::class, 'destroy']);
        Route::post('/{id}/convert', [LeadController::class, 'convert']);
        Route::get('/{id}/activities', [LeadController::class, 'activities']);
        Route::post('/{id}/activities', [LeadController::class, 'addActivity']);
    });

    // 3. Opportunities Module
    Route::prefix('opportunities')->group(function () {
        Route::get('/pipeline', [OpportunityController::class, 'pipeline']);
        Route::get('/', [OpportunityController::class, 'index']);
        Route::post('/', [OpportunityController::class, 'store']);
        Route::get('/{id}', [OpportunityController::class, 'show']);
        Route::put('/{id}', [OpportunityController::class, 'update']);
        Route::delete('/{id}', [OpportunityController::class, 'destroy']);
        Route::put('/{id}/stage', [OpportunityController::class, 'updateStage']);
        Route::get('/{id}/activities', [OpportunityController::class, 'activities']);
        Route::post('/{id}/activities', [OpportunityController::class, 'addActivity']);
    });

    // 4. Customers Module
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/{id}', [CustomerController::class, 'show']);
        Route::put('/{id}', [CustomerController::class, 'update']);
        Route::delete('/{id}', [CustomerController::class, 'destroy']);
        Route::get('/{id}/contacts', [CustomerController::class, 'contacts']);
        Route::get('/{id}/orders', [CustomerController::class, 'orders']);
        Route::get('/{id}/invoices', [CustomerController::class, 'invoices']);
    });

    // 5. Contacts Module
    Route::prefix('contacts')->group(function () {
        Route::get('/', [ContactController::class, 'index']);
        Route::post('/', [ContactController::class, 'store']);
        Route::get('/{id}', [ContactController::class, 'show']);
        Route::put('/{id}', [ContactController::class, 'update']);
        Route::delete('/{id}', [ContactController::class, 'destroy']);
    });

    // 6. Quotes Module
    Route::prefix('quotes')->group(function () {
        Route::get('/', [QuoteController::class, 'index']);
        Route::post('/', [QuoteController::class, 'store']);
        Route::get('/{id}', [QuoteController::class, 'show']);
        Route::put('/{id}', [QuoteController::class, 'update']);
        Route::delete('/{id}', [QuoteController::class, 'destroy']);
        Route::post('/{id}/send', [QuoteController::class, 'send']);
        Route::post('/{id}/accept', [QuoteController::class, 'accept']);
        Route::post('/{id}/reject', [QuoteController::class, 'reject']);
        Route::post('/{id}/convert', [QuoteController::class, 'convertToOrder']);
        Route::get('/{id}/pdf', [QuoteController::class, 'pdfData']);
    });

    // 7. Orders Module
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::put('/{id}', [OrderController::class, 'update']);
        Route::delete('/{id}', [OrderController::class, 'destroy']);
        Route::put('/{id}/status', [OrderController::class, 'updateStatus']);
        Route::get('/{id}/invoice', [OrderController::class, 'generateInvoice']);
        Route::post('/{id}/invoice', [OrderController::class, 'generateInvoice']);
    });

    // 8. Products Module
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
    });

    // 9. Inventory Module
    Route::prefix('inventory')->group(function () {
        Route::get('/low-stock', [InventoryController::class, 'lowStock']);
        Route::get('/warehouses', [InventoryController::class, 'warehouses']);
        Route::post('/transfer', [InventoryController::class, 'transfer']);
        Route::get('/', [InventoryController::class, 'index']);
        Route::post('/', [InventoryController::class, 'stockIn']);
        Route::post('/stock-in', [InventoryController::class, 'stockIn']);
        Route::put('/{id}', [InventoryController::class, 'update']);
    });

    // 10. Invoices Module
    Route::prefix('invoices')->group(function () {
        Route::get('/overdue', [InvoiceController::class, 'overdue']);
        Route::get('/', [InvoiceController::class, 'index']);
        Route::post('/', [InvoiceController::class, 'store']);
        Route::get('/{id}', [InvoiceController::class, 'show']);
        Route::put('/{id}', [InvoiceController::class, 'update']);
        Route::delete('/{id}', [InvoiceController::class, 'destroy']);
        Route::post('/{id}/send', [InvoiceController::class, 'send']);
        Route::post('/{id}/payment', [InvoiceController::class, 'recordPayment']);
        Route::get('/{id}/pdf', [InvoiceController::class, 'pdfData']);
    });

    // 11. Commissions Module
    Route::prefix('commissions')->group(function () {
        Route::get('/summary', [CommissionController::class, 'summary']);
        Route::post('/calculate', [CommissionController::class, 'calculate']);
        Route::get('/', [CommissionController::class, 'index']);
        Route::get('/{id}', [CommissionController::class, 'show']);
        Route::put('/{id}/approve', [CommissionController::class, 'approve']);
        Route::put('/{id}/pay', [CommissionController::class, 'pay']);
        Route::post('/{id}/adjust', [CommissionController::class, 'addAdjustment']);
    });

    // 12. Territories Module
    Route::prefix('territories')->group(function () {
        Route::get('/', [TerritoryController::class, 'index']);
        Route::post('/', [TerritoryController::class, 'store']);
        Route::get('/{id}', [TerritoryController::class, 'show']);
        Route::put('/{id}', [TerritoryController::class, 'update']);
        Route::delete('/{id}', [TerritoryController::class, 'destroy']);
        Route::get('/{id}/users', [TerritoryController::class, 'users']);
    });

    // Teams Module
    Route::prefix('teams')->group(function () {
        Route::get('/', [TeamController::class, 'index']);
        Route::post('/', [TeamController::class, 'store']);
        Route::get('/{id}', [TeamController::class, 'show']);
        Route::put('/{id}', [TeamController::class, 'update']);
        Route::delete('/{id}', [TeamController::class, 'destroy']);
    });

    // 13. Reports Module
    Route::prefix('reports')->group(function () {
        Route::get('/sales-summary', [ReportController::class, 'salesSummary']);
        Route::get('/pipeline', [ReportController::class, 'pipeline']);
        Route::get('/forecast', [ReportController::class, 'forecast']);
        Route::get('/top-performers', [ReportController::class, 'topPerformers']);
        Route::get('/territory-performance', [ReportController::class, 'territoryPerformance']);
        Route::get('/product-performance', [ReportController::class, 'productPerformance']);
        Route::get('/revenue-trends', [ReportController::class, 'revenueTrends']);
    });

    // 14. Dashboard Module
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/revenue-chart', [DashboardController::class, 'revenueChart']);
        Route::get('/pipeline-chart', [DashboardController::class, 'pipelineChart']);
        Route::get('/activities', [DashboardController::class, 'activities']);
        Route::get('/tasks', [DashboardController::class, 'tasks']);
    });
});
