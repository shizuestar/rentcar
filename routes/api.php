<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;

Route::middleware(['auth:sanctum'])->group(function(){
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user:id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::patch('/users/{user:id}/update', [UserController::class, 'update']);
    Route::delete('/users/{user:id}/delete', [UserController::class, 'destroy']);

    Route::get('/cars', [CarController::class, 'index']);
    Route::post('/cars', [CarController::class, 'store']);
    Route::get('/cars/{car:id}', [CarController::class, 'show']);
    Route::patch('/cars/{car:id}/update', [CarController::class, 'update']);
    Route::delete('/cars/{car:id}/delete', [CarController::class, 'destroy']);

    Route::get('/transactions', [TransactionController::class, "index"]);
    Route::post('/transactions', [TransactionController::class, "store"]);
    Route::get('/transactions/{transaction:id}', [TransactionController::class, "show"]);
    Route::post('/transactions/{transaction:id}/pay', [TransactionController::class, "payTransaction"]);
    Route::patch('/transactions/{transaction:id}/completed', [TransactionController::class, "completeTransaction"]);
    Route::patch('/transactions/{transaction:id}/verifyPayment', [TransactionController::class, 'verifyPayment']);

    Route::get("/reviews", [ReviewController::class, "index"]);
    Route::post("/cars/{car:id}/review", [ReviewController::class, "store"]);

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('/login', [AuthController::class, 'login']);
