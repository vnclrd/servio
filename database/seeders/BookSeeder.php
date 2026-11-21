<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            ['title' => 'It Ends With Us', 'author' => 'Colleen Hoover', 'status' => 'Available'],
            ['title' => 'Fourth Wing', 'author' => 'Rebecca Yarros', 'status' => 'Available'],
            ['title' => 'The Silent Patient', 'author' => 'Alex Michaelides', 'status' => 'Available'],
            ['title' => 'The Housemaid', 'author' => 'Freida McFadden', 'status' => 'Available'],
            ['title' => 'Project Hail Mary', 'author' => 'Andy Weir', 'status' => 'Available'],
            ['title' => 'Better Than The Movies', 'author' => 'Lynn Painter', 'status' => 'Available'],
        ];

        foreach ($books as $book) {
            Book::firstOrCreate(
                ['title' => $book['title'], 'author' => $book['author']],
                ['status' => $book['status']]
            );
        }
    }
}
