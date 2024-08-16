<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\WalletCharge;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletChargeController extends Controller
{
    public function getUserBalanceWithHistory(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $charges = collect($user->charges)->map(function ($charge) use ($user) {
            unset($charge['user_id']);
            $date = Carbon::parse($charge->created_at);
            $charge->amount =  number_format($charge->amount,2) . ' ' . $user->profile->preferred_currency;
            $charge['date'] = $date->diffForHumans();
            return $charge;
        });
        return response()->json([
            'charges' => $charges,
            'balance' => number_format($user->profile->balance,2) . ' ' . $user->profile->preferred_currency
        ],200);
    }
    //////////////////////////////////////////////////////////////////////////////////


}
