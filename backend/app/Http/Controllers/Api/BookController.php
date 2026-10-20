<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Daftar buku berhasil diambil',
            'data' => Book::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'publication_year' => 'required|integer|min:1000|max:' . date('Y'),
        ]);

        $book = Book::create($validated);

        return response()->json([
            'message' => 'Buku berhasil ditambahkan',
            'data' => $book,
        ], 201);
    }

    public function show(Book $book)
    {
        return response()->json([
            'message' => 'Detail buku berhasil diambil',
            'data' => $book,
        ]);
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:100',
            'publication_year' => 'sometimes|required|integer|min:1000|max:' . date('Y'),
        ]);

        $book->update($validated);

        return response()->json([
            'message' => 'Buku berhasil diperbarui',
            'data' => $book->fresh(),
        ]);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json([
            'message' => 'Buku berhasil dihapus',
        ]);
    }
}
