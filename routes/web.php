<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::get('/', [HomeController::class, 'home'])->name('home');

// Admin Routes
Route::middleware('auth', 'admin')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::resource('/plans', PlanController::class);
    Route::resource('/users', UserController::class);
    Route::resource('payments', AdminPaymentController::class);

});

Route::post('/purchase', [PaymentController::class, 'purchase'])->name('purchase');
