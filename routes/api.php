<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatbotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [Authcontroller::class, "login"])->name("login");
Route::get("/logout", [Authcontroller::class, "logout"])->middleware("auth:sanctum");

Route::post("/chat", [ChatbotController::class, "chat"]);
Route::post("/chat-auth", [ChatbotController::class, "chat_auth"])->middleware("auth:sanctum");
