<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\WalletCharge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletChargeController extends Controller
{
    public function getUserBalanceWithHistory(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $charges = collect($user->charges)->map(function ($charge) {
            unset($charge['user_id']);
            return $charge;
        });
        return response()->json([
            'charges' => $charges,
            'balance' => $user->profile->balance
        ],200);
    }
    //////////////////////////////////////////////////////////////////////////////////


}
