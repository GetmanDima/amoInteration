<?php

use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use App\Http\Controllers\AmoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
   return redirect('/amo/dashboard');
});
Route::get('/amo/auth', [AmoController::class, 'authForm']);
Route::post('/amo/auth', [AmoController::class, 'auth']);
Route::get('/amo/dashboard', [AmoController::class, 'dashboard'])
    ->middleware('amo.auth')
    ->name('dashboard');
Route::post('/amo/data', [AmoController::class, 'updateData'])->middleware('amo.auth');
