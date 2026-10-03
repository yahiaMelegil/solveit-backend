<?php

use App\Http\Controllers\Api\Admin\FinanceController;
use App\Http\Controllers\Api\Expert\StripeAccountController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\User\FundingController;
use App\Http\Middleware\SprintOneApi;
use Illuminate\Support\Facades\Route;

// Sprint 5 (contract: docs/sprint5/CONTRACT.md). Additive; existing /api paths are untouched; no new URI version.
// No Transfer, Payout, or "mark as paid" route exists anywhere: payment truth comes only from Stripe (DR-090).

// Stripe -> SolveIt. No Sanctum: the Stripe-Signature over the raw body is the authentication.
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])->middleware('throttle:payments-webhook')->name('webhooks.stripe');

$user = [SprintOneApi::class, 'auth:sanctum', 'regular-user', 'user.verified', 'abilities:user:access'];
$expert = [SprintOneApi::class, 'auth:sanctum', 'expert', 'abilities:expert:access', 'expert.verified'];

Route::prefix('v2/user/cases/{case}')->whereNumber('case')->name('v2.user.cases.')->middleware($user)->group(function (): void {
    Route::get('agreements/{agreement}/funding', [FundingController::class, 'agreementFunding'])->whereNumber('agreement')->middleware('throttle:payments-read')->name('agreements.funding.show');
    Route::post('agreements/{agreement}/funding', [FundingController::class, 'open'])->whereNumber('agreement')->middleware('throttle:payments-write')->name('agreements.funding.store');
    Route::get('fundings/{funding}', [FundingController::class, 'show'])->whereNumber('funding')->middleware('throttle:payments-read')->name('fundings.show');
    Route::post('fundings/{funding}/checkout', [FundingController::class, 'checkout'])->whereNumber('funding')->middleware('throttle:payments-write')->name('fundings.checkout');
    Route::post('fundings/{funding}/sync', [FundingController::class, 'sync'])->whereNumber('funding')->middleware('throttle:payments-sync')->name('fundings.sync');
    Route::get('fundings/{funding}/documents', [FundingController::class, 'documents'])->whereNumber('funding')->middleware('throttle:payments-read')->name('fundings.documents.index');
    Route::get('fundings/{funding}/documents/{financialDocument}', [FundingController::class, 'document'])->whereNumber(['funding', 'financialDocument'])->middleware('throttle:payments-read')->name('fundings.documents.show');
    Route::post('fundings/{funding}/refund-requests', [FundingController::class, 'requestRefund'])->whereNumber('funding')->middleware('throttle:payments-write')->name('fundings.refund-requests.store');
    Route::get('fundings/{funding}/refunds', [FundingController::class, 'refunds'])->whereNumber('funding')->middleware('throttle:payments-read')->name('fundings.refunds.index');
});

Route::prefix('expert')->name('expert.')->middleware($expert)->group(function (): void {
    Route::get('stripe/account', [StripeAccountController::class, 'show'])->middleware('throttle:payments-read')->name('stripe.account.show');
    Route::post('stripe/onboarding-link', [StripeAccountController::class, 'onboardingLink'])->middleware('throttle:payments-write')->name('stripe.onboarding-link');
    Route::post('stripe/onboarding/return', [StripeAccountController::class, 'onboardingReturn'])->middleware('throttle:payments-sync')->name('stripe.onboarding.return');
    Route::post('stripe/account/sync', [StripeAccountController::class, 'sync'])->middleware('throttle:payments-sync')->name('stripe.account.sync');
    Route::get('agreements/{agreement}/funding', [StripeAccountController::class, 'funding'])->whereNumber('agreement')->middleware('throttle:payments-read')->name('agreements.funding.show');
});

Route::prefix('admin/finance')->name('admin.finance.')->middleware([SprintOneApi::class, 'auth:sanctum', 'admin', 'abilities:admin:access'])->group(function (): void {
    Route::get('fundings', [FinanceController::class, 'fundings'])->middleware('throttle:payments-read')->name('fundings.index');
    Route::get('fundings/{funding}', [FinanceController::class, 'funding'])->whereNumber('funding')->middleware('throttle:payments-read')->name('fundings.show');
    Route::post('fundings/{funding}/refunds', [FinanceController::class, 'createRefund'])->whereNumber('funding')->middleware('throttle:payments-write')->name('fundings.refunds.store');
    Route::post('fundings/{funding}/reconcile', [FinanceController::class, 'reconcile'])->whereNumber('funding')->middleware('throttle:payments-sync')->name('fundings.reconcile');
    Route::get('ledger', [FinanceController::class, 'ledger'])->middleware('throttle:payments-read')->name('ledger.index');
    Route::get('refunds', [FinanceController::class, 'refunds'])->middleware('throttle:payments-read')->name('refunds.index');
    Route::post('refunds/{refund}/decision', [FinanceController::class, 'decide'])->whereNumber('refund')->middleware('throttle:payments-write')->name('refunds.decision');
    Route::get('reconciliation', [FinanceController::class, 'reconciliation'])->middleware('throttle:payments-read')->name('reconciliation.index');
    Route::get('disputes', [FinanceController::class, 'disputes'])->middleware('throttle:payments-read')->name('disputes.index');
    Route::get('stripe-accounts', [FinanceController::class, 'stripeAccounts'])->middleware('throttle:payments-read')->name('stripe-accounts.index');
});
