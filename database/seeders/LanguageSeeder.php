<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('languages')->truncate();
        DB::table('translations')->where('translation_type', Language::class)->delete();
        Schema::enableForeignKeyConstraints();

        $en = Language::createWithTranslations([
            'code' => 'en',
            'title' => [
                'en' => 'english',
            ],
        ]);

        Language::createWithTranslations([
            'code' => 'ar',
            'title' => [
                'en' => 'arabic',
                'ar' => 'العربية',
            ],
        ]);

        $en->updateWithTranslations([
            'code' => 'en',
            'title' => [
                'en' => 'english',
                'ar' => 'الانكليزية',
            ],
        ]);
    }
}
