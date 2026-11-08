<?php

use App\Http\Controllers\Admin\InvestmentAccrualController;
use App\Http\Controllers\Admin\InvestmentController as AdminInvestmentController;
use App\Http\Controllers\Admin\InvestmentDocumentController;
use App\Http\Controllers\Admin\InvestmentParticipantController;
use App\Http\Controllers\Admin\MonthlyPaymentController;
use App\Http\Controllers\Admin\NoticeController as AdminNoticeController;
use App\Http\Controllers\Admin\SpecialInstallmentController;
use App\Http\Controllers\Admin\ProfitWithdrawalController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Investor\InvestmentController as InvestorInvestmentController;
use App\Http\Controllers\Investor\NoticeController as InvestorNoticeController;
use App\Http\Controllers\Investor\PaymentController as InvestorPaymentController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PhoneAccess\InvestmentLookupController as PhoneAccessInvestmentLookupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ViteBuildAssetController;
use Illuminate\Support\Facades\Route;

Route::get('/build/{path}', [ViteBuildAssetController::class, 'show'])
    ->where('path', '.*')
    ->name('vite-build-asset');

Route::get('/_vite/{path}', [ViteBuildAssetController::class, 'show'])
    ->where('path', '.*')
    ->name('vite-build-asset-app');

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::redirect('/', '/dashboard');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/payments', [InvestorPaymentController::class, 'index'])->name('payments.index');
    Route::get('/portfolio', [InvestorInvestmentController::class, 'index'])->name('investments.index');
    Route::get('/portfolio/investments', [InvestorInvestmentController::class, 'all'])->name('investments.all');
    Route::get('/portfolio/{investment}/documents/{document}/download', [InvestmentDocumentController::class, 'download'])
        ->name('investments.documents.download');
    Route::get('/portfolio/{investment}', [InvestorInvestmentController::class, 'show'])->name('investments.show');

    Route::get('/notices', [InvestorNoticeController::class, 'index'])->name('notices.index');
    Route::get('/notices/{notice}', [InvestorNoticeController::class, 'show'])->name('notices.show');
    Route::get('/notices/{notice}/attachments/{attachment}/download', [InvestorNoticeController::class, 'downloadAttachment'])
        ->name('notices.attachments.download');
});

Route::middleware(['guest'])->group(function () {
    Route::post('/phone-access', [PhoneAccessInvestmentLookupController::class, 'store'])->name('phone-access.store');
});

