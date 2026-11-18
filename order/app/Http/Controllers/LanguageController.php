<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LanguageController extends Controller
{
    /**
     * Get translations for specific a language or default one
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $langCode = $request->input('langCode', self::getDefaultLanguage());
            $translations = Language::getTranslations($langCode);

            return response()->json($translations, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Get translations for the landing site
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getLandingTranslations(Request $request)
    {
        $landingTranslationsPath = "resources/lang/translationsLanding.php";

        try {
            $langCode = $request->input('langCode', self::getDefaultLanguage());
            $translations = Language::getTranslations($langCode, $landingTranslationsPath);

            return response()->json($translations, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Get default language code from .env file or use english as fallback
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string 
     */
    public function getDefaultLanguage(){
        return config('app.locale');
    }

    /**
     * Get available language codes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getLanguageCodes(){
        try {
            $codes = Language::getCodes();

            return response()->json($codes, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }
}
