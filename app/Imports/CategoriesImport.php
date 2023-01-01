<?php

namespace App\Imports;

use App\Models\BusinessCategory;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CategoriesImport implements ToCollection, WithValidation, SkipsEmptyRows, WithHeadingRow, SkipsOnFailure, WithChunkReading
{
    public function collection(Collection $rows)
    {
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
        foreach ($rows as $key => $row) {
            if(!$row['business_category'] || !$row['product_category']) {
                $errors[$row_number] = [
                    'business_category' => $row['business_category'],
                    'product_category' => $row['product_category']
                ];
            }

            if (!isset($old_business_categories[$row['business_category']])) {
                $id++;
                $old_business_categories[$row['business_category']] = $id;
                $new_business_categories[] = [
                    'id' => $id,
                    'category_name' => $row['business_category'],
                    'created_at' => $today_date_time,
                    'updated_at' => $today_date_time
                ];
            }

            if(!isset($old_product_categories[$row['product_category'].'_'.$old_business_categories[$row['business_category']]])) {
                $new_product_categories[] = [
                    'business_category_id'  => $old_business_categories[$row['business_category']],
                    'product_category_name' => $row['product_category'],
                    'created_at' => $today_date_time,
                    'updated_at' => $today_date_time
                ];
            }
            $row_number++;
        }
        if(count($errors)) {
            return false;
        }
        if(count($new_business_categories)) BusinessCategory::insert($new_business_categories);
        if(count($new_product_categories)) ProductCategory::insert($new_product_categories);
        return true;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return [
            'business_category' => [],
            'product_category' => [],
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        logger($failures);
    }

}
