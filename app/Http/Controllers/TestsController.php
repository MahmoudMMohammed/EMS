<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Helpers\GenderService;
use App\Helpers\TranslateTextHelper;
use Illuminate\Http\Request;

class TestsController extends Controller
{
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

}
