<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Favorite;
use App\Models\Food;
use App\Models\FoodCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FoodController extends Controller
{
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

}
