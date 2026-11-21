<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $rawTerm = $validated['search'] ?? $validated['q'] ?? '';
        $search = trim($rawTerm);

        $query = Book::query()
            ->select(['book_id', 'title', 'author', 'status', 'updated_at'])
            ->orderBy('book_id', 'asc');

        if ($search !== '') {
            $pattern = '%' . $search . '%';
            $query->where(function ($builder) use ($pattern) {
                $builder->where('title', 'LIKE', $pattern)
                    ->orWhere('author', 'LIKE', $pattern);
            });
        }

        $books = $query->get();

        return response()->json([
            'success' => true,
            'count' => $books->count(),
            'query' => $search,
            'data' => $books,
        ], 200);
    }

    public function updateStatus(Request $request, int $bookId): JsonResponse
    {
        if ($bookId < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid book ID.',
            ], 400);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['Available', 'Borrowed'])],
        ]);

        $book = Book::query()->find($bookId);

        if (! $book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found.',
            ], 404);
        }

        $book->status = $validated['status'];
        $book->save();

        return response()->json([
            'success' => true,
            'message' => 'Book status updated successfully.',
            'data' => $book->only(['book_id', 'title', 'author', 'status', 'updated_at']),
        ], 200);
    }
}