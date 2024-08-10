<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait RegistrationData
{
    public function getRegistrationInfo($model): array
    {

        $date = Carbon::parse($model->created_at);

        $registrationDate = $date->toDateString();
        $time = $date->format('h:i:s A');

        $diffInDays = $date->diffInDays(now());

        return [
            "date" => "$registrationDate    $time",
            "days" => $diffInDays
        ];
    }
}
