<?php

use App\Http\Controllers\Api\Admin\ExpertReviewController as AdminExpertReviewController;
use App\Http\Controllers\Api\PublicApi\ExpertReviewController as PublicExpertReviewController;
use App\Http\Controllers\Api\User\ReviewController;
use App\Http\Middleware\SprintOneApi;
use Illuminate\Support\Facades\Route;

// Sprint 4.1 (contract: docs/sprint4/reviews-contract.md). Additive; existing /api paths are untouched; no new URI version.

Route::get('/experts/{profile:slug}/reviews', [PublicExpertReviewController::class, 'index'])
    ->middleware([SprintOneApi::class, 'throttle:public-expert-profile'])->name('experts.reviews.index');

$user = [SprintOneApi::class, 'auth:sanctum', 'regular-user', 'user.verified', 'abilities:user:access'];

Route::prefix('v2/user')->name('v2.user.')->middleware($user)->group(function (): void {
    Route::get('cases/{case}/agreements/{agreement}/review-eligibility', [ReviewController::class, 'eligibility'])->whereNumber(['case', 'agreement'])->middleware('throttle:commerce-read')->name('reviews.eligibility');
    Route::post('cases/{case}/agreements/{agreement}/review', [ReviewController::class, 'store'])->whereNumber(['case', 'agreement'])->middleware('throttle:commerce-write')->name('reviews.store');
    Route::put('reviews/{review}', [ReviewController::class, 'update'])->whereNumber('review')->middleware('throttle:commerce-write')->name('reviews.update');
});

Route::prefix('admin/expert-reviews')->name('admin.expert-reviews.')->middleware([SprintOneApi::class, 'auth:sanctum', 'admin', 'abilities:admin:access', 'throttle:catalog-admin'])->group(function (): void {
    Route::get('', [AdminExpertReviewController::class, 'index'])->name('index');
    Route::post('{review}/moderation', [AdminExpertReviewController::class, 'moderate'])->whereNumber('review')->name('moderate');
});
