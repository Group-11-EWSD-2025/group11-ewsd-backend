<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/idea-download-temp/{file}', function ($file) {
    $fullPath = sys_get_temp_dir() . '/' . $file;

    if (!file_exists($fullPath)) {
        abort(404);
    }

    return response()->download($fullPath)->deleteFileAfterSend(true);
})->name('idea.download_temp');
