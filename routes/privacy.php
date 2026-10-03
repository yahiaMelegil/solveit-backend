<?php

use App\Http\Controllers\Api\Admin\PrivacyOversightController;
use App\Http\Controllers\Api\User\Privacy\ConsentController;
use App\Http\Controllers\Api\User\Privacy\ContextController;
use App\Http\Controllers\Api\User\Privacy\DataRightsRequestController;
use App\Http\Controllers\Api\User\Privacy\PasswordConfirmationController;
use App\Http\Controllers\Api\User\Privacy\PreferenceController;
use App\Http\Controllers\Api\User\Privacy\ProfileController;
use App\Http\Middleware\SprintOneApi;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->name('user.')->middleware([SprintOneApi::class, 'auth:sanctum', 'regular-user', 'user.verified', 'abilities:user:access', 'throttle:privacy-read'])->group(function (): void {
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('preferences', [PreferenceController::class, 'show'])->name('preferences.show');
    Route::get('context-schemas', [ContextController::class, 'schemas'])->name('context-schemas.index');
    Route::get('contexts', [ContextController::class, 'index'])->name('contexts.index');
    Route::get('contexts/{context}', [ContextController::class, 'show'])->whereNumber('context')->name('contexts.show');
    Route::get('contexts/{context}/versions', [ContextController::class, 'versions'])->whereNumber('context')->name('contexts.versions.index');
    Route::get('policies', [ConsentController::class, 'policies'])->name('policies.index');
    Route::get('consents', [ConsentController::class, 'index'])->name('consents.index');
    Route::get('consents/history', [ConsentController::class, 'history'])->name('consents.history');
    Route::get('data-requests', [DataRightsRequestController::class, 'index'])->name('data-requests.index');
    Route::get('data-requests/{dataRequest}', [DataRightsRequestController::class, 'show'])->whereNumber('dataRequest')->name('data-requests.show');
    Route::get('data-requests/{dataRequest}/download', [DataRightsRequestController::class, 'download'])->whereNumber('dataRequest')->middleware('throttle:privacy-download')->name('data-requests.download');

    Route::middleware('throttle:privacy-write')->group(function (): void {
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::patch('preferences', [PreferenceController::class, 'update'])->name('preferences.update');
        Route::post('contexts', [ContextController::class, 'store'])->name('contexts.store');
        Route::patch('contexts/{context}', [ContextController::class, 'update'])->whereNumber('context')->name('contexts.update');
        Route::post('contexts/{context}/archive', [ContextController::class, 'archive'])->whereNumber('context')->name('contexts.archive');
        Route::post('contexts/{context}/restore', [ContextController::class, 'restore'])->whereNumber('context')->name('contexts.restore');
        Route::delete('contexts/{context}', [ContextController::class, 'destroy'])->whereNumber('context')->name('contexts.destroy');
        Route::post('consents', [ConsentController::class, 'store'])->name('consents.store');
        Route::post('data-requests/{dataRequest}/cancel', [DataRightsRequestController::class, 'cancel'])->whereNumber('dataRequest')->name('data-requests.cancel');
    });
    Route::post('security/confirm-password', [PasswordConfirmationController::class, 'store'])->middleware('throttle:privacy-confirm')->name('security.confirm-password');
    Route::post('data-requests', [DataRightsRequestController::class, 'store'])->middleware('throttle:privacy-request')->name('data-requests.store');
});

Route::prefix('admin')->name('admin.')->middleware([SprintOneApi::class, 'auth:sanctum', 'admin', 'abilities:admin:access', 'throttle:privacy-read'])->group(function (): void {
    Route::get('users/{user}/consents', [PrivacyOversightController::class, 'consents'])->whereNumber('user')->middleware('can:users.consentMetadata.view')->name('users.consents.index');
    Route::get('data-requests', [PrivacyOversightController::class, 'index'])->middleware('can:dataRequests.viewAny')->name('data-requests.index');
    Route::get('data-requests/{dataRequest}', [PrivacyOversightController::class, 'show'])->whereNumber('dataRequest')->middleware('can:dataRequests.view')->name('data-requests.show');
});
