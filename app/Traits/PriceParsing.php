<?php

namespace App\Traits;

trait PriceParsing
{
    public function parsePrice($priceString): float
    {
        $cleanedPrice = preg_replace('/[^0-9.,]/', '', $priceString);
        $cleanedPrice = str_replace(',', '', $cleanedPrice);
        return floatval($cleanedPrice);
    }
}
