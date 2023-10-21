<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Front-End Development','Back-End Development','Full-Stack Development','Web Designer'];
        foreach($categories as $value){
            $category = Category::firstOrNew(['title'=>$value]);
            $category->title = $value;
            $category->status = true;
            $category->save();
        }
    }
}
