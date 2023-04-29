<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $badgeData = [
            ['name' => 'Beginner', 'required_achievement_count' => 1],
            ['name' => 'Enthusiast', 'required_achievement_count' => 3],
            ['name' => 'Advanced', 'required_achievement_count' => 5],
        ];

        DB::table('badges')->insert($badgeData);
    }
}
