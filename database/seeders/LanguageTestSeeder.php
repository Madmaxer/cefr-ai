<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LanguageTest;
use Carbon\Carbon;

class LanguageTestSeeder extends Seeder
{
    public function run()
    {
        LanguageTest::create([
            'user_id' => '1d6fea35-453d-4d4f-b7a0-5362526ab238',
            'language' => 'english',
            'level' => 'B2',
            'description' => 'Upper-Intermediate level achieved.',
            'tested_at' => Carbon::now(),
        ]);

        LanguageTest::create([
            'user_id' => '1d6fea35-453d-4d4f-b7a0-5362526ab238',
            'language' => 'spanish',
            'level' => 'A1',
            'description' => 'Beginner level achieved.',
            'tested_at' => Carbon::now()->subDays(5),
        ]);
    }
}
