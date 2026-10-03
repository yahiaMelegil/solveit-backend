<?php

use App\Http\Controllers\Api\Expert\InvitationController as ExpertInvitationController;
use App\Http\Controllers\Api\Expert\OfferingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\User\Cases\AiQuestionController;
use App\Http\Controllers\Api\User\Cases\CaseInvitationController;
use App\Http\Controllers\Api\User\Cases\CaseMatchingController;
use App\Http\Middleware\SprintOneApi;
use Illuminate\Support\Facades\Route;

// Sprint 3 (contract: docs/sprint3/CONTRACT.md). Existing /api paths are untouched; no new URI version is introduced.

$user = [SprintOneApi::class, 'auth:sanctum', 'regular-user', 'user.verified', 'abilities:user:access'];
$expert = [SprintOneApi::class, 'auth:sanctum', 'expert', 'abilities:expert:access', 'expert.verified'];

Route::prefix('v2/user/cases/{case}')->whereNumber('case')->name('v2.user.cases.')->middleware($user)->group(function (): void {
    Route::get('ai-questions', [AiQuestionController::class, 'index'])->middleware('throttle:cases-read')->name('ai-questions.index');
    Route::post('ai-questions', [AiQuestionController::class, 'store'])->middleware('throttle:ai-intake')->name('ai-questions.store');
    Route::put('ai-questions/{set}/responses', [AiQuestionController::class, 'respond'])->whereNumber('set')->middleware('throttle:cases-write')->name('ai-questions.respond');

    Route::prefix('scopes/{scope}')->whereNumber('scope')->group(function (): void {
        Route::get('matching-runs', [CaseMatchingController::class, 'indexRuns'])->middleware('throttle:matching-read')->name('matching-runs.index');
        Route::post('matching-runs', [CaseMatchingController::class, 'storeRun'])->middleware('throttle:matching-write')->name('matching-runs.store');
        Route::get('matching-runs/{run}', [CaseMatchingController::class, 'showRun'])->whereNumber('run')->middleware('throttle:matching-read')->name('matching-runs.show');
        Route::post('briefs/preview', [CaseMatchingController::class, 'previewBrief'])->middleware('throttle:matching-write')->name('briefs.preview');
        Route::post('briefs', [CaseMatchingController::class, 'storeBrief'])->middleware('throttle:matching-write')->name('briefs.store');
        Route::get('briefs', [CaseMatchingController::class, 'indexBriefs'])->middleware('throttle:matching-read')->name('briefs.index');
        Route::get('briefs/{brief}', [CaseMatchingController::class, 'showBrief'])->whereNumber('brief')->middleware('throttle:matching-read')->name('briefs.show');
    });

    Route::get('invitations', [CaseInvitationController::class, 'index'])->middleware('throttle:matching-read')->name('invitations.index');
    Route::post('invitations', [CaseInvitationController::class, 'store'])->middleware('throttle:matching-write')->name('invitations.store');
    Route::get('invitations/{invitation}', [CaseInvitationController::class, 'show'])->whereNumber('invitation')->middleware('throttle:matching-read')->name('invitations.show');
    Route::post('invitations/{invitation}/revoke', [CaseInvitationController::class, 'revoke'])->whereNumber('invitation')->middleware('throttle:matching-write')->name('invitations.revoke');
    Route::get('invitations/{invitation}/document-grants', [CaseInvitationController::class, 'indexGrants'])->whereNumber('invitation')->middleware('throttle:matching-read')->name('document-grants.index');
    Route::post('invitations/{invitation}/document-grants', [CaseInvitationController::class, 'grantDocuments'])->whereNumber('invitation')->middleware('throttle:matching-write')->name('document-grants.store');
    Route::post('invitations/{invitation}/document-grants/{grant}/revoke', [CaseInvitationController::class, 'revokeGrant'])->whereNumber(['invitation', 'grant'])->middleware('throttle:matching-write')->name('document-grants.revoke');
});

Route::prefix('user/notifications')->name('user.notifications.')->middleware($user)->group(function (): void {
    Route::get('', [NotificationController::class, 'index'])->middleware('throttle:matching-read')->name('index');
    Route::post('{notification}/read', [NotificationController::class, 'read'])->middleware('throttle:matching-write')->name('read');
});

Route::prefix('expert')->name('expert.')->middleware($expert)->group(function (): void {
    Route::get('offerings/eligible-services', [OfferingController::class, 'eligibleServices'])->middleware('throttle:matching-read')->name('offerings.eligible-services');
    Route::get('offerings', [OfferingController::class, 'index'])->middleware('throttle:matching-read')->name('offerings.index');
    Route::post('offerings', [OfferingController::class, 'store'])->middleware('throttle:matching-write')->name('offerings.store');
    Route::get('offerings/{offering}', [OfferingController::class, 'show'])->whereNumber('offering')->middleware('throttle:matching-read')->name('offerings.show');
    Route::put('offerings/{offering}', [OfferingController::class, 'update'])->whereNumber('offering')->middleware('throttle:matching-write')->name('offerings.update');
    Route::post('offerings/{offering}/publish', [OfferingController::class, 'publish'])->whereNumber('offering')->middleware('throttle:matching-write')->name('offerings.publish');
    Route::post('offerings/{offering}/pause', [OfferingController::class, 'pause'])->whereNumber('offering')->middleware('throttle:matching-write')->name('offerings.pause');

    Route::get('invitations', [ExpertInvitationController::class, 'index'])->middleware('throttle:matching-read')->name('invitations.index');
    Route::get('invitations/{invitation}', [ExpertInvitationController::class, 'show'])->whereNumber('invitation')->middleware('throttle:matching-read')->name('invitations.show');
    Route::post('invitations/{invitation}/accept', [ExpertInvitationController::class, 'accept'])->whereNumber('invitation')->middleware('throttle:matching-write')->name('invitations.accept');
    Route::post('invitations/{invitation}/decline', [ExpertInvitationController::class, 'decline'])->whereNumber('invitation')->middleware('throttle:matching-write')->name('invitations.decline');
    Route::get('invitations/{invitation}/documents', [ExpertInvitationController::class, 'documents'])->whereNumber('invitation')->middleware('throttle:matching-read')->name('invitations.documents');
    Route::get('invitations/{invitation}/documents/{grant}/download', [ExpertInvitationController::class, 'download'])->whereNumber(['invitation', 'grant'])->middleware('throttle:matching-download')->name('invitations.documents.download');

    Route::get('notifications', [NotificationController::class, 'index'])->middleware('throttle:matching-read')->name('notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->middleware('throttle:matching-write')->name('notifications.read');
});
