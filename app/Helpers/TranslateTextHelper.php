<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
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
        $cacheKey = 'translation_' . self::$source . '_' . self::$target . '_' . $text;

        // Check if translation exists in cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Initialize GoogleTranslate instance
            $translator = new GoogleTranslate();

            // Set source and target languages
            $translator->setSource(self::$source);
            $translator->setTarget(self::$target);

            // Translate text
            $translatedText = $translator->translate($text);

            // Cache translated text for future use
            Cache::put($cacheKey, $translatedText, now()->addHours(72));

            return $translatedText;
        } catch (LargeTextException|RateLimitException|TranslationRequestException $ex) {
            Log::error('TranslateTextHelperException', [
                'message' => $ex->getMessage(),
            ]);
            return 'Translation error';
        } catch (\Exception $e) {
            Log::error('TranslateTextHelperException', [
                'message' => $e->getMessage(),
            ]);
            return 'Translation error';
        }
    }

    public static function batchTranslate(array $texts): array
    {
        $translatedTexts = [];

        // Initialize GoogleTranslate instance
        $translator = new GoogleTranslate();

        // Set source and target languages
        $translator->setSource(self::$source);
        $translator->setTarget(self::$target);

        // Translate each text in the batch
        foreach ($texts as $text) {
            $cacheKey = 'translation_' . self::$source . '_' . self::$target . '_' . $text;

            // Check if translation exists in cache
            if (Cache::has($cacheKey)) {
                $translatedTexts[$text] = Cache::get($cacheKey);
                continue;
            }

            try {
                // Translate text
                $translatedText = $translator->translate($text);
                $translatedTexts[$text] = $translatedText;

                // Cache translated text for future use
                Cache::put($cacheKey, $translatedText, now()->addHours(72));
            } catch (\Exception $e) {
                // Handle translation errors
                $translatedTexts[$text] = 'Translation error';
            }
        }

        return $translatedTexts;
    }


    public static function batchTranslateArray(array $texts): array
    {
        $translatedTexts = [];

        // Initialize GoogleTranslate instance
        $translator = new GoogleTranslate();

        // Set source and target languages
        $translator->setSource(self::$source);
        $translator->setTarget(self::$target);

        // Translate each text in the batch
        foreach ($texts as $text) {
            $cacheKey = 'translation_' . self::$source . '_' . self::$target . '_' . $text;

            // Check if translation exists in cache
            if (Cache::has($cacheKey)) {
                $translatedText = Cache::get($cacheKey);
            } else {
                try {
                    // Translate text
                    $translatedText = $translator->translate($text);

                    // Cache translated text for future use
                    Cache::put($cacheKey, $translatedText, now()->addHours(72));
                } catch (\Exception $e) {
                    // Handle translation errors
                    $translatedText = 'Translation error';
                }
            }

            $translatedTexts[] = $translatedText;
        }

        return $translatedTexts;
    }


}
