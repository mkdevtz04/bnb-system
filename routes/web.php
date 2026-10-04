<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', [ApartmentController::class, 'index'])->name('home');
Route::get('/search', [ApartmentController::class, 'search'])->name('apartments.search');

// Kept so older links and bookmarks still resolve.
Route::redirect('/apartments/search', '/search');

Route::post('/contact', [InquiryController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

/*
|--------------------------------------------------------------------------
| Property pages
|--------------------------------------------------------------------------
|
| Declared after /search so the literal segment is never swallowed by the
| {apartment} wildcard.
*/

Route::get('/apartments/{apartment}', [ApartmentController::class, 'show'])->name('apartments.show');
Route::get('/apartments/{apartment}/quote', [ApartmentController::class, 'quote'])->name('apartments.quote');
Route::get('/apartments/{apartment}/reviews', [ReviewController::class, 'index'])->name('apartments.reviews');

/*
|--------------------------------------------------------------------------
| Signed-in guests
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [ApartmentController::class, 'guestDashboard'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Booking funnel
    Route::get('/apartments/{apartment}/book', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings', [BookingController::class, 'history'])->name('bookings.history');
    Route::get('/bookings/{booking}/confirmation', [BookingController::class, 'confirmation'])->name('bookings.confirmation');
    Route::delete('/bookings/{booking}', [BookingController::class, 'cancel'])->name('bookings.cancel');

    // Reviews
    Route::get('/bookings/{booking}/review', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/bookings/{booking}/review', [ReviewController::class, 'store'])->name('reviews.store');

    // Guest <-> host threads. The inbox serves both roles; which conversations
    // appear is decided by who is asking.
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/bookings/{booking}/chat', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/bookings/{booking}/chat', [MessageController::class, 'store'])->name('messages.store');

    // Notifications
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
    Route::post('/bookings/{booking}/confirm', [AdminController::class, 'confirmBooking'])->name('bookings.confirm');
    Route::post('/bookings/{booking}/cancel', [AdminController::class, 'cancelBooking'])->name('bookings.cancel');

    Route::get('/apartments', [AdminController::class, 'apartments'])->name('apartments');
    Route::get('/apartments/create', [AdminController::class, 'createApartment'])->name('apartments.create');
    Route::post('/apartments', [AdminController::class, 'storeApartment'])->name('apartments.store');
    Route::get('/apartments/{apartment}/edit', [AdminController::class, 'editApartment'])->name('apartments.edit');
    Route::put('/apartments/{apartment}', [AdminController::class, 'updateApartment'])->name('apartments.update');
    Route::delete('/apartments/{apartment}', [AdminController::class, 'destroyApartment'])->name('apartments.destroy');
    Route::patch('/apartments/{apartment}/toggle-status', [AdminController::class, 'toggleStatus'])->name('apartments.toggle-status');
    Route::post('/apartments/{apartment}/block-dates', [AdminController::class, 'blockDates'])->name('apartments.block-dates');
    Route::delete('/blocked-dates/{blockedDate}', [AdminController::class, 'unblockDate'])->name('apartments.unblock-date');
    Route::delete('/apartments/images/{image}', [AdminController::class, 'destroyImage'])->name('apartments.images.destroy');

    Route::get('/users', [AdminController::class, 'users'])->name('users');

    // Contact-form messages. The dashboard counted these long before there was
    // anywhere to read them.
    Route::get('/inquiries', [AdminController::class, 'inquiries'])->name('inquiries');
    Route::post('/inquiries/{inquiry}/read', [AdminController::class, 'markInquiryRead'])->name('inquiries.read');
    Route::post('/inquiries/{inquiry}/reply', [AdminController::class, 'replyToInquiry'])->name('inquiries.reply');

    Route::get('/reports/booked', [AdminController::class, 'bookedReport'])->name('reports.booked');
});

require __DIR__.'/auth.php';
