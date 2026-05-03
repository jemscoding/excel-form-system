<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DepositingBankController;
use App\Http\Controllers\ExcelFormController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::resource('/products', ProductController::class);
    Route::resource('/clients', ClientController::class);
    Route::resource('/payment-methods', PaymentMethodController::class);
    Route::resource('/agents', AgentController::class);
    Route::resource('/depositing-banks', DepositingBankController::class);

    Route::controller(ExcelFormController::class)->group(function () {
    Route::get('excel-forms', 'index')->name('excel-forms.index');
    Route::get('excel-forms/create', 'create')->name('excel-forms.create');
    Route::post('excel-forms', 'store')->name('excel-forms.store');
    Route::get('excel-forms/download', 'download')->name('excel-forms.download');
});
});

require __DIR__.'/settings.php';
