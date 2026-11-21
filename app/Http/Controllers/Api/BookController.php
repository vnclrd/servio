<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    // Controller method to get the books from the database
    public function index(Request $request): JsonResponse
    {
        // Validates that search and q are strings up to 255 characters
        $validated = $request->validate([ 
            'search' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $rawTerm = $validated['search'] ?? $validated['q'] ?? '';
        $search = trim($rawTerm);

        $query = Book::query()
            ->select(['book_id', 'title', 'author', 'status', 'updated_at'])
            ->orderBy('book_id', 'asc');
        
        // Return all records if no query is entered
        if ($search !== '') {
            $pattern = '%' . $search . '%'; // Treats the payloads as literal search text rather than SQL commands (Laravel Eloquent)
            $query->where(function ($builder) use ($pattern) {
                $builder->where('title', 'LIKE', $pattern)
                    ->orWhere('author', 'LIKE', $pattern);
            });
        }

        $books = $query->get();

        // Return all results in JSON format
        return response()->json([
            'success' => true,
            'count' => $books->count(),
            'query' => $search,
            'data' => $books,
        ], 200);
    }

    // Controller method to update the statuses of the books in the database
    public function updateStatus(Request $request, $bookId): JsonResponse
    {
        // Validation that rejects ids less than 1 and prevents non-numeric queries
        if (filter_var($bookId, FILTER_VALIDATE_INT) === false || (int) $bookId < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid book ID.',
            ], 400);
        }
        $bookId = (int) $bookId;

        // Validates the request that 'Available' and 'Borrowed' statuses are only allowed
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['Available', 'Borrowed'])],
        ]);

        // Treats the payloads as literal search text rather than SQL commands
        $book = Book::query()->find($bookId);

        if (! $book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found.',
            ], 404);
        }

        $newStatus = $validated['status'];

        // Rejects borrowing a borrowed book and returning an available book
        if ($book->status === $newStatus) {
            return response()->json([
                'success' => false,
                'message' => $newStatus === 'Borrowed'
                    ? 'This book is already borrowed.'
                    : 'This book is already available.',
            ], 400);
        }

        // Only updates if the status is still what was just read (prevents two requests from both succeeding)
        $changed = Book::query()
            ->where('book_id', $bookId)
            ->where('status', $book->status)
            ->update(['status' => $newStatus]);

        if (! $changed) {
            return response()->json([
                'success' => false,
                'message' => 'Book was just updated by another request. Please refresh.',
            ], 409);
        }

        $book->refresh();

        // REQUIREMENT 2.4: Return an appropriate JSON response after a successful or unsuccessful operation
        return response()->json([
            'success' => true,
            'message' => 'Book status updated successfully.',
            'data' => $book->only(['book_id', 'title', 'author', 'status', 'updated_at']),
        ], 200);
    }
}