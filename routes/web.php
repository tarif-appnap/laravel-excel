<?php

use App\Http\Controllers\ExcelToCategoryImportController;
use App\Http\Controllers\FastExcelController;
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

Route::post('/laravel-excel-import', [ExcelToCategoryImportController::class, 'import'])->name('excel.import');
Route::post('/fast-excel-import', [FastExcelController::class, 'import'])->name('fast-excel.import');
