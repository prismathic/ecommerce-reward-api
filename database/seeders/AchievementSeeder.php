<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $achievementData = [
            ['name' => 'First Purchase Achievement', 'required_purchase_count' => 1],
            ['name' => 'Fifth Purchase Achievement', 'required_purchase_count' => 5],
            ['name' => 'Tenth Purchase Achievement', 'required_purchase_count' => 10],
            ['name' => 'Dozen Purchase Achievement', 'required_purchase_count' => 12],
            ['name' => 'Muritala Purchase Achievement', 'required_purchase_count' => 20],
        ];

        DB::table('achievements')->insert($achievementData);
    }
}
