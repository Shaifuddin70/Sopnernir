<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Admin\InvestmentAccrualController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\InvestmentController as AdminInvestmentController;
use App\Http\Controllers\Admin\InvestmentDocumentController;
use App\Http\Controllers\Admin\InvestmentParticipantController;
use App\Http\Controllers\Investor\InvestmentController as InvestorInvestmentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::redirect('/', '/dashboard');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/portfolio', [InvestorInvestmentController::class, 'index'])->name('investments.index');
    Route::get('/portfolio/{investment}/documents/{document}/download', [InvestmentDocumentController::class, 'download'])
        ->name('investments.documents.download');
    Route::get('/portfolio/{investment}', [InvestorInvestmentController::class, 'show'])->name('investments.show');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('users/import/template', [AdminUserController::class, 'importTemplate'])->name('users.import.template');
    Route::get('users/import', [AdminUserController::class, 'importForm'])->name('users.import');
    Route::post('users/import', [AdminUserController::class, 'importStore'])->name('users.import.store');
    Route::patch('users/{user}/active', [AdminUserController::class, 'toggleActive'])->name('users.active');
    Route::resource('users', AdminUserController::class)->only(['index', 'create', 'store', 'edit', 'update']);

    Route::patch('investments/{investment}/active', [AdminInvestmentController::class, 'toggleActive'])->name('investments.active');
    Route::resource('investments', AdminInvestmentController::class)->except(['destroy']);

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
