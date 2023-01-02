<?php

namespace App\Http\Controllers;

use App\Models\BusinessCategory;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;

class FastExcelController extends Controller
{
    public function import(Request $request) {
        if($request->has('file')) {
            $old_business_categories = BusinessCategory::query()->orderBy('id')->pluck('id', 'category_name')->toArray();
            $old_product_categories = ProductCategory::query()->get()->mapWithKeys(fn($item) => [$item->product_category_name.'_'.$item->business_category_id => $item->id])->toArray();
            $id = end($old_business_categories);
            if (!$id) {
                $id = 0;
            }
            $new_business_categories = [];
            $new_product_categories = [];
            $today_date_time = Carbon::now()->format('Y-m-d H:i:s');
            $row_number = 2;
            $errors = [];
            $path = $request->file('file')->store('excels');
            $rows = (new FastExcel)->import(Storage::disk('public')->path($path));
            foreach($rows as $row) {
                if(!$row['Business Category'] || !$row['Product Category']) {
                    $errors[$row_number] = [
                        'business_category' => $row['Business Category'],
                        'product_category' => $row['Product Category']
                    ];
                }

                if (!isset($old_business_categories[$row['Business Category']])) {
                    $id++;
                    $old_business_categories[$row['Business Category']] = $id;
                    $new_business_categories[] = [
                        'id' => $id,
                        'category_name' => $row['Business Category'],
                        'created_at' => $today_date_time,
                        'updated_at' => $today_date_time
                    ];
                }

                if(!isset($old_product_categories[$row['Product Category'].'_'.$old_business_categories[$row['Business Category']]])) {
                    $new_product_categories[] = [
                        'business_category_id'  => $old_business_categories[$row['Business Category']],
                        'product_category_name' => $row['Product Category'],
                        'created_at' => $today_date_time,
                        'updated_at' => $today_date_time
                    ];
                }
                $row_number++;
            }
            if(count($errors)) {
                return false;
            }
            if(count($new_business_categories)) {
                foreach (array_chunk($new_business_categories, 1000) as $data) {
                    BusinessCategory::query()->insert($data);
                }
            }
            if(count($new_product_categories)) {
                foreach (array_chunk($new_product_categories, 1000) as $data) {
                    ProductCategory::query()->insert($data);
                }
            }
            dd('ok');
        }
    }
}
