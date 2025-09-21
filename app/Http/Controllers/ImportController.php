<?php

namespace App\Http\Controllers;

use App\Services\Upload\UploadCsv;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function __construct(protected UploadCsv $uploadCsv) {
        $this->uploadCsv = new UploadCsv();
    }

    public function csvImport(Request $request): JsonResponse
    {
        $fileName = $this->uploadCsv->toStorage($request);

        $result = $this->uploadCsv->toDatabase($fileName);

        return response()->json(['message' => $result['message']], $result['status']);
    }
}
