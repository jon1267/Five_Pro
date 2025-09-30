<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorResource;
use App\Http\Resources\BookResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\PublisherResource;
use App\Models\Author;
use App\Models\Genre;
use App\Models\Publisher;
use Illuminate\Http\Request;
use App\Http\Requests\CreateBookRequest;
use App\Models\Book;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Book::with(['authors', 'genres', 'publishers']);

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('sort')) {
            $query->sort($request->input('sort'), $request->input('direction', 'asc'));
        }

        $books = $query->paginate(10); //$books = Book::with(['authors','genres','publisher'])->paginate(10);

        return BookResource::collection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateBookRequest $request)
    {
        $book = Book::create($request->except('authors', 'genres', 'publishers'));
        $book->authors()->attach($request->authors);
        $book->genres()->attach($request->genres);
        $book->publishers()->attach($request->publishers);

        return new BookResource($book->load(['authors', 'genres', 'publishers']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $bookAllDetails = $book->load(['authors', 'genres', 'publishers']);
        return new BookResource($bookAllDetails);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CreateBookRequest $request, Book $book)
    {
        $book->update($request->except('authors', 'genres', 'publishers'));

        if ($request->has('authors')) {
            $book->authors()->sync($request->authors);
        }

        if ($request->has('genres')) {
            $book->genres()->sync($request->genres);
        }

        if ($request->has('publishers')) {
            $book->publishers()->sync($request->publishers);
        }

        return new BookResource($book->load(['authors', 'genres', 'publishers']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book): JsonResponse
    {
        return response()->json($book->delete(), 204);
    }

    public function createAuthor(Request $request): AuthorResource
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:authors,name',
        ]);

        $author = Author::create($request->only('name'));

        return new AuthorResource($author);
    }

    public function createGenre(Request $request): GenreResource
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:genres,name',
        ]);

        $author = Genre::create($request->only('name'));

        return new GenreResource($author);
    }

    public function createPublisher(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:publishers,name',
        ]);

        $publisher = Publisher::create($request->only('name'));

        return new PublisherResource($publisher);
    }
}
