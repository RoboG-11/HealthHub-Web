<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleLoginController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/error', function () {
    return response()->json([
        'success' => false,
        'message' => 'No hay credenciales de acceso'
    ], 401);
})->name('error');

Route::get('/login-google', [GoogleLoginController::class, 'redirectToGoogle']);
Route::get('/google-callback', [GoogleLoginController::class, 'handleGoogleCallback']);
