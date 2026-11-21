<?php

use App\Http\Controllers\Api\BookController;
use Illuminate\Support\Facades\Route;

// REQUIREMENT 1.1: Accept GET requests to retrieve books from the database
Route::get('/books', [BookController::class, 'index']);

// REQUIREMENT: 2.1: Accept POST or PUT requests to update a book's borrowing status using its book_id
Route::post('/books/{book_id}/status', [BookController::class, 'updateStatus']);
Route::put('/books/{book_id}/status', [BookController::class, 'updateStatus']);