<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Accessory;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Drink;
use App\Models\Food;
use App\Models\Location;
use App\Models\LocationPicture;
use App\Models\User;
use App\Models\UserEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Constraint\IsEmpty;

class LocationController extends Controller
{
    public function HomeCount(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $location = Location::query()->count();
        if(!$location){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $Accessory = Accessory::query()->count();
        if(!$Accessory){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $Food = Food::query()->count();
        if(!$Food){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $Drink = Drink::query()->count();

        if(!$Drink){
            return response()->json([
                'error' => 'Something went wrong , try again later' ,
                'status_code' => 400
            ] , 400);
        }

        $cart = Cart::query()->where('user_id' , $user->id)->first();

        if(!$cart){
           return response()->json(['location_count' => $location ,
                                 'food_count' => $Food ,
                                 'drink_count' => $Drink ,
                                 'accessory_count' => $Accessory ,
                                 'item of cart' => 0,
                                 'status_code' => 200] , 200);
        }

        $count = CartItem::query()
            ->where('cart_id' , $cart->id)
            ->where('itemable_type' ,'!=' , 'App\Models\Accessory')->count();

        return response()->json(['location_count' => $location ,
                                 'food_count' => $Food ,
                                 'drink_count' => $Drink ,
                                 'accessory_count' => $Accessory ,
                                 'item of cart' => $count,
                                 'status_code' => 200] , 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////
    public function getLocations(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $locations = Location::select('id', 'name', 'governorate', 'open_time', 'close_time', 'logo')->get();

        if ($locations->isEmpty()) {
            return response()->json([
                "error" => "No locations to show!",
                "status_code" => 404
            ], 404);
        }

        // Convert the plucked collections to arrays and reset indices
        $names = $locations->pluck('name')->values()->toArray();
        $governorates = $locations->pluck('governorate')->values()->toArray();

        // Translate names and governorates
        $translatedNames = TranslateTextHelper::batchTranslate($names);
        $translatedGovernorates = TranslateTextHelper::batchTranslate($governorates);

        // Map the translated names and governorates back to the locations
        foreach ($locations as  $location) {
            $location->name = $translatedNames[$location->name] ?? $location->name;
            $location->governorate = $translatedGovernorates[$location->governorate] ?? $location->governorate;
        }

        return response()->json($locations, 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////

    public function getLocationById($location_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $location = Location::find($location_id);
        if (!$location){
            return response()->json([
                "error" => "Location not found!",
                "status_code" => 404,
            ], 404);
        }

        $admin = User::with('profile')->find($location->user_id);

        $locationPictures = LocationPicture::whereLocationId($location_id)->pluck('picture');

        $isFavorite = false;

        foreach ($user->favorites as $favorite){
            if ($location_id == $favorite->favoritable_id && get_class($location) == $favorite->favoritable_type){
                $isFavorite = true;
                break;
            }
        }

        $locationData = [
            "id" => $location->id,
            "name" => TranslateTextHelper::translate($location->name),
            "governorate" => TranslateTextHelper::translate($location->governorate),
            "address" => TranslateTextHelper::translate($location->address),
            "capacity" => $location->capacity,
            "open_time" => $location->open_time,
            "close_time" => $location->close_time,
            "reservation_price" => $location->reservation_price,
            "x_position" => $location->x_position,
            "y_position" => $location->y_position,
            "logo" => $location->logo,
            "picture1" => $locationPictures[0],
            "picture2" => $locationPictures[1],
            "picture3" => $locationPictures[2],
            "admin_name" => $admin->name,
            "admin_email" => $admin->email,
            "admin_phone_number" => $admin->profile->phone_number,
            "is_favorite" => $isFavorite,
        ];
        return response()->json($locationData, 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    public function SortLocation(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }
        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $validator = Validator::make($request->all(), [
            "host_id" => 'required|integer|exists:locations,host_id',
            "governorate" => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ], 422);
        }

        $isGovernorateNull = strtolower($request->governorate) === 'null';

        $query = Location::query()->where('host_id' , $request->host_id);

        if (!$query->count() > 0) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        if($request->governorate && !$isGovernorateNull)
        {
            $query->where('governorate' , TranslateTextHelper::translateToEnglishOnly($request->governorate));
        }

        $locations = $query->select('id', 'name', 'governorate', 'open_time', 'close_time', 'capacity' , 'logo' , 'maintenance')->get();

        if ($locations->isEmpty() && $request->governorate  && !$isGovernorateNull) {
            return response()->json([
                'error' => TranslateTextHelper::translate("No locations found for the specified governorate"),
                'status_code' => 404,
            ], 404);
        }


        $name = $locations->pluck('name')->toArray();
        $name = TranslateTextHelper::batchTranslate($name);

        $governorate = $locations->pluck('governorate')->toArray();
        $governorate = TranslateTextHelper::batchTranslate($governorate);

        $response = [];
        foreach ($locations as $location)
        {
            $response [] = [
                'id' => $location -> id ,
                'name' => $name[$location->name] ,
                'governorate' => $governorate[$location->governorate] ,
                'open_time' => $location -> open_time ,
                'close_time' => $location -> close_time,
                'capacity' => $location -> capacity,
                'logo' => $location -> logo,
                'maintenance' => $location -> maintenance
            ];
        }


        sleep(1);
        return response()->json($response , 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function GetAllGovernorate(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $governorate = Location::distinct()->pluck('governorate')->toArray();

        if(!$governorate)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        array_unshift($governorate, 'all');

        $response = TranslateTextHelper::batchTranslateArray($governorate);

        return response()->json($response , 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebGetLocationByHost($category_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        if($category_id < 0 || $category_id > 7)
        {
            return response()->json([
                "error" => "invalid category ID must be between 0 and 7",
                "status_code" => 422,
            ], 422);
        }

        $result = [];
        if($category_id == 0)
        {
            $result = Location::query()->select('id' , 'name' , 'governorate' , 'capacity' , 'open_time' , 'close_time' , 'logo')->get();
            if($result->isEmpty())
            {
                return response()->json([
                    "message" => "There are no Locations.",
                    "status_code" => 404,
                ], 404);
            }
        }
        elseif (in_array($category_id , range(1,7)))
        {
            $result = Location::query()
                ->where('host_id' , $category_id)
                ->select('id' , 'name' , 'governorate' , 'capacity' , 'open_time' , 'close_time' , 'logo')
                ->get();

            if($result->isEmpty())
            {
                return response()->json([
                    "message" => "There are no Locations for this specific host.",
                    "status_code" => 404,
                ], 404);
            }
        }
        return response()->json($result , 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebGetLocationGeneral($location_id): JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Location::query()->find($location_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid Location id",
                "status_code" => 422,
            ], 422);
        }

        $photo = LocationPicture::query()
            ->where('location_id' , $location_id)
            ->orderBy('id')
            ->select('picture')
            ->get();


        $response = [
            'id' => $exist->id ,
            'governorate' => $exist->governorate ,
            'address' => $exist->address ,
            'host' => $exist->host->name ,
            'Xp' => $exist->x_position ,
            'Yp' => $exist->y_position ,
            'photo_1' => $photo[0]->picture ,
            'photo_2' => $photo[1]->picture ,
            'photo_3' => $photo[2]->picture

        ];
        return response()->json($response ,200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebGetLocationDetails($location_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Location::query()->find($location_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid Location id",
                "status_code" => 422,
            ], 422);
        }

        $response = [
            'id' => $exist->id ,
            'name' => $exist->name ,
            'hour_price' => number_format($exist->reservation_price).' S.P' ,
            'open_time' => $exist->open_time ,
            'close_time' => $exist->close_time ,
            'capacity' => $exist->capacity
        ];
        return response()->json($response ,200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebEditLocationDetails(Request $request , $location_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Location::query()->find($location_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid Location id",
                "status_code" => 422,
            ], 422);
        }

        $validator = Validator::make($request->all() , [
            'name' => 'required|max:50' ,
            'price' => 'required|ends_with: S.P' ,
            'open' => 'required|date_format:h:i A' ,
            'close' => 'required|date_format:h:i A' ,
            'capacity' => 'required|integer|doesnt_start_with:0|max:100000|min:1'
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        // Initialize counters for "S" and "P"
        $sCount = 0;
        $pCount = 0;

        // Check each character in the input
        foreach (str_split($request->input('price')) as $char) {
            if ($char === 'S') {
                $sCount++;
            } elseif ($char === 'P') {
                $pCount++;
            } elseif (!ctype_digit($char) && $char !== ' ' && $char !== '.' && $char !== ',') {
                // Contains an invalid character
                return response()->json([
                    "error" => 'The format of the price is incorrect.',
                    "status_code" => 422,
                ], 422);
            }
        }

        // Validate the count of "S" and "P"
        if ($sCount > 1 || $pCount > 1) {
            return response()->json([
                "error" => 'The format of the price is incorrect.',
                "status_code" => 422,
            ], 422);
        }

        $format = (float)str_replace(['S.P', ',', ' '], '', $request->input('price'));

        $exist->update([
            'name' => $request->input('name'),
            'reservation_price' => $format,
            'open_time' => $request->input('open'),
            'close_time' => $request->input('close'),
            'capacity' => $request->input('capacity'),
        ]);


        return response()->json([
            "message" => "Location details updated successfully",
            "status_code" => 200,
        ], 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebGetLocationAdmin($location_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Location::query()->find($location_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid Location id",
                "status_code" => 422,
            ], 422);
        }

        $response = [
            'id' => $exist->admin->id ,
            'name' => $exist->admin->name ,
            'email' => $exist->admin->email ,
            'phone_number' => $exist->admin->profile->phone_number
        ];

        return response()->json($response ,200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebDeleteLocation($location_id):JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Location::query()->find($location_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid Location id",
                "status_code" => 422,
            ], 422);
        }


        $event = UserEvent::query()
            ->where('location_id' , $exist->id)
            ->whereNotIn('verified', ['Finished', 'Rejected'])
            ->exists();

        if($event)
        {
            return response()->json([
                "message" => "You cannot delete this place because there are reservations attached to it.",
                "status_code" => 422,
            ], 422);
        }

        $exist->delete();

        return response()->json([
            "message" => "This location has been successfully deleted" ,
            "status_code" => 200,
        ] , 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebPutLocationInMaintenance($location_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Location::query()->find($location_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid Location id",
                "status_code" => 422,
            ], 422);
        }

        $event = UserEvent::query()
            ->where('location_id' , $exist->id)
            ->whereNotIn('verified', ['Finished', 'Rejected'])
            ->exists();

        if($event)
        {
            return response()->json([
                "message" => "You cannot maintain this place because there are reservations attached to it.",
                "status_code" => 422,
            ], 422);
        }

        if($exist->maintenance == 1)
        {
            return response()->json([
                "message" => "This place is actually under maintenance.",
                "status_code" => 422,
            ], 422);
        }

        $exist->maintenance = 1;
        $exist->save();

        return response()->json([
            "message" => "This place has been successfully put into maintenance" ,
            "status_code" => 200,
        ]);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebPutLocationInService($location_id):JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Location::query()->find($location_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid Location id",
                "status_code" => 422,
            ], 422);
        }

        if($exist->maintenance == 0)
        {
            return response()->json([
                "message" => "This place is actually under service.",
                "status_code" => 422,
            ], 422);
        }

        $exist->maintenance = 0;
        $exist->save();

        return response()->json([
            "message" => "This place has been successfully put into service" ,
            "status_code" => 200,
        ]);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function updateLocationLogo(Request $request): JsonResponse
    {
        $owner = Auth::user();
        TranslateTextHelper::setTarget($owner->profile->preferred_language);

        $validator = Validator::make($request->all(), [
            'location_id' => 'required|exists:locations,id',
            'logo' => 'required|image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ], 422);
        }

        $location = Location::findOrFail($request->location_id);
        $logo = $location->getRawOriginal('logo');
        $filenameWithoutExtension = pathinfo($logo, PATHINFO_FILENAME);

        //delete the old file
        if ($logo) {
            $currentLogoPath = public_path('Location/') . $filenameWithoutExtension . '.' . pathinfo($logo, PATHINFO_EXTENSION) ;
            if (file_exists($currentLogoPath)) {
                unlink($currentLogoPath);
            }
        }

        $image = $request->file('logo');
        $newFilename = $filenameWithoutExtension . '.' . $image->getClientOriginalExtension() ;
        $destination = public_path('Location/');
        $image->move($destination, $newFilename);

        $location->logo = "Location/$newFilename";
        $location->save();

        return response()->json([
            "message" => TranslateTextHelper::translate("Location logo has been updated successfully"),
            "status_code" => 200,
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////

    public function updateOneOfLocationPictures(Request $request): JsonResponse
    {
        $owner = Auth::user();
        TranslateTextHelper::setTarget($owner->profile->preferred_language);

        $validator = Validator::make($request->all(), [
            'location_id' => 'required|exists:locations,id',
            'picture_number' => 'required|in:1,2,3',
            'image' => 'required|image'
        ]);

        if($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ], 422);
        }

        $location = Location::findOrFail($request->location_id);
        $location_pictures = LocationPicture::whereLocationId($location->id)->get();

        // Map picture_id to the correct picture in the collection
        $locationPicture = $location_pictures->get($request->picture_number - 1);

        $picture = $locationPicture->getRawOriginal('picture');
        $filenameWithoutExtension = pathinfo($picture, PATHINFO_FILENAME);

        //delete the old file
        if ($picture) {
            $currentPicturePath = public_path('Location/') . $filenameWithoutExtension . '.' . pathinfo($picture, PATHINFO_EXTENSION) ;
            if (file_exists($currentPicturePath)) {
                unlink($currentPicturePath);
            }
        }

        $image = $request->file('image');
        $newFilename = $filenameWithoutExtension . '.' . $image->getClientOriginalExtension() ;
        $destination = public_path('Location/');
        $image->move($destination, $newFilename);

        $locationPicture->picture = "Location/$newFilename";
        $locationPicture->save();

        return response()->json([
            "message" => TranslateTextHelper::translate("Location picture has been updated successfully"),
            "status_code" => 200,
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function WebAddLocation(Request $request):JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $validator = Validator::make($request->all() , [
            'name' => 'required|max:50' ,
            'governorate' => 'required|in:Damascus,Homs,Tartus,Aleppo,Suwayda,Daraa,Raqqa' ,
            'address' => 'required|max:30',
            'admin'=> 'required|integer|exists:users,id',
            'logo'=> 'required|image',
            'photo_1'=>'required|image',
            'photo_2'=>'required|image',
            'photo_3'=>'required|image',
            'host' => 'required|integer|exists:hosts,id',
            'open' => 'required|date_format:h:i A' ,
            'close' => 'required|date_format:h:i A' ,
            'capacity' => 'required|integer|doesnt_start_with:0|max:100000|min:1' ,
            'price' => 'required|integer|doesnt_start_with:0|max:1000000000|min:1'
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $publicPath = public_path('Location');

        $logoPath = 'Location/' . $request->file('logo')->getClientOriginalName();
        $request->file('logo')->move($publicPath, $logoPath);

        $photo1Path = 'Location/' .  $request->file('photo_1')->getClientOriginalName();
        $request->file('photo_1')->move($publicPath, $photo1Path);

        $photo2Path = 'Location/' .  $request->file('photo_2')->getClientOriginalName();
        $request->file('photo_2')->move($publicPath, $photo2Path);

        $photo3Path = 'Location/' .  $request->file('photo_3')->getClientOriginalName();
        $request->file('photo_3')->move($publicPath, $photo3Path);

        $location = Location::query()->create([
            'name' => $request->input('name'),
            'governorate' => $request->input('governorate'),
            'address' => $request->input('address'),
            'reservation_price' => $request->input('price'),
            'user_id' => $request->input('admin'),
            'host_id' => $request->input('host'),
            'open_time' => $request->input('open'),
            'close_time' => $request->input('close'),
            'capacity' => $request->input('capacity'),
            'logo' => $logoPath,
        ]);

        if(!$location)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $photoPaths = [$photo1Path , $photo2Path , $photo3Path];

        foreach ($photoPaths as $photoPath) {
            LocationPicture::query()->create([
                'location_id' => $location->id,
                'picture' => $photoPath,
            ]);
        }

        return response()->json([
            "message" => "Location added successfully",
            "status_code" => 201,
        ], 201);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function getAllLocations(Request $request) : JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::setTarget($user -> profile -> preferred_language);



        $validator = Validator::make($request->all(), [
            "governorate" => 'required' , //'all' 'Damascus' , 'Homs' , 'Tartus' , 'Aleppo' , 'Suwayda' , 'Daraa' , 'Raqqa'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::translateToEnglishOnly($request->governorate) ;

        $results = [];
        if($request->input('governorate') == 'null')
        {
            $results = Location::query()->select('id' , 'name' , 'governorate' , 'capacity' , 'open_time' , 'close_time' , 'logo' , 'maintenance')->get();

            if($results->isEmpty())
            {
                return response()->json([
                    "message" => "There are no locations to display.",
                    "status_code" => 404,
                ], 404);
            }
        }
        elseif ($request->input('governorate') != 'null')
        {
            $results = Location::query()
                ->where('governorate' , TranslateTextHelper::translateToEnglishOnly($request->input('governorate')))
                ->select('id' , 'name' , 'governorate' , 'capacity' , 'open_time' , 'close_time' , 'logo' , 'maintenance')
                ->get();

            if($results->isEmpty())
            {
                return response()->json([
                    "message" => "There are no locations for specific governorate to display",
                    "status_code" => 404,
                ], 404);
            }
        }

        return response()->json($results , 200);

    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function getTheMostLocationReserved():JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        // First, aggregate the reservation counts per location
        $locations = DB::table('user_events')
            ->select('user_events.location_id', DB::raw('COUNT(user_events.id) as reservations_count'))
            ->groupBy('user_events.location_id');

        // Then join with locations and location_pictures to get the name and picture
        $result = DB::table('locations')
            ->joinSub($locations, 'location_counts', function ($join) {
                $join->on('locations.id', '=', 'location_counts.location_id');
            })
            ->leftJoin('location_pictures', 'locations.id', '=', 'location_pictures.location_id')
            ->select('locations.name', DB::raw('MIN(location_pictures.picture) as picture'), 'location_counts.reservations_count')
            ->groupBy('locations.id', 'locations.name', 'location_counts.reservations_count')
            ->orderByDesc('location_counts.reservations_count')
            ->limit(3) // Limit the result to the top 3 locations
            ->get();

        $response = [];
        foreach ($result as $res)
        {
            $response [] = [
                "name" => $res->name,
                "picture" => env('APP_URL').'/'.$res->picture,
                "reservations_count" => $res->reservations_count
            ];
        }
        return response()->json($response , 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    public function getAllLocationsSelect():JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $locations = Location::query()->select('id' , 'name')->get();
        if(!$locations)
        {
            return response()->json([
                'message' => 'There is no locations to manage it.',
                'status_code' => 404
            ], 404);
        }
        return response()->json($locations, 200);
    }
}
