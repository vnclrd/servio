<?php

use App\Http\Controllers\Api\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/books', [BookController::class, 'index']);
Route::patch('/books/{book}/status', [BookController::class, 'updateStatus']);