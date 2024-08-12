<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Helpers\CurrencyConverter;
use App\Helpers\CurrencyConverterScraper;
use App\Helpers\TranslateTextHelper;
use App\Models\Food;
use App\Services\GenderService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestsController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService){
        $this->notificationService = $notificationService;
    }
    public function testNotifications($user_id)
    {
        $message = "Testing Pusher";

        event(new NotificationEvent($user_id, $message));

    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function testTranslation()
    {
        // Set the source and target languages
        //TranslateTextHelper::setSource('en')->setTarget('ar');
        //TranslateTextHelper::setSource('en')->setTarget('es');
        //TranslateTextHelper::setSource('en')->setTarget('fr');

        // Translate the text
        $translatedText = TranslateTextHelper::translate('Hello, world!');

        // Output the translated text
        echo $translatedText;

    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getGender(Request $request)
    {
        $name = $request->input('name');
        $gender = GenderService::getGenderByName($name);

        return response()->json([
            'name' => $name,
            'gender' => $gender,
        ]);
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function convertPrice()
    {
        $food = Food::find(1);

        return CurrencyConverterScraper::convert($food->getRawPriceAttribute(),"SYP");

//        return CurrencyConverterScraper::getAvailableExchanges();
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function sendPushNotification(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'nullable|array',
        ]);

        $token = $request->token;
        $title = $request->title;
        $body = $request->body;
        $data = $request->data;

        $this->notificationService->sendNotification($token, $title, $body, $data);

        return response()->json(['message' => "notification sent successfully"]);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////

}
