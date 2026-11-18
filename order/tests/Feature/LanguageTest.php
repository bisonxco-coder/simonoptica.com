<?php

namespace Tests\Feature;

use App\Models\Language;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    public static $testTranslationsPath = 'tests/Feature/data/testTranslations.php';

    public function test_system_language_table_exists()
    {
        $hasTable = Schema::hasTable('system_language');
        $this->assertTrue($hasTable);
    }

    public function test_execute_get_translations_successfully()
    {
        $translations = Language::getTranslations('', self::$testTranslationsPath);
        $this->assertTrue(true);
    }

    public function test_get_default_translation_of_word_orderbasket()
    {
        $defaultLanguageCode = config('app.locale');
        $translations = Language::getTranslations($defaultLanguageCode, self::$testTranslationsPath);
        $this->assertEqualsIgnoringCase('orderbasket', $translations['navbar']['orderbasket']);
    }

    public function test_get_default_translation_of_word_orderbasket_because_of_non_existing_language()
    {
        $translations = Language::getTranslations('aaaaaaaaaaaaaaaaaaaaa', self::$testTranslationsPath);
        $this->assertEqualsIgnoringCase('ORDER BASKET', $translations['navbar']['orderbasket']);
    }

    public function test_get_english_translation_of_word_orderbasket()
    {
        $translations = Language::getTranslations('en', self::$testTranslationsPath);
        $this->assertEqualsIgnoringCase('orderbasket', $translations['navbar']['orderbasket']);
    }

    public function test_get_german_translation_of_word_orderbasket()
    {
        $translations = Language::getTranslations('de', self::$testTranslationsPath);
        $this->assertEqualsIgnoringCase('warenkorb', $translations['navbar']['orderbasket']);
    }

    public function test_get_default_translation_of_word_from_existing_language()
    {
        $translations = Language::getTranslations('de', self::$testTranslationsPath);
        $this->assertEqualsIgnoringCase('log out', $translations['navbar']['dropdown']['logout']);
    }

    public function test_check_if_some_words_are_translated_and_some_are_default()
    {
        $translations = Language::getTranslations('de', self::$testTranslationsPath);
        $this->assertEqualsIgnoringCase('warenkorb', $translations['navbar']['orderbasket']);
        $this->assertEqualsIgnoringCase('log out', $translations['navbar']['dropdown']['logout']);
    }

    public function test_upper_case_translation_of_word_orderbasket()
    {
        $translations = Language::getTranslations('en', self::$testTranslationsPath);
        $this->assertEquals('ORDERBASKET', $translations['navbar']['orderbasket']);
    }

    public function test_execute_get_language_codes_successfully() 
    {
        $languageCodes = Language::getCodes();
        $this->assertTrue(true);
    }

    public function test_is_concatenated_translation_of_new_order_correct()
    {
        $translations = Language::getTranslations('en', self::$testTranslationsPath);
        $this->assertEquals('NEW ORDER', $translations['navbar']['newOrder']);
    }
}
