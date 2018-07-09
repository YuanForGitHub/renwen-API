<?php

use Illuminate\Database\Seeder;

class LendSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('lend')->insert([
            'classroom' => '人文课室1或2',
            'personName' => str_random(4),
            'personId' => str_random(10),
            'phone' => rand(1000000000, 1999999999),
            'org' => str_random(4),
            'reason' => str_random(16),
            'year' => 2018,
            'month' => 6,
            'date' => rand(1, 31),
            'start_hour' => rand(0, 24),
            'start_minute' => rand(0, 30),
            'end_hour' => rand(0, 24),
            'end_minute' => rand(0, 30),
            'pass' => 0
        ]);
    }
}
