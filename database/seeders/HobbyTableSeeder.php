<?php

namespace Database\Seeders;

use App\Models\Hobby;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HobbyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hobbies = ['Reading','Writing','Photography','Sports','Traveling','Drawing','Cooking'];
        foreach($hobbies as $value){
            $hobby = Hobby::firstOrNew(['name'=>$value]);
            $hobby->name = $value;
            $hobby->save();
        }
    }
}
