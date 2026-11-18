<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'system_language';

    // String translation options (used in resources/lang/translations.php)
    const optionToUpper = 0;      // foo -> FOO
    const optionToLower = 1;      // FOO -> foo
    const optionCapitalize = 2;   // foo -> Foo
    const optionShortenUpper = 3; // fooooo -> FOO.
    const optionShortenLower = 4; // Fooooo -> foo.

    /**
     * Get all available language codes
     */
    public static function getCodes() {
        $lang = self::select('LangCode')
        ->distinct()
        ->orderBy('LangCode')
        ->get()
        ->pluck('LangCode');

        if(env('LOCALE_ENABLED')){
            $enabled = explode(',', env('LOCALE_ENABLED'));
            foreach ($lang as $key => $l){
                if(!in_array($l, $enabled)){
                    unset($lang[$key]);
                }
            }
        }
        return array_values($lang->toArray());
    }

    /**
     * Get translations for a specific language
     */
    public static function getTranslations($langCode, $translationFilePath = 'resources/lang/translations.php', $useCustomPath = false) {
        $path = $useCustomPath ? $translationFilePath : base_path($translationFilePath);
        $translations = require($path);

        $translationIds = self::getTranslationsLanguageNumbers($translations, 'langNumber');

        // Get database translations
        $databaseTranslations = [];
        try {
            $databaseTranslations = self::where([
                ['LangCode', '=', $langCode],
                ['LangPartner', '=', ''],
            ])
            ->whereIn('LangNumber', $translationIds)
            ->get()
            ->toArray();
        }
        catch (\Throwable $th) {}

        $params = [
            'translations' => $translations,
            'databaseTranslations' => $databaseTranslations,
            'translationsRowId' => 'langNumber',
            'translationsDefaultId' => 'default',
            'translationsOptionId' => 'option',
            'databaseTranslationRowId' => 'LangNumber',
            'databaseTranslationTextId' => 'LangText',
        ];
        $newTranslations = self::replaceTranslationsFromDatabase($params);
        $newTranslations = self::parseConcatenatedTranslations($newTranslations);

        return $newTranslations;
    }

    private static function getTranslationsLanguageNumbers($translations, $langNumberId) {
        $ids = [];
        array_walk_recursive($translations, function($item, $key) use(&$ids, $langNumberId){
            if ($key == $langNumberId) $ids[] = $item;
        });
        return $ids;
    }

    private static function replaceTranslationsFromDatabase($params){
        return array_map(function ($translation) use($params) {
            if (isset($translation[$params['translationsRowId']])){
                // Use db translation if available or use default one
                $translationValue = $translation[$params['translationsDefaultId']];
                $translationId = $translation[$params['translationsRowId']];
                $dbTranslations = $params['databaseTranslations'];
                array_walk($dbTranslations, function($dbTranslation) use($params, $translationId, &$translationValue){
                    if (isset($dbTranslation[$params['databaseTranslationRowId']]) && $dbTranslation[$params['databaseTranslationRowId']] == $translationId) {
                        if (isset($dbTranslation[$params['databaseTranslationTextId']])){
                            $translationValue = $dbTranslation[$params['databaseTranslationTextId']];
                        }
                    }
                });

                // Apply string options
                if (isset($translation[$params['translationsOptionId']])){
                    $optionId = $translation[$params['translationsOptionId']];

                    switch($optionId) {
                        case self::optionToUpper:
                            $translationValue = self::optionToUpper($translationValue);
                            break;
                        case self::optionToLower:
                            $translationValue = self::optionToLower($translationValue);
                            break;
                        case self::optionCapitalize:
                            $translationValue = self::optionCapitalize($translationValue);
                            break;
                        case self::optionShortenUpper:
                            $translationValue = self::optionShortenUpper($translationValue);
                            break;
                        case self::optionShortenLower:
                            $translationValue = self::optionShortenLower($translationValue);
                            break;
                    }
                }

                return $translation = $translationValue;
            }else if(is_string($translation)) {
                return $translation;
            }

            // Recurse
            if (is_array($translation)){
                $newParams = $params;
                $newParams['translations'] = $translation;
                return self::replaceTranslationsFromDatabase($newParams);
            }
        }, $params['translations']);
    }

    private static function parseConcatenatedTranslations($translations) {
        return array_map(function ($item) {

            // Detect for array with no key values -> the array, whose values need to be concated
            if (is_array($item) && key($item) === 0){
                return implode($item);
            }

            // Recurse
            if (is_array($item)){
                return self::parseConcatenatedTranslations($item);
            }

            return $item;

        }, $translations);
    }

    private static function optionToUpper($string) {
        return strtoupper($string);
    }

    private static function optionToLower($string) {
        return strtolower($string);
    }

    private static function optionCapitalize($string) {
        return \Str::ucfirst($string);
    }

    private static function optionShortenUpper($string) {
        return self::optionToUpper(\Str::limit($string, 3, '.'));
    }

    private static function optionShortenLower($string) {
        return self::optionToLower(\Str::limit($string, 3, '.'));
    }
}
