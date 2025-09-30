<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('books', BookController::class );

Route::post('create-author', [BookController::class, 'createAuthor']);
Route::post('create-genre', [BookController::class, 'createGenre']);
Route::post('create-publisher', [BookController::class, 'createPublisher']);