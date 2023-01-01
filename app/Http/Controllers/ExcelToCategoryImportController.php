<?php

namespace App\Http\Controllers;

use App\Imports\CategoriesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelToCategoryImportController extends Controller
{
    public function import(Request $request) {
        if($request->has('file')) Excel::import(new CategoriesImport, $request->file('file'));
        return redirect('/')->with('success', 'All good!');
    }
}
