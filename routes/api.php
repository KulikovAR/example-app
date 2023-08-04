<?php

use App\Http\Controllers\AssetsController;
use App\Http\Controllers\Auth\AuthProviderController;
use App\Http\Controllers\Auth\AuthTokenController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\VerificationContactController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['guest'])->group(function () {
    Route::post('/registration/email', [RegistrationController::class, 'emailRegistration'])->name('registration');

    Route::post('/login', [AuthTokenController::class, 'store'])->name('login.stateless');

    Route::post('/password/send', [PasswordController::class, 'sendPasswordLink'])->middleware(['throttle:6,1'])->name('password.send');
    Route::post('/password/reset', [PasswordController::class, 'store'])->name('password.reset');

    // Lesson
    Route::prefix('lessons')->group(function () {
        Route::get('/', [LessonController::class, 'index'])->name('lesson.index');
        Route::get('/{lesson_id}', [LessonController::class, 'view'])->name('lesson.view');
    });
});



Route::middleware('auth:sanctum')->group(function () {
    Route::post('/verification/email', [VerificationContactController::class, 'sendEmailVerification'])->name('verification.email.send');

    Route::patch('/password', [PasswordController::class, 'update'])->name('password.update');
    Route::delete('/logout', [AuthTokenController::class, 'destroy'])->name('logout.stateless');

    Route::middleware('verified')->group(function () {
        Route::get('/user_profile', [UserProfileController::class, 'index'])->name('user_profile.index');
        Route::post('/user_profile', [UserProfileController::class, 'store'])->name('user_profile.store');

    });

    // Lesson
    Route::prefix('lessons')->group(function () {
        Route::get('/', [LessonController::class, 'index'])->name('lesson.index');
        Route::get('/{lesson_id}', [LessonController::class, 'view'])->name('lesson.view');
    });
});

Route::get('/auth/{provider}/redirect', [AuthProviderController::class, 'redirectToProvider'])->middleware('throttle:10,1')->name('provider.redirect');
Route::get('/auth/{provider}/callback', [AuthProviderController::class, 'loginOrRegister'])->name('provider.callback');

Route::get('/verification/{id}/{hash}', [VerificationContactController::class, 'verifyEmail'])->middleware(['signed', 'throttle:6,1'])->name('verification.email.url');

Route::get('/assets/{locale?}', [AssetsController::class, 'show'])->name('assets.index');

Route::prefix('payments')->group(function () {
    Route::get('/redirect', [PaymentController::class, 'redirect'])->name('payment.redirect');
    Route::get('/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/failed', [PaymentController::class, 'failed'])->name('payment.failed');

});

