<?php

use App\Http\Controllers\Api\Expert\BookingController as ExpertBookingController;
use App\Http\Controllers\Api\Expert\ProposalController as ExpertProposalController;
use App\Http\Controllers\Api\User\Cases\CaseBookingController;
use App\Http\Controllers\Api\User\Cases\CaseProposalController;
use App\Http\Middleware\SprintOneApi;
use Illuminate\Support\Facades\Route;

// Sprint 4 (contract: docs/sprint4/CONTRACT.md). Existing /api paths are untouched; no new URI version is introduced.
// No payment, funding or delivery routes exist here: an accepted proposal is `awaiting_payment` and nothing more.

$user = [SprintOneApi::class, 'auth:sanctum', 'regular-user', 'user.verified', 'abilities:user:access'];
$expert = [SprintOneApi::class, 'auth:sanctum', 'expert', 'abilities:expert:access', 'expert.verified'];

Route::prefix('v2/user/cases/{case}')->whereNumber('case')->name('v2.user.cases.')->middleware($user)->group(function (): void {
    Route::get('proposals', [CaseProposalController::class, 'index'])->middleware('throttle:commerce-read')->name('proposals.index');
    Route::get('proposals/{proposal}', [CaseProposalController::class, 'show'])->whereNumber('proposal')->middleware('throttle:commerce-read')->name('proposals.show');
    Route::post('proposals/{proposal}/change-requests', [CaseProposalController::class, 'requestChange'])->whereNumber('proposal')->middleware('throttle:commerce-write')->name('proposals.change-requests.store');
    Route::post('proposals/{proposal}/accept', [CaseProposalController::class, 'accept'])->whereNumber('proposal')->middleware('throttle:commerce-write')->name('proposals.accept');
    Route::post('proposals/{proposal}/reject', [CaseProposalController::class, 'reject'])->whereNumber('proposal')->middleware('throttle:commerce-write')->name('proposals.reject');

    Route::get('agreements', [CaseBookingController::class, 'indexAgreements'])->middleware('throttle:commerce-read')->name('agreements.index');
    Route::get('agreements/{agreement}', [CaseBookingController::class, 'showAgreement'])->whereNumber('agreement')->middleware('throttle:commerce-read')->name('agreements.show');
    Route::post('agreements/{agreement}/cancel', [CaseBookingController::class, 'cancelAgreement'])->whereNumber('agreement')->middleware('throttle:commerce-write')->name('agreements.cancel');
    Route::get('agreements/{agreement}/availability', [CaseBookingController::class, 'availability'])->whereNumber('agreement')->middleware('throttle:commerce-read')->name('agreements.availability');
    Route::get('agreements/{agreement}/bookings', [CaseBookingController::class, 'indexBookings'])->whereNumber('agreement')->middleware('throttle:commerce-read')->name('agreements.bookings.index');
    Route::post('agreements/{agreement}/bookings', [CaseBookingController::class, 'hold'])->whereNumber('agreement')->middleware('throttle:commerce-write')->name('agreements.bookings.store');

    Route::get('bookings/{booking}', [CaseBookingController::class, 'showBooking'])->whereNumber('booking')->middleware('throttle:commerce-read')->name('bookings.show');
    Route::post('bookings/{booking}/confirm', [CaseBookingController::class, 'confirm'])->whereNumber('booking')->middleware('throttle:commerce-write')->name('bookings.confirm');
    Route::post('bookings/{booking}/cancel', [CaseBookingController::class, 'cancelBooking'])->whereNumber('booking')->middleware('throttle:commerce-write')->name('bookings.cancel');
    Route::post('bookings/{booking}/reschedule', [CaseBookingController::class, 'reschedule'])->whereNumber('booking')->middleware('throttle:commerce-write')->name('bookings.reschedule');
});

Route::prefix('expert')->name('expert.')->middleware($expert)->group(function (): void {
    Route::post('invitations/{invitation}/proposal', [ExpertProposalController::class, 'store'])->whereNumber('invitation')->middleware('throttle:commerce-write')->name('invitations.proposal.store');
    Route::get('proposals', [ExpertProposalController::class, 'index'])->middleware('throttle:commerce-read')->name('proposals.index');
    Route::get('proposals/{proposal}', [ExpertProposalController::class, 'show'])->whereNumber('proposal')->middleware('throttle:commerce-read')->name('proposals.show');
    Route::post('proposals/{proposal}/versions', [ExpertProposalController::class, 'revise'])->whereNumber('proposal')->middleware('throttle:commerce-write')->name('proposals.versions.store');
    Route::post('proposals/{proposal}/withdraw', [ExpertProposalController::class, 'withdraw'])->whereNumber('proposal')->middleware('throttle:commerce-write')->name('proposals.withdraw');

    Route::get('agreements', [ExpertBookingController::class, 'indexAgreements'])->middleware('throttle:commerce-read')->name('agreements.index');
    Route::get('agreements/{agreement}', [ExpertBookingController::class, 'showAgreement'])->whereNumber('agreement')->middleware('throttle:commerce-read')->name('agreements.show');
    Route::post('agreements/{agreement}/cancel', [ExpertBookingController::class, 'cancelAgreement'])->whereNumber('agreement')->middleware('throttle:commerce-write')->name('agreements.cancel');

    Route::get('bookings', [ExpertBookingController::class, 'indexBookings'])->middleware('throttle:commerce-read')->name('bookings.index');
    Route::get('bookings/{booking}', [ExpertBookingController::class, 'showBooking'])->whereNumber('booking')->middleware('throttle:commerce-read')->name('bookings.show');
    Route::post('bookings/{booking}/cancel', [ExpertBookingController::class, 'cancelBooking'])->whereNumber('booking')->middleware('throttle:commerce-write')->name('bookings.cancel');
    Route::post('bookings/{booking}/reschedule', [ExpertBookingController::class, 'reschedule'])->whereNumber('booking')->middleware('throttle:commerce-write')->name('bookings.reschedule');
});
