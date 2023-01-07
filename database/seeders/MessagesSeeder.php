<?php

namespace Database\Seeders;

use App\Http\Middleware\Language;
use App\Models\Message;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MessagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('messages')->truncate();
        DB::table('translations')->where('translation_type', Message::class)->delete();
        Schema::enableForeignKeyConstraints();

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'required',
            'value'=> ['en' => 'The :attribute field is required.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'regex',
            'value'=> ['en' => 'The :attribute format is invalid.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'unique',
            'value'=> ['en' => 'The :attribute has already been taken.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'date',
            'value'=> ['en' => 'The :attribute is not a valid date.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'string',
            'value'=> ['en' => 'The :attribute must be a string.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'exists',
            'value'=> ['en' => 'The selected :attribute is invalid.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'array',
            'value'=> ['en' => 'The :attribute must be an array.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'numeric',
            'value'=> ['en' => 'The :attribute must be a number.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'image',
            'value'=> ['en' => 'The :attribute must be an image.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'boolean',
            'value'=> ['en' => 'The :attribute field must be true or false.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'mimes',
            'value'=> ['en' => 'The :attribute must be a file of type: :values.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'in',
            'value'=> ['en' => 'The selected :attribute is invalid.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'date_format',
            'value'=> ['en' => 'The :attribute does not match the format :format.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'required_if',
            'value'=> ['en' => 'The :attribute field is required when :other is :value.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'required_array_keys',
            'value'=> ['en' => 'The :attribute field must contain entries for: :values.'],
        ]);

        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'min-array',
            'value'=> ['en' => 'The :attribute must have at least :min items.'],
        ]);
        
        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'min-file',
            'value'=> ['en' => 'The :attribute must be at least :min kilobytes.'],
        ]);
        
        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'min-numeric',
            'value'=> ['en' => 'The :attribute must be at least :min.'],
        ]);
        
        Message::createWithTranslations([
            'type' => 'validation', 'key'  => 'min-string',
            'value'=> ['en' => 'The :attribute must be at least :min characters.'],
        ]);

        Message::createWithTranslations([
            'type' => 'auth', 'key'  => 'failed',
            'value'=> ['en' => 'username or password is incorrect.'],
        ]);

        Message::createWithTranslations([
            'type' => 'auth', 'key'  => 'password',
            'value'=> ['en' => 'The provided password is incorrect.'],
        ]);

        Message::createWithTranslations([
            'type' => 'auth', 'key'  => 'throttle',
            'value'=> ['en' => 'Too many login attempts. Please try again in :seconds seconds.'],
        ]);


        load_messages(Language::$all_languages);
    }
}
