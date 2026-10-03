<?php

use App\Http\Controllers\Api\Admin\CaseV2OversightController;
use App\Http\Controllers\Api\User\Cases\CaseV2Controller as C;
use App\Http\Middleware\SprintOneApi;
use Illuminate\Support\Facades\Route;

Route::prefix('v2/user/cases')->name('v2.user.cases.')->middleware([SprintOneApi::class, 'auth:sanctum', 'regular-user', 'user.verified', 'abilities:user:access'])->group(function () {
    Route::get('', [C::class, 'index'])->middleware('throttle:cases-read')->name('index');
    Route::post('', [C::class, 'write'])->middleware('throttle:cases-create')->name('store');
    Route::get('{case}', [C::class, 'show'])->whereNumber('case')->middleware('throttle:cases-read')->name('show');
    Route::patch('{case}', [C::class, 'write'])->whereNumber('case')->middleware('throttle:cases-autosave')->name('update');
    Route::get('{case}/readiness', [C::class, 'readiness'])->whereNumber('case')->middleware('throttle:cases-read')->name('readiness');
    Route::put('{case}/scopes', [C::class, 'write'])->whereNumber('case')->middleware('throttle:cases-write')->name('scopes');
    foreach (['assessment', 'confirm', 'submit', 'cancel'] as $action) {
        Route::post('{case}/'.$action, [C::class, 'write'])->whereNumber('case')->middleware('throttle:cases-'.($action === 'submit' ? 'submit' : 'write'))->name($action);
    }
});

Route::prefix('v2/admin/cases')->name('v2.admin.cases.')->middleware([SprintOneApi::class, 'auth:sanctum', 'admin', 'abilities:admin:access', 'throttle:cases-read'])->group(function () {
    Route::get('', [CaseV2OversightController::class, 'index'])->name('index');
    Route::get('{case}', [CaseV2OversightController::class, 'show'])->whereNumber('case')->name('show');
});
