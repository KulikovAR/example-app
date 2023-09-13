<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\ChunkedFileUpload;
use App\Http\Controllers\Auth\AuthSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
| Get csrf token if front on same domain:
| GET|HEAD  sanctum/csrf-cookie
|
*/
Route::post('/login/session', [AuthSessionController::class, 'store'])->name('login.stateful');

Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/logout/session', [AuthSessionController::class, 'destroy'])->name('logout.stateful');
});

Route::get('/', ChunkedFileUpload::class);