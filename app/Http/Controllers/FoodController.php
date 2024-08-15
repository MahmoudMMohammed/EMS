<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Favorite;
use App\Models\Food;
use App\Models\FoodCategory;
use App\Models\Location;
use App\Traits\ModelUsageCheck;
use App\Traits\RegistrationData;
use App\Traits\SalesData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class FoodController extends Controller
{
    use RegistrationData,SalesData,ModelUsageCheck;
    public function getFoodByCategory($category_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $foodCategory = FoodCategory::find($category_id);
        if (!$foodCategory){
            return response()->json([
                "error" => TranslateTextHelper::translate("Category not found!"),
                "status_code" => "404"
            ],404);
        }
        $food = Food::select('id','name','price','picture', 'description', 'country_of_origin')->whereFoodCategoryId($category_id)->get();

        $names = $food->pluck('name')->values()->toArray();
        $descriptions = $food->pluck('description')->values()->toArray();
        $countries = $food->pluck('country_of_origin')->values()->toArray();

        $translatedNames = TranslateTextHelper::batchTranslate($names);
        $translatedDescriptions = TranslateTextHelper::batchTranslate($descriptions);
        $translatedCountries = TranslateTextHelper::batchTranslate($countries);

        if (!$food->count() > 0){
            return response()->json([
                "error" => TranslateTextHelper::translate("No food to show!"),
                "status_code" => "404"
            ],404);
        }

        foreach ($food as $item){
            $item->name = $translatedNames[$item->name];
            $item->description = $translatedDescriptions[$item->description];
            $item->country_of_origin = $translatedCountries[$item->country_of_origin];
        }
        return response()->json($food,200);
    }

    ////////////////////////////////////////////////////////////////////////////////

    public function getFoodById($food_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $food = Food::with('foodCategory')->find($food_id);
        if (!$food){
            return response()->json([
                "error" => TranslateTextHelper::translate("Food not found!"),
                "status_code" => 404,
            ], 404);
        }
        $foodData = [
            "id" => $food->id,
            "name" => TranslateTextHelper::translate($food->name),
            "price" => $food->price,
            "category" => TranslateTextHelper::translate($food->foodCategory->category),
            "description" => TranslateTextHelper::translate($food->description),
            "country_of_origin" => TranslateTextHelper::translate($food->country_of_origin),
            "picture" => $food->picture,
        ];
        return response()->json($foodData, 200);
    }

    ////////////////////////////////////////////////////////////////////////////////
    public function getFoodCategories(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $foodCategories = FoodCategory::all();
        if (!$foodCategories){
            return response()->json([
                "error" => TranslateTextHelper::translate("No food categories to show!"),
                "status_code" => "404"
            ],404);
        }
        $names = $foodCategories->pluck('category')->values()->toArray();
        $translatedNames = TranslateTextHelper::batchTranslate($names);

        foreach ($foodCategories as $category){
            $category->category = $translatedNames[$category->category];
        }

        return response()->json($foodCategories,200);
    }

    ////////////////////////////////////////////////////////////////////////////////
    public function getFoodByCategorySorted(Request $request): JsonResponse
    {
        $user = Auth::user();

        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::setTarget($user -> profile -> preferred_language);

        $validator = Validator::make($request->all(), [
            "category_id" => 'required|integer|exists:food_categories,id',
            "type" => 'required|integer|between:-1,999999'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $isTypeNull = $request->type == '-1' ;

        $query = Food::query()->where('food_category_id' , $request->category_id);

        if (!$query->count() > 0) {
            return response()->json([
                "error" => TranslateTextHelper::translate("No food found for the specified category"),
                "status_code" => 404,
            ], 404);
        }

        if($request->type && !$isTypeNull)
        {
            $query->where('price' , '<=' , $request->type);
        }

        $foods = $query->orderBy('price')->get();


        if ($foods->isEmpty() && $request->type  && !$isTypeNull) {
            return response()->json([
                'error' =>TranslateTextHelper::translate("No food found for the specified price"),
                'status_code' => 404,
            ], 404);
        }

        $name = $foods->pluck('name')->toArray();
        $name = TranslateTextHelper::batchTranslate($name);

        $description = $foods->pluck('description')->toArray();
        $description = TranslateTextHelper::batchTranslate($description);

        $country_of_origin = $foods->pluck('country_of_origin')->toArray();
        $country_of_origin = TranslateTextHelper::batchTranslate($country_of_origin);


        $foodsIds = $foods->pluck('id')->toArray();
        $favorites = Favorite::query()
            ->where('favoritable_type' , 'App\Models\Food')
            ->whereIn('favoritable_id' , $foodsIds)
            ->pluck('favoritable_id')
            ->toArray();


        $response = [];

        foreach ($foods as $food)
        {
            $response [] = [
                'id' => $food -> id ,
                'name' => $name[$food-> name] ,
                'price' => $food->RawPrice ,
                'currency' => 'S.P' ,
                'description' => $description[$food -> description] ,
                'country_of_origin' => $country_of_origin[$food -> country_of_origin] ,
                'picture' => $food -> picture,
                'is_favorite' => in_array($food->id , $favorites),
            ];
        }

        return response()->json($response , 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function updateFoodPicture(Request $request): JsonResponse
    {
        $owner = Auth::user();
        TranslateTextHelper::setTarget($owner->profile->preferred_language);

        $validator = Validator::make($request->all(), [
            'food_id' => 'required|exists:food,id',
            'picture' => 'required|image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ], 422);
        }

        $food = Food::findOrFail($request->food_id);
        $picture = $food->getRawOriginal('picture');
        $filenameWithoutExtension = pathinfo($picture, PATHINFO_FILENAME);
        $directory = pathinfo($picture, PATHINFO_DIRNAME);



        //delete the old file
        if ($picture) {
            $currentPicturePath = public_path($directory) .'/'. $filenameWithoutExtension . '.' . pathinfo($picture, PATHINFO_EXTENSION) ;
            if (file_exists($currentPicturePath)) {
                unlink($currentPicturePath);
            }
        }

        $image = $request->file('picture');
        $newFilename = $filenameWithoutExtension . '.' . $image->getClientOriginalExtension() ;
        $destination = public_path($directory);
        $image->move($destination, $newFilename);

        $food->picture = "$directory/$newFilename";
        $food->save();

        return response()->json([
            "message" => TranslateTextHelper::translate("Food picture has been updated successfully"),
            "status_code" => 200,
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebGetFoodByCategory($category_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        if($category_id < 0 || $category_id > 12)
        {
            return response()->json([
                "error" => "invalid category ID must be between 0 and 12",
                "status_code" => 422,
            ], 422);
        }

        $results = [];
        if($category_id == 0)
        {
            $results = Food::query()->get();

            if($results->isEmpty())
            {
                return response()->json([
                    "message" => "There are no food.",
                    "status_code" => 404,
                ], 404);
            }
        }
        elseif (in_array($category_id , range(1,12)))
        {
            $results = Food::query()
                ->where('food_category_id' , $category_id)
                ->get();

            if($results->isEmpty())
            {
                return response()->json([
                    "message" => "There are no food for this specific category.",
                    "status_code" => 404,
                ], 404);
            }
        }

        $response = [];
        foreach ($results as $result)
        {
            $response [] = [
                'id' => $result->id ,
                'name' => $result->name ,
                'price' =>$result->price ,
                'food_category' => $result->category->category ,
                'country_of_origin' => $result->country_of_origin ,
                'picture' => $result->picture
            ];
        }

        return response()->json($response , 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebGetFoodCount():JsonResponse
    {
        $count = Food::query()->count();
        $response = ['count' => $count.' Item'];
        return response()->json($response);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebGetFoodGeneral($food_id):JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Food::query()->find($food_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid food id",
                "status_code" => 422,
            ], 422);
        }

        $response = [
            'id' => $exist->id ,
            'food_category' => $exist->category->category ,
            'country_of_origin' => $exist->country_of_origin ,
        ];

        return response()->json($response ,200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebGetFoodDetails($food_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Food::query()->find($food_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid food id",
                "status_code" => 422,
            ], 422);
        }

        $response = [
            'id' => $exist->id ,
            'name' => $exist->name ,
            'price' => $exist->price ,
            'description' => $exist->description
        ];

        return response()->json($response ,200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebEditFoodDetails(Request $request , $food_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Food::query()->find($food_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid food id",
                "status_code" => 422,
            ], 422);
        }

        $validator = Validator::make($request->all() , [
            'name' => 'sometimes|max:50' ,
            'price' => 'required|ends_with: S.P' ,
            'description' => 'sometimes|string' ,
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
            'price' => $format,
            'description' => $request->input('description')
        ]);

        return response()->json([
            "message" => "food details updated successfully",
            "status_code" => 200,
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebAddFood(Request $request):JsonResponse
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
            'price'=> 'required|integer|doesnt_start_with:0|max:1000000000|min:1' ,
            'food_category_id'=> 'required|integer|exists:food_categories,id',
            'picture'=> 'required|image',
            'description' => 'required|string',
            'country_of_origin' => 'required|alpha' ,
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $publicPath = public_path("Food/Added");

        $PicturePath = 'Food/Added/' . $request->file('picture')->getClientOriginalName();
        $request->file('picture')->move($publicPath, $PicturePath);

        $food = Food::query()->create([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'food_category_id' => $request->input('food_category_id'),
            'description' => $request->input('description'),
            'country_of_origin' => $request->input('country_of_origin'),
            'picture' => $PicturePath,
        ]);

        if(!$food)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        return response()->json([
            "message" => "food added successfully",
            "status_code" => 201,
        ], 201);
    }

    ////////////////////////////////////////////////////////////////////////////////

    public function getFoodStatistics($food_id): array
    {
        $food = Food::findOrFail($food_id);
        $foodRegistration = $this->getRegistrationInfo($food);
        $foodSales = $this->getModelSales($food);
        return array_merge($foodRegistration,$foodSales);
    }
    ////////////////////////////////////////////////////////////////////////////////

    public function deleteFood($food_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $food = Food::findOrFail($food_id);
        if ($this->checkModelUsage($food)){
            return response()->json([
                "error" => TranslateTextHelper::translate("Cannot delete this food as it is used in pending or confirmed events."),
                "status_code" => 400
            ], 400);
        }
        $food->delete();
        return response()->json([
            "error" => TranslateTextHelper::translate("Food deleted successfully."),
            "status_code" => 200
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function getAllFood(Request $request):JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $validator = Validator::make($request->all(), [
            "type" => 'required|integer|between:-1,999999'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $isTypeNull = $request->type == '-1';

        // Start with the base query
        $query = Food::query();

        // If a type is provided and it's not -1, filter the results
        if ($request->type && !$isTypeNull) {
            $query->where('price', '<=', $request->type);
        }

        // Retrieve the food items
        $foods = $query->orderby('price')->get();

        // Check if any food items were found
        if ($foods->isEmpty()) {
            $errorMessage = $request->type && !$isTypeNull
                ? TranslateTextHelper::translate("No food found for the specified price")
                : TranslateTextHelper::translate("No food found in application");

            return response()->json([
                "error" => $errorMessage,
                "status_code" => 404,
            ], 404);
        }

            $name = $foods->pluck('name')->toArray();
            $name = TranslateTextHelper::batchTranslate($name);

            $description = $foods->pluck('description')->toArray();
            $description = TranslateTextHelper::batchTranslate($description);

            $country_of_origin = $foods->pluck('country_of_origin')->toArray();
            $country_of_origin = TranslateTextHelper::batchTranslate($country_of_origin);


            $foodsIds = $foods->pluck('id')->toArray();

            $favorites = Favorite::query()
                ->where('favoritable_type', 'App\Models\Food')
                ->whereIn('favoritable_id', $foodsIds)
                ->pluck('favoritable_id')
                ->toArray();

            $response = [];

            foreach ($foods as $food) {
                $response [] = [
                    'id' => $food->id,
                    'name' => $name[$food->name] ?? $food->name,
                    'price' => $food->RawPrice,
                    'currency' => 'S.P',
                    'description' => $description[$food->description] ?? $food->description,
                    'country_of_origin' => $country_of_origin[$food->country_of_origin] ?? $food->country_of_origin,
                    'picture' => $food->picture,
                    'is_favorite' => in_array($food->id, $favorites),
                ];
            }
            return response()->json($response, 200);
    }
}