Route::middleware('phone.access')->prefix('phone-access')->name('phone-access.')->group(function () {
    Route::get('/investments', [PhoneAccessInvestmentLookupController::class, 'index'])->name('investments.index');
    Route::get('/investments/all', [PhoneAccessInvestmentLookupController::class, 'all'])->name('investments.all');
    Route::get('/investments/{investment}', [PhoneAccessInvestmentLookupController::class, 'show'])->name('investments.show');
    Route::post('/logout', [PhoneAccessInvestmentLookupController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('users/import/template', [AdminUserController::class, 'importTemplate'])->name('users.import.template');
    Route::get('users/import', [AdminUserController::class, 'importForm'])->name('users.import');
    Route::post('users/import', [AdminUserController::class, 'importStore'])->name('users.import.store');
    Route::patch('users/{user}/active', [AdminUserController::class, 'toggleActive'])->name('users.active');
    Route::patch('users/{user}/password', [AdminUserController::class, 'resetPassword'])->name('users.password.reset');
    Route::resource('users', AdminUserController::class)->only(['index', 'create', 'store', 'edit', 'update']);

    Route::get('monthly-payments', [MonthlyPaymentController::class, 'index'])->name('monthly-payments.index');
    Route::patch('monthly-payments/amount', [MonthlyPaymentController::class, 'updateAmount'])->name('monthly-payments.amount');
    Route::patch('monthly-payments', [MonthlyPaymentController::class, 'update'])->name('monthly-payments.update');
    Route::patch('monthly-payments/bulk', [MonthlyPaymentController::class, 'bulkUpdate'])->name('monthly-payments.bulk-update');

    Route::get('special-installments', [SpecialInstallmentController::class, 'index'])->name('special-installments.index');
    Route::post('special-installments', [SpecialInstallmentController::class, 'store'])->name('special-installments.store');
    Route::get('special-installments/{specialInstallment}', [SpecialInstallmentController::class, 'show'])->name('special-installments.show');
    Route::patch('special-installments/{specialInstallment}', [SpecialInstallmentController::class, 'update'])->name('special-installments.update');
    Route::patch('special-installments/{specialInstallment}/bulk', [SpecialInstallmentController::class, 'bulkUpdate'])->name('special-installments.bulk-update');

    Route::get('notices', [AdminNoticeController::class, 'index'])->name('notices.index');
    Route::get('notices/create', [AdminNoticeController::class, 'create'])->name('notices.create');
    Route::post('notices', [AdminNoticeController::class, 'store'])->name('notices.store');
    Route::get('notices/{notice}/edit', [AdminNoticeController::class, 'edit'])->name('notices.edit');
    Route::put('notices/{notice}', [AdminNoticeController::class, 'update'])->name('notices.update');
    Route::delete('notices/{notice}', [AdminNoticeController::class, 'destroy'])->name('notices.destroy');
    Route::post('notices/{notice}/publish', [AdminNoticeController::class, 'publish'])->name('notices.publish');
    Route::post('notices/{notice}/unpublish', [AdminNoticeController::class, 'unpublish'])->name('notices.unpublish');
    Route::post('notices/{notice}/resend', [AdminNoticeController::class, 'resend'])->name('notices.resend');
    Route::delete('notices/{notice}/attachments/{attachment}', [AdminNoticeController::class, 'destroyAttachment'])
        ->name('notices.attachments.destroy');
    Route::get('notices/{notice}/attachments/{attachment}/download', [AdminNoticeController::class, 'downloadAttachment'])
        ->name('notices.attachments.download');

    Route::patch('investments/{investment}/active', [AdminInvestmentController::class, 'toggleActive'])->name('investments.active');
    Route::resource('investments', AdminInvestmentController::class)->except(['destroy']);

    Route::get('profit-withdrawals', [ProfitWithdrawalController::class, 'index'])
        ->name('profit-withdrawals.index');
    Route::get('profit-withdrawals/{batch}', [ProfitWithdrawalController::class, 'show'])
        ->name('profit-withdrawals.show');
    Route::delete('profit-withdrawals/{batch}', [ProfitWithdrawalController::class, 'destroy'])
        ->name('profit-withdrawals.destroy');
    Route::get('investments-profit-withdrawals/preview', [ProfitWithdrawalController::class, 'preview'])
        ->name('investments.profit-withdrawals.preview');
    Route::post('investments/profit-withdrawals/bulk', [ProfitWithdrawalController::class, 'bulkStore'])
        ->name('investments.profit-withdrawals.bulk');
    Route::post('investments/{investment}/profit-withdrawals', [ProfitWithdrawalController::class, 'store'])
        ->name('investments.profit-withdrawals.store');
    Route::delete('investments/{investment}/profit-withdrawals/{withdrawal}', [ProfitWithdrawalController::class, 'destroyWithdrawal'])
        ->name('investments.profit-withdrawals.destroy');

    Route::post('investments/{investment}/documents', [InvestmentDocumentController::class, 'store'])
        ->name('investments.documents.store');
    Route::delete('investments/{investment}/documents/{document}', [InvestmentDocumentController::class, 'destroy'])
        ->name('investments.documents.destroy');
    Route::get('investments/{investment}/documents/{document}/download', [InvestmentDocumentController::class, 'download'])
        ->name('investments.documents.download');

    Route::post('investments/{investment}/participants', [InvestmentParticipantController::class, 'store'])
        ->name('investments.participants.store');
    Route::post('investments/{investment}/participants/tag-all', [InvestmentParticipantController::class, 'tagAllInvestors'])
        ->name('investments.participants.tag-all');
    Route::patch('investments/{investment}/participants/{participant}', [InvestmentParticipantController::class, 'update'])
        ->name('investments.participants.update');
    Route::delete('investments/{investment}/participants/{participant}', [InvestmentParticipantController::class, 'destroy'])
        ->name('investments.participants.destroy');

    Route::post('investments/{investment}/accruals/fill-missing', [InvestmentAccrualController::class, 'fillMissing'])
        ->name('investments.accruals.fill-missing');
    Route::post('investments/{investment}/accruals', [InvestmentAccrualController::class, 'store'])
        ->name('investments.accruals.store');
});

require __DIR__.'/auth.php';
