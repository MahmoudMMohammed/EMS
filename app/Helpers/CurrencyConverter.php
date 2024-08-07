<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyConverter
{

    protected static $apiKey = "5abfdbcacbf4aeb5b1b6a029";

    public static function setApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }

    public static function convert($amount, $to, $from = "SYP"): float|int|null
    {
        if (is_null(self::$apiKey)) {
            Log::error('API key for currency exchange is not set.');
            return "API key for currency exchange is not set.";
        }

        $url = 'https://v6.exchangerate-api.com/v6/'.self::$apiKey.'/latest/' . $from;
//        $url = 'https://api.exchangerate-api.com/v4/latest/' . $from;

        $response = Http::get($url);

        if ($response->successful()) {
            $rates = $response->json()['conversion_rates'];
            if (isset($rates[$to])) {
                return $amount * $rates[$to];
            }
        }
        return $amount;
    }
}
