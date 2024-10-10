<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\QualificationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserController::class, "store"]);
Route::post('/login', [LoginController::class, "authenticate"]);
Route::post('/qualifications', [QualificationController::class, "store"])->middleware('auth:sanctum');
Route::get('/qualifications', [QualificationController::class, "index"])->middleware('auth:sanctum');


Route::get('/me', fn() => response()->json(["data" => auth()->user()]))
    ->middleware('auth:sanctum');
