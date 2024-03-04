<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AllergyController;
use App\Http\Controllers\AllergyPatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\DiseasePatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorEstablishmentController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\DoctorSpecialtyController;
use App\Http\Controllers\EstablishmentController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\MedicationPatientController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\SummaryController;
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

Route::post('/login', [UserController::class, 'login']);
Route::post('/users/register', [UserController::class, 'store']);
Route::post('/doctors/register', [DoctorController::class, 'store']);
Route::post('/patients/register', [PatientController::class, 'store']);
Route::get('/doctors_publics', [DoctorController::class, 'doctors_publics']);
Route::get('/specialties_public', [SpecialtyController::class, 'specialties_public']);

Route::middleware('auth:sanctum', 'role:doctor')->group(function () {
    Route::get('/doctor/info', [DoctorController::class, 'info']);
    Route::get('/patients', [PatientController::class, 'index']);
    Route::get('/patients/{id}', [PatientController::class, 'show']);
    Route::put('/doctors', [DoctorController::class, 'update']);
    Route::delete('/doctors', [DoctorController::class, 'destroy']);
    Route::resource('/doctors/specialty', DoctorSpecialtyController::class);
    Route::resource('/establishments', EstablishmentController::class);
    Route::resource('/addresses', AddressController::class);
    Route::resource('/doctors/establishment', DoctorEstablishmentController::class);
    Route::resource('/appointments', AppointmentController::class);
    Route::resource('/medicines', MedicineController::class);
    Route::resource('/summaries', SummaryController::class);
    Route::resource('/schedules', ScheduleController::class);
});

Route::middleware('auth:sanctum', 'role:patient')->group(function () {
    Route::get('/patient/info', [PatientController::class, 'info']);
    // Route::get('/doctors', [DoctorController::class, 'index']);
    Route::get('/doctors/{id}', [DoctorController::class, 'show']);
    Route::put('/patients', [PatientController::class, 'update']);
    Route::delete('/patients', [PatientController::class, 'destroy']);
    Route::resource('/allergies', AllergyController::class);
    Route::resource('/patient/allergies', AllergyPatientController::class);
    Route::resource('/diseases', DiseaseController::class);
    Route::resource('/patient/diseases', DiseasePatientController::class);
    Route::resource('/medications', MedicationController::class);
    Route::resource('/patient/medications', MedicationPatientController::class);
});

// Route::middleware(['auth:sanctum', 'admin'])->group(function () {
//     Route::resource('/users', UserController::class);
//     Route::resource('/patients', PatientController::class);
//     Route::resource('/allergies', AllergyController::class);
//     Route::resource('/patients_allergies', AllergyPatientController::class);
//     Route::resource('/diseases', DiseaseController::class);
//     Route::resource('/diseases_patients', DiseasePatientController::class);
//     Route::resource('/medications', MedicationController::class);
//     Route::resource('/medications_patients', MedicationPatientController::class);
//     Route::resource('/doctors', DoctorController::class);
//     Route::resource('/specialty', SpecialtyController::class);
//     Route::resource('/doctor_specialty', DoctorSpecialtyController::class);
//     Route::resource('/establishments', EstablishmentController::class);
//     Route::resource('/addresses', AddressController::class);
//     Route::resource('/doctor_establishment', DoctorEstablishmentController::class);
//     Route::resource('/appointments', AppointmentController::class);
//     Route::resource('/medicines', MedicineController::class);
//     Route::resource('/summaries', SummaryController::class);
//     Route::resource('/schedules', ScheduleController::class);
// });
