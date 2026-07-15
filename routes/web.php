<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/scorer', function () {
    return view('scorer');
})->name('scorer');

Route::view('/refund-policy', 'pages.policy', ['title' => 'Refund policy'])->name('refund-policy');
Route::view('/privacy-policy', 'pages.policy', ['title' => 'Privacy policy'])->name('privacy-policy');
Route::view('/terms-of-service', 'pages.policy', ['title' => 'Terms of service'])->name('terms-of-service');
Route::view('/contact-information', 'pages.policy', ['title' => 'Contact information'])->name('contact-information');
Route::view('/cookie-preferences', 'pages.policy', ['title' => 'Cookie preferences'])->name('cookie-preferences');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
