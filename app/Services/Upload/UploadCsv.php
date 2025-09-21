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
            DB::transaction(function () use ($importData) {

                foreach ($importData as $key => $row) {
                    $importData[$key]['authors'] = explode(';', $row['authors']);
                    $importData[$key]['genre'] = explode(';', $row['genre']);
                    $importData[$key]['publisher'] = explode(', ', $row['publisher']);
                }

                foreach ($importData as  $data) {

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

                    if (!empty($data['authors'])) {
                        foreach ($data['authors'] as $author) {
                            if (Author::query()->where('name', $author)->doesntExist()) {
                                Author::create([
                                    'name' => $author,
                                ]);
                            }
                        }
                        $authors = Author::whereIn('name', $data['authors'])->pluck('id')->toArray();
                        $book->authors()->attach($authors);
                    }

                    if (!empty($data['genre'])) {
                        foreach ($data['genre'] as $genre) {
                            if (Genre::query()->where('name', $genre)->doesntExist()) {
                                Genre::create([
                                    'name' => $genre,
                                ]);
                            }
                        }
                        $genre = Genre::whereIn('name', $data['genre'])->pluck('id')->toArray();
                        $book->genres()->attach($genre);
                    }

                    if (!empty($data['publisher'])) {
                        foreach ($data['publisher'] as $publisher) {
                            if (Publisher::query()->where('name', $publisher)->doesntExist()) {
                                Publisher::create([
                                    'name' => $publisher,
                                ]);
                            }
                        }
                        $publishers = Publisher::whereIn('name', $data['publisher'])->pluck('id')->toArray();;
                        $book->publishers()->attach($publishers);
                    }

                }

            });

           Log::info('CSV data imported successfully');
           return ['message' => 'CSV data imported successfully', 'status' => 200];
       } catch (\Exception $e) {
           Log::error('An error occurred during CSV data import transaction: ' . $e->getMessage());
           return ['message' => 'CSV data import error', 'status' => 400];
       }
    }


}
