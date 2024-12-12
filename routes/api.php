<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\DividendController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\HelpCenterController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\MfaController;
use App\Http\Controllers\LocalizationController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SystemHealthController;
use Illuminate\Support\Facades\Route;

// Localization Routes
Route::get('locales', [LocalizationController::class, 'getLocales']);
Route::post('locales', [LocalizationController::class, 'setLocale']);

Route::middleware(['auth:sanctum'])->group(function () {
    // KYC Routes with rate limiting
    Route::prefix('kyc')->middleware(['api.ratelimit:kyc'])->group(function () {
        Route::post('verify', [KycController::class, 'verify']);
        Route::get('status', [KycController::class, 'status']);
    });

    // MFA Routes with rate limiting
    Route::prefix('mfa')->middleware(['api.ratelimit:otp'])->group(function () {
        Route::post('send-otp', [MfaController::class, 'sendOtp']);
        Route::post('verify-otp', [MfaController::class, 'verifyOtp']);
    });

    // Asset Management Routes with general API rate limiting
    Route::prefix('assets')->middleware(['api.ratelimit:api'])->group(function () {
        Route::get('/', [AssetController::class, 'index']);
        Route::post('/', [AssetController::class, 'store']);
        Route::get('/{asset}', [AssetController::class, 'show']);
        Route::put('/{asset}', [AssetController::class, 'update']);
        Route::delete('/{asset}', [AssetController::class, 'destroy']);
        Route::get('/{asset}/analytics', [AssetController::class, 'analytics']);
        Route::get('/{asset}/dividends', [DividendController::class, 'assetDividends']);
    });

    // Investment Routes with rate limiting
    Route::prefix('investments')->middleware(['api.ratelimit:api'])->group(function () {
        Route::post('assets/{asset}/invest', [InvestmentController::class, 'invest']);
        Route::get('portfolio', [InvestmentController::class, 'portfolio']);
        Route::get('transactions', [InvestmentController::class, 'transactions']);
    });

    // Tax Routes with rate limiting
    Route::prefix('tax')->middleware(['api.ratelimit:api'])->group(function () {
        Route::get('summary/{year}', [TaxController::class, 'annualSummary']);
        Route::get('certificate/{year}', [TaxController::class, 'downloadTaxCertificate']);
    });
});