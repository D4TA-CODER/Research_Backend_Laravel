<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StudentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [StudentController::class, 'login']);

Route::get('/login', [StudentController::class, 'login'])->name('login');

Route::post('/login', [StudentController::class, 'processLogin']);

//Route::get('/login', [StudentController::class, 'login']);

Route::get('/logout', [StudentController::class, 'logout']);


Route::get('/register', [StudentController::class, 'showRegister']);

Route::post('/register', [StudentController::class, 'store']);


Route::get('/schedule', [StudentController::class, 'schedule']);


Route::get('/profile', [StudentController::class, 'profile']);


Route::get('/update-profile', [StudentController::class, 'updateProfileForm']);

Route::post('/update-profile', [StudentController::class, 'processUpdateProfile']);



Route::get('/confirm-delete', [StudentController::class, 'confirmDelete']);


Route::get('/delete-profile-step2', [StudentController::class, 'deleteProfileStep2']);


Route::post('/delete-profile', [StudentController::class, 'processDeleteProfile']);