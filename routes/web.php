<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [ApartmentController::class, 'index'])->name('home');
Route::get('/apartments/search', [ApartmentController::class, 'search'])->name('apartments.search');
Route::get('/apartments/{apartment}', [ApartmentController::class, 'show'])->name('apartments.show');

// Auth / OTP routes
Route::post('/auth/otp/send', [App\Http\Controllers\OtpController::class, 'send']);
Route::post('/auth/otp/verify', [App\Http\Controllers\OtpController::class, 'verify']);

// Contact / Inquiry
Route::post('/contact', [App\Http\Controllers\InquiryController::class, 'store'])->name('contact.store');

// Authenticated user routes
Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard
    Route::get('/dashboard', [ApartmentController::class, 'guestDashboard'])->name('dashboard');

    // Booking routes
    Route::get('/apartments/{apartment}/book', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}/confirmation', [BookingController::class, 'confirmation'])->name('bookings.confirmation');
    Route::get('/bookings/history', [BookingController::class, 'history'])->name('bookings.history');
    Route::delete('/bookings/{booking}', [BookingController::class, 'cancel'])->name('bookings.cancel');

    // Chat routes
    Route::get('/bookings/{booking}/chat', [App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
    Route::post('/bookings/{booking}/chat', [App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
    Route::post('/bookings/{booking}/confirm', [AdminController::class, 'confirmBooking'])->name('bookings.confirm');
    Route::post('/bookings/{booking}/cancel', [AdminController::class, 'cancelBooking'])->name('bookings.cancel');
    
    // Apartments Management
    Route::get('/apartments', [AdminController::class, 'apartments'])->name('apartments');
    Route::get('/apartments/create', [AdminController::class, 'createApartment'])->name('apartments.create');
    Route::post('/apartments', [AdminController::class, 'storeApartment'])->name('apartments.store');
    Route::get('/apartments/{apartment}/edit', [AdminController::class, 'editApartment'])->name('apartments.edit');
    Route::put('/apartments/{apartment}', [AdminController::class, 'updateApartment'])->name('apartments.update');
    Route::delete('/apartments/{apartment}', [AdminController::class, 'destroyApartment'])->name('apartments.destroy');
    Route::patch('/apartments/{apartment}/toggle-status', [AdminController::class, 'toggleStatus'])->name('apartments.toggle-status');
    Route::post('/apartments/{apartment}/block-dates', [AdminController::class, 'blockDates'])->name('apartments.block-dates');
    Route::delete('/apartments/images/{image}', [AdminController::class, 'destroyImage'])->name('apartments.images.destroy');

    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('users');

    // Reports
    Route::get('/reports/booked', [AdminController::class, 'bookedReport'])->name('reports.booked');
});

require __DIR__.'/auth.php';
