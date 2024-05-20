<?php

namespace App\Helpers;

use GuzzleHttp\Client;
class GenderService
{
    protected static $client;

    public static function init()
    {
        if (self::$client === null) {
            self::$client = new Client();
        }
    }

    public static function getGenderByName(string $name)
    {
        self::init();

        $response = self::$client->get('https://api.genderize.io', [
            'query' => [
                'name' => $name,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['gender'] ?? null;
    }
}
