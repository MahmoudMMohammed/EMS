<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\Exceptions\LargeTextException;
use Stichoza\GoogleTranslate\Exceptions\RateLimitException;
use Stichoza\GoogleTranslate\Exceptions\TranslationRequestException;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateTextHelper
{
    private static string $source = 'en';
    private static string $target = 'ar';

    public static function setSource(string $source): self
    {
        self::$source = $source;
        return new self();
    }

    public static function setTarget(string $target): self
    {
        self::$target = $target;
        return new self();
    }

    public static function translate(string $text): string
    {
        $translatedText = $text; // Default to original text if translation fails

        try {
            $translator = new GoogleTranslate();
            $translator->setSource(self::$source);
            $translator->setTarget(self::$target);

            $translatedText = $translator->translate($text);
        } catch (LargeTextException|RateLimitException|TranslationRequestException $ex) {
            Log::error('TranslateTextHelperException', [
                'message' => $ex->getMessage(),
            ]);
        } catch (\Exception $e) {
            // Handle generic exceptions (e.g., network error)
            Log::error('TranslateTextHelperException', [
                'message' => $e->getMessage(),
            ]);
        }

        return $translatedText;
    }
}
