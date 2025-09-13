<?php

namespace App\Http\Controllers;

use App\Services\Import\Csv;
use App\Services\Upload\UploadCsv;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ImportController extends Controller
{
    public function __construct(protected UploadCsv $uploadCsv) {
        $this->uploadCsv = new UploadCsv();
    }

    public function csvImport(Request $request): JsonResponse
    {

        $fileName = $this->uploadCsv->toStorage($request);

        if(!is_null($fileName) && File::exists('storage/books/'.$fileName)) {

            $importData = Csv::parseCsv(public_path('storage/books/'.$fileName), ',');

            if ($this->uploadCsv->toDatabase($importData)) {
                return response()->json(['message' => 'CSV data imported successfully']);
            }
        }

        return response()->json(['message' => 'CSV data import error'], 400);
    }
}
