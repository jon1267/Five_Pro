<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('import', [ImportController::class, 'csvImport'])
    ->withoutMiddleware('web')
    ->name('import.csv');