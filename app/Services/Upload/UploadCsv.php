<?php

namespace App\Services\Upload;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UploadCsv
{
    public function toStorage(Request $request, string $storage_path = 'app/public/books/'): ?string
    {
        $fileName = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->extension() ?: 'csv';
            $upload_file = Str::random(10) . '.' . $extension;
            $destinationPath = storage_path($storage_path);
            $file->move($destinationPath, $upload_file);
            $fileName = $upload_file;
        }

        return $fileName;
    }

    public function toDatabase(array $importData): bool
    {
        try {
            DB::transaction(function () use ($importData) {

                foreach ($importData as $key => $row) {
                    $importData[$key]['authors'] = explode(';', $row['authors']);
                    $importData[$key]['genre'] = explode(';', $row['genre']);
                    $importData[$key]['created_at'] = $importData[$key]['updated_at'] = now();
                }

                foreach ($importData as  $data) {
                    $book = Book::create($data);
                    if (!empty($data['authors'])) {
                        foreach ($data['authors'] as $author) {
                            DB::table('authors')->insert([
                                'name' => $author,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                        $authors = Author::whereIn('name', $data['authors'])->pluck('id')->toArray();
                        $book->authors()->attach($authors);
                    }
                    if (!empty($data['genre'])) {
                        foreach ($data['genre'] as $genre) {
                            DB::table('genres')->insert([
                                'name' => $genre,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                        $genre = Genre::whereIn('name', $data['genre'])->pluck('id')->toArray();
                        $book->genres()->attach($genre);
                    }
                }

            });

            return true;
        } catch (\Exception $e) {
            Log::error('An error occurred during transaction: ' . $e->getMessage());
            return false;
        }
    }


}
