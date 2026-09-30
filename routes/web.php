<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrameController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LensController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('home') : redirect()->route('login');
});

Route::get('/home', function () {
    $user = Auth::user();

    if ($user->isCustomer()) {
        return redirect()->route('portal.index');
    }

    return redirect()->route('dashboard');
})->name('home')->middleware('auth');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/login/wa', [AuthController::class, 'showOtpLogin'])->name('login.otp');
    Route::post('/login/wa', [AuthController::class, 'sendOtp'])->name('login.otp.send');
    Route::get('/login/wa/verify', [AuthController::class, 'showVerify'])->name('login.otp.verify');
    Route::post('/login/wa/verify', [AuthController::class, 'verifyOtp'])->name('login.otp.check');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Admin & Staff
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/pos', [PosController::class, 'create'])->name('pos.create');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
    Route::post('/pos/customers', [PosController::class, 'quickCustomer'])->name('pos.customers');

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

    Route::get('/prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::get('/prescriptions/create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::post('/prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');
    Route::get('/prescriptions/{prescription}/edit', [PrescriptionController::class, 'edit'])->name('prescriptions.edit');
    Route::put('/prescriptions/{prescription}', [PrescriptionController::class, 'update'])->name('prescriptions.update');
    Route::get('/prescriptions/{prescription}', [PrescriptionController::class, 'show'])->name('prescriptions.show');

    Route::get('/transactions/create', [PosController::class, 'create'])->name('transactions.create');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::patch('/transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.status');
    Route::get('/transactions/{transaction}/print', [TransactionController::class, 'print'])->name('transactions.print');
});

/*
|--------------------------------------------------------------------------
| Admin only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/accessories/create', [ItemController::class, 'createAccessory'])->name('items.accessories.create');
    Route::post('/items/accessories', [ItemController::class, 'storeAccessory'])->name('items.accessories.store');
    Route::get('/items/accessories/{accessory}/edit', [ItemController::class, 'editAccessory'])->name('items.accessories.edit');
    Route::put('/items/accessories/{accessory}', [ItemController::class, 'updateAccessory'])->name('items.accessories.update');
    Route::delete('/items/accessories/{accessory}', [ItemController::class, 'destroyAccessory'])->name('items.accessories.destroy');

    Route::resource('frames', FrameController::class)->except(['show']);
    Route::resource('lenses', LensController::class)->except(['show']);

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::resource('promotions', PromotionController::class)->except(['show']);

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
});

/*
|--------------------------------------------------------------------------
| Customer Portal
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:customer'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [PortalController::class, 'dashboard'])->name('index');
    Route::get('/transactions', [PortalController::class, 'transactions'])->name('transactions');
    Route::get('/transactions/{transaction}', [PortalController::class, 'transactionShow'])->name('transactions.show');
    Route::get('/promos', [PortalController::class, 'promos'])->name('promos');
});
