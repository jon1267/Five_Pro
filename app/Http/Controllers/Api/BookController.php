<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Http\Resources\DetailBookResource;
//use Illuminate\Http\Request;
use App\Http\Requests\CreateBookRequest;
use App\Models\Book;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allBooks = Book::with('authors')->get();
        return BookResource::collection($allBooks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateBookRequest $request)
    {
        $book = Book::create($request->validated());
        return new DetailBookResource($book);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $bookAllDetails = $book->load(['authors', 'genres']);;
        return new DetailBookResource($bookAllDetails);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CreateBookRequest $request, Book $book)
    {
        $book->update($request->validated());
        return new DetailBookResource($book);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book): JsonResponse
    {
        $book->delete();
        return response()->json(null, 204);
    }
}
