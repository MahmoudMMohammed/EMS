<?php
namespace App\Helpers;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyConverterScraper
{
    private static $baseUrl = 'https://sp-today.com/en/';
    private static $cacheKey = 'local_currency_rates';
    private static $cacheDuration = 60; // Duration in minutes

    /**
     * Get exchange rates from the "local-cur" table with caching.
     *
     * @return array
     */
    public static function getAvailableExchanges(): array
    {
        return Cache::remember(self::$cacheKey, self::$cacheDuration, function () {
            $client = new Client();
            $crawler = null;

            try {
                // Fetch the content of the page
                $response = $client->request('GET', self::$baseUrl);
                $html = $response->getBody()->getContents();

                // Initialize the Crawler
                $crawler = new Crawler($html);

                // Extract data from the "local-cur" table
                $currencies = [];
                $crawler->filter('table.table-hover.local-cur tbody tr')->each(function (Crawler $node) use (&$currencies) {
                    // Adjust the selector based on the actual HTML structure
                    $currencyName = $node->filter('th:nth-child(1)')->text(); // Assuming the currency name is in the first column
                    $currencyRate = $node->filter('td:nth-child(4)')->text(); // Assuming the exchange rate is in the fourth column

                    // Extract the currency code from the name (e.g., "US Dollar (USD):" -> "USD")
                    if (preg_match('/\(([^)]+)\)/', $currencyName, $matches)) {
                        $currencyCode = $matches[1];
                        $currencies[$currencyCode] = (float) str_replace(',', '', $currencyRate);
                    }
                });

                return $currencies;
            } catch (\Exception $e) {
                Log::error('Failed to scrape local currency rates: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Convert amount from one currency to another using local currency rates.
     *
     * @param float $amount
     * @param string $to
     * @return float|null
     */
    public static function convert($amount, $to = 'USD'): ?float
    {
        $rates = self::getAvailableExchanges();

        if (isset($rates[$to])) {
            return $amount / $rates[$to];
        }

        return $amount;
    }
}
