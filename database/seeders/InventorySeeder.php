<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        for($i = 1; $i <= rand(1000, 2000); $i++){
            $name = $faker->text(20, 3);
            $slug = Str::slug($name, '-');
            $check_slug = DB::table('inventories')->where('slug', $slug)->get();
            $x = 1;
            $nslug = $slug;
            while(count($check_slug) > 0){
                $nslug = $slug.'-'.$x;
                $check_slug = DB::table('inventories')->where('slug', $nslug)->get();
                $x++;
            }
            $slug = $nslug;

            $unitArray = array('kg', 'g', 'pc');
            $unit = $unitArray[array_rand($unitArray, 1)];

            Inventory::create([
                'item_code' => null,
                'name' => $name,
                'category_id' => rand(1,10),
                'quantity' => rand(10,100),
                'reorder_point' => rand(10,15),
                'unit' => $unit,
                'price' => rand(1000,100000),
                'image' => null,
                'slug' => $slug,
            ]);
        }
    }
}
