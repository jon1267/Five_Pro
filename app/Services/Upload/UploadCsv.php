<?php

namespace App\Services\Upload;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\Import\Csv;

class UploadCsv
{
    public function toStorage(Request $request): ?string
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->extension() ?: 'csv';
            $upload_file = Str::random(10) . '.' . $extension;
            $destinationPath = storage_path(config('config.storage_path'));
            $file->move($destinationPath, $upload_file);
            return $upload_file;
        }

        return null;
    }

    public function toDatabase(?string $fileName): array
    {
        if(!is_null($fileName) && File::exists(config('config.file_dir').$fileName)) {
            $importData = Csv::parseCsv(public_path(config('config.file_dir').$fileName), ',');
        } else {
            Log::warning('CSV file is empty or invalid');
            return ['message' => 'CSV file for import not found', 'status' => 404];
        }

        if (empty($importData)) {
            Log::warning('CSV file is empty or invalid');
            return ['message' => 'CSV file is empty or invalid', 'status' => 400];
        }

        try {
            // turn off transaction may be it dont need here.
            //DB::transaction(function () use ($importData) {

            foreach ($importData as $key => $row) {
                $importData[$key]['authors'] = explode(';', $row['authors']);
                $importData[$key]['genre'] = explode(';', $row['genre']);
                $importData[$key]['publisher'] = explode(', ', $row['publisher']);
            }

            $importBooksCount = 0;

            foreach ($importData as  $data) {



                $bookExists = Book::query()
                    ->where('title', $data['title'])
                    ->where('description', $data['description'])
                    ->exists();

                if (!$bookExists) {
                    $book = Book::create([
                        'title' => $data['title'],
                        'description' => $data['description'] ?? null,
                        'edition' => $data['edition'] ?? null,
                        'year' => $data['year'] ?? null,
                        'format' => $data['format'] ?? null,
                        'pages' => $data['pages'] ?? null,
                        'country' => $data['country'] ?? null,
                        'isbn' => $data['isbn'] ?? null,
                    ]);

                    $importBooksCount++;
                } else {
                    // If the Book already exists, we assume it has Authors, Genres, Publishers. We skip it.
                    continue;
                }

                if (!empty($data['authors'])) {
                    $attachAuthors = [];
                    foreach ($data['authors'] as $author) {
                        if (Author::query()->where('name', $author)->doesntExist()) {
                            $createdAuthor = Author::create(['name' => $author]);
                            $attachAuthors[] = $createdAuthor->id;
                        } else {
                            // Check if the author already exists in the database
                            if ($existingAuthor = Author::query()->where('name', $author)->first()) {
                                $attachAuthors[] = $existingAuthor->id;
                            }
                        }
                    }

                    if (!empty($attachAuthors)) {
                        $book->authors()->attach($attachAuthors);
                    }
                }

                if (!empty($data['genre'])) {
                    $attachGenres = [];
                    foreach ($data['genre'] as $genre) {
                        if (Genre::query()->where('name', $genre)->doesntExist()) {
                            $createdGenre = Genre::create(['name' => $genre]);
                            $attachGenres[] = $createdGenre->id;
                        } else {
                            // Check if the genre already exists in the database
                            if ($existingGenre = Genre::query()->where('name', $genre)->first()) {
                                $attachGenres[] = $existingGenre->id;
                            }
                        }
                    }

                    if (!empty($attachGenres)) {
                        $book->genres()->attach($attachGenres);
                    }
                }

                if (!empty($data['publisher'])) {
                    $attachPublishers = [];
                    foreach ($data['publisher'] as $publisher) {
                        if (Publisher::query()->where('name', $publisher)->doesntExist()) {
                            $createdPublisher = Publisher::create(['name' => $publisher]);
                            $attachPublishers[] = $createdPublisher->id;
                        } else {
                            // Check if the publisher already exists in the database
                            if ($existingPublisher = Publisher::query()->where('name', $publisher)->first()) {
                                $attachPublishers[] = $existingPublisher->id;
                            }
                        }
                    }

                    if (!empty($attachPublishers)) {
                        $book->publishers()->attach($attachPublishers);
                    }
                }
            }

            //});

            $message = ($importBooksCount > 0) ?
                'CSV data imported successfully. Was imported ' . $importBooksCount . ' books.' :
                'CSV data import finish. No new books were imported.';
            Log::info($message);
            return ['message' => $message, 'status' => 200];
       } catch (\Exception $e) {
           Log::error('An error occurred during CSV data import transaction: ' . $e->getMessage());
           return ['message' => 'CSV data import error', 'status' => 400];
       }
    }


}
