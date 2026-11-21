<?php

use App\Http\Controllers\Api\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/books', [BookController::class, 'index']);
Route::post('/books/{book_id}/status', [BookController::class, 'updateStatus']);
Route::put('/books/{book_id}/status', [BookController::class, 'updateStatus']);