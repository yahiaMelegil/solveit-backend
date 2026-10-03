<?php

use App\Http\Controllers\Api\Catalog\CatalogController as C;
use App\Http\Middleware\SprintOneApi;
use Illuminate\Support\Facades\Route;

Route::prefix('catalog')->name('catalog.')->middleware([SprintOneApi::class, 'throttle:catalog-read'])->group(function () {
    foreach (['countries' => 'country', 'jurisdictions' => 'jurisdiction', 'domains' => 'domain', 'specialties' => 'specialty', 'services' => 'service', 'delivery-modes' => 'delivery_mode'] as $path => $kind) {
        Route::get($path, [C::class, 'nodes'])->defaults('kind', $kind)->name($path);
    }
    Route::get('entries', [C::class, 'entries'])->name('entries');
    Route::get('intake-schema/{entry}', [C::class, 'schema'])->whereNumber('entry')->name('schema');
});
Route::prefix('admin/catalog')->name('admin.catalog.')->middleware([SprintOneApi::class, 'auth:sanctum', 'admin', 'abilities:admin:access', 'throttle:catalog-admin'])->group(function () {
    Route::get('nodes', [C::class, 'nodes'])->name('nodes');
    Route::post('nodes', [C::class, 'write'])->name('node');
    Route::patch('nodes/{node}', [C::class, 'write'])->whereNumber('node')->name('updateNode');
    Route::get('entries', [C::class, 'entries'])->name('entries');
    Route::post('entries', [C::class, 'write'])->name('store');
    Route::get('entries/{entry}', [C::class, 'detail'])->whereNumber('entry')->name('detail');
    Route::post('entries/{entry}/versions', [C::class, 'write'])->whereNumber('entry')->name('version');
    Route::get('versions/{version}/impact', [C::class, 'impact'])->whereNumber('version')->name('impact');
    foreach (['review', 'publish', 'pause'] as $a) {
        Route::post('versions/{version}/'.$a, [C::class, 'write'])->whereNumber('version')->name($a);
    }
    Route::get('grants', [C::class, 'grants'])->name('grants');
    Route::post('grants', [C::class, 'write'])->name('grant');
    Route::post('grants/{grant}/revoke', [C::class, 'write'])->whereNumber('grant')->name('revoke');
    Route::get('waiting-cases', [C::class, 'waiting'])->name('waiting');
});
