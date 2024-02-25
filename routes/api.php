<?php

use App\Http\Controllers\AllergyController;
use App\Http\Controllers\AllergyPatientController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\DiseasePatientController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\MedicationPatientController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::resource('/users', UserController::class);
Route::resource('/patients', PatientController::class);
Route::resource('/allergies', AllergyController::class);
Route::resource('/patients_allergies', AllergyPatientController::class);
Route::resource('/diseases', DiseaseController::class);
Route::resource('/diseases_patients', DiseasePatientController::class);
Route::resource('/medications', MedicationController::class);
Route::resource('/medications_patients', MedicationPatientController::class);

// Route::middleware('auth:sanctum')->get('/test', function (Request $request) {
//     return $request->user();
// });

// Route::post('/patients/login', [PatientController::class, 'login']);

// TEST: Google
// use Laravel\Socialite\Facades\Socialite;
// use App\Http\Controllers\GoogleLoginController;

// Route::get('/login-google', [GoogleLoginController::class, 'redirectToGoogle']);
// Route::get('/google-callback', [GoogleLoginController::class, 'handleGoogleCallback']);

// Route::group(['middleware' => ['web']], function () {
//     Route::get('/login-google', [GoogleLoginController::class, 'redirectToGoogle']);
//     Route::get('/google-callback', [GoogleLoginController::class, 'handleGoogleCallback']);
// });
