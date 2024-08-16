<?php

namespace App\Http\Controllers;

use App\Helpers\CurrencyConverterScraper;
use App\Helpers\TranslateTextHelper;
use App\Models\Drink;
use App\Models\DrinkCategory;
use App\Models\Favorite;
use App\Traits\ModelUsageCheck;
use App\Traits\RegistrationData;
use App\Traits\SalesData;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DrinkController extends Controller
{
    use RegistrationData,SalesData,ModelUsageCheck;
    public function getDrinksByCategory($category_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $drinkCategory = DrinkCategory::find($category_id);
        if (!$drinkCategory){
            return response()->json([
                "error" => TranslateTextHelper::translate("Category not found!"),
                "status_code" => "404"
            ],404);
        }

        $drinks = Drink::select('id','name','price','picture', 'description')->whereDrinkCategoryId($category_id)->get();

        $names = $drinks->pluck('name')->values()->toArray();
        $descriptions = $drinks->pluck('description')->values()->toArray();

        $translatedNames = TranslateTextHelper::batchTranslate($names);
        $translatedDescriptions = TranslateTextHelper::batchTranslate($descriptions);

        if (!$drinks->count() > 0){
            return response()->json([
                "error" => TranslateTextHelper::translate("No drinks to show!"),
                "status_code" => "404"
            ],404);
        }

        foreach ($drinks as $item){
            $item->name = $translatedNames[$item->name];
            $item->description = $translatedDescriptions[$item->description];
        }

        return response()->json($drinks,200);
    }

    ////////////////////////////////////////////////////////////////////////////////

    public function getDrinkById($drink_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $drink = Drink::with('drinkCategory')->find($drink_id);
        if (!$drink){
            return response()->json([
                "error" => "Drink not found!",
                "status_code" => 404,
            ], 404);
        }
        $drinkData = [
            "id" => $drink->id,
            "name" => TranslateTextHelper::translate($drink->name),
            "price" => $drink->price,
            "category" => TranslateTextHelper::translate($drink->drinkCategory->category),
            "description" => TranslateTextHelper::translate($drink->description),
            "picture" => $drink->picture,
        ];
        return response()->json($drinkData, 200);
    }

    ////////////////////////////////////////////////////////////////////////////////
    public function getDrinksCategories(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $drinksCategories = DrinkCategory::all();

        if (!$drinksCategories){
            return response()->json([
                "error" => TranslateTextHelper::translate("No drinks categories to show!"),
                "status_code" => "404"
            ],404);
        }
        $names = $drinksCategories->pluck('category')->values()->toArray();
        $translatedNames = TranslateTextHelper::batchTranslate($names);

        foreach ($drinksCategories as $category){
            $category->category = $translatedNames[$category->category];
        }
        return response()->json($drinksCategories,200);
    }

    ////////////////////////////////////////////////////////////////////////////////
    public function getDrinksByCategorySorted(Request $request): JsonResponse
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

        $validator = Validator::make($request->all() , [
            "category_id" => 'required|integer|exists:drink_categories,id',
            "type" => 'required|integer|between:-1,999999'
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $isTypeNull = $request->type == '-1' ;

        $query = Drink::query()->where('drink_category_id' , $request->category_id);

        if(!$query->count() > 0)
        {
            return response()->json([
                "error" => TranslateTextHelper::translate("No drinks found for the specified category"),
                "status_code" => 404,
            ], 404);
        }

        if($request->type && !$isTypeNull)
        {
            $query->where('price' , '<=' , $request->type);
        }

        $drinks = $query->orderBy('price')->get();

        if($drinks->isEmpty() && $request->type && !$isTypeNull )
        {
            return response()->json([
                'error' =>TranslateTextHelper::translate("No drinks found for the specified price"),
                'status_code' => 404,
            ], 404);
        }

        $name = $drinks->pluck('name')->toArray();
        $name = TranslateTextHelper::batchTranslate($name);

        $description = $drinks->pluck('description')->toArray();
        $description = TranslateTextHelper::batchTranslate($description);

        $drinksIds = $drinks->pluck('id')->toArray();

        $favorites = Favorite::query()
            ->where('favoritable_type' , 'App\Models\Drink')
            ->whereIn('favoritable_id' , $drinksIds)
            ->pluck('favoritable_id')
            ->toArray();

        $response = [];
        foreach ($drinks as $drink)
        {
            $response [] = [
                'id' => $drink->id ,
                'name' => $name[$drink->name] ,
                'price' => (float)number_format(CurrencyConverterScraper::convert($drink->RawPrice) , 2) ,
                'currency' =>  $user->profile->preferred_currency ,
                'description' => $description[$drink->description] ,
                'picture' => $drink->picture ,
                'is_favorite' => in_array($drink->id , $favorites)
            ];
        }

        return response()->json($response , 200);
    }

    ////////////////////////////////////////////////////////////////////////////////

    public function updateDrinkPicture(Request $request): JsonResponse
    {
        $owner = Auth::user();
        TranslateTextHelper::setTarget($owner->profile->preferred_language);

        $validator = Validator::make($request->all(), [
            'drink_id' => 'required|exists:drinks,id',
            'picture' => 'required|image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ], 422);
        }

        $drink = Drink::findOrFail($request->drink_id);
        $picture = $drink->getRawOriginal('picture');
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

        $drink->picture = "$directory/$newFilename";
        $drink->save();

        return response()->json([
            "message" => TranslateTextHelper::translate("Drink picture has been updated successfully"),
            "status_code" => 200,
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebGetDrinksByCategory($drink_id):JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        if($drink_id < 0 || $drink_id > 7)
        {
            return response()->json([
                "error" => "invalid drink ID must be between 0 and 7",
                "status_code" => 422,
            ], 422);
        }

        $results = [];
        if($drink_id == 0)
        {
            $results = Drink::query()->get();

            if($results->isEmpty())
            {
                return response()->json([
                    "message" => "There are no drinks.",
                    "status_code" => 404,
                ], 404);
            }
        }
        elseif (in_array($drink_id , range(1,7)))
        {
            $results = Drink::query()
                ->where('drink_category_id' , $drink_id)
                ->get();

            if($results->isEmpty())
            {
                return response()->json([
                    "message" => "There are no drinks for this specific category.",
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
                'drink_category' => $result->category->category ,
                'picture' => $result->picture
            ];
        }

        return response()->json($response , 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebGetDrinksCount():JsonResponse
    {
        $count = Drink::query()->count();
        $response = ['count' => $count.' Item'];
        return response()->json($response);
    }

    ////////////////////////////////////////////////////////////////////////////////
    public function WebGetDrinksGeneral($drink_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Drink::query()->find($drink_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid drink id",
                "status_code" => 422,
            ], 422);
        }

        $response = [
            'id' => $exist->id ,
            'drink_category' => $exist->category->category ,
        ];

        return response()->json($response ,200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebGetDrinksDetails($drink_id):JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Drink::query()->find($drink_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid drink id",
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
    public function WebEditDrinksDetails(Request $request , $drink_id):JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        $exist = Drink::query()->find($drink_id);
        if(!$exist)
        {
            return response()->json([
                "error" => "Invalid drink id",
                "status_code" => 422,
            ], 422);
        }

        $validator = Validator::make($request->all() , [
            'name' => 'required|max:50' ,
            'price' => 'required|regex:/^\d+(\.\d{1,2})?\s+SYP$/' ,
            'description' => 'required|string' ,
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $format = (float)str_replace(['SYP', ',', ' '], '', $request->input('price'));

        $exist->update([
            'name' => $request->input('name'),
            'price' => $format,
            'description' => $request->input('description')
        ]);

        return response()->json([
            "message" => "drink details updated successfully",
            "status_code" => 200,
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebAddDrink(Request $request):JsonResponse
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
            'drink_category_id'=> 'required|integer|exists:drink_categories,id',
            'picture'=> 'required|image',
            'description' => 'required|string',
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $publicPath = public_path("Drinks/Added");

        $PicturePath = 'Drinks/Added/' . $request->file('picture')->getClientOriginalName();
        $request->file('picture')->move($publicPath, $PicturePath);

        $drink = Drink::query()->create([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'drink_category_id' => $request->input('drink_category_id'),
            'description' => $request->input('description'),
            'picture' => $PicturePath,
        ]);

        if(!$drink)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        return response()->json([
            "message" => "drink added successfully",
            "status_code" => 201,
        ], 201);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function getDrinkStatistics($drink_id): array
    {
        $drink = Drink::findOrFail($drink_id);
        $drinkRegistration = $this->getRegistrationInfo($drink);
        $drinkSales = $this->getModelSales($drink);
        return array_merge($drinkRegistration,$drinkSales);
    }

    ////////////////////////////////////////////////////////////////////////////////

    public function deleteDrink($drink_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $drink = Drink::findOrFail($drink_id);
        if ($this->checkModelUsage($drink)){
            return response()->json([
                "error" => TranslateTextHelper::translate("Cannot delete this drink as it is used in pending or confirmed events."),
                "status_code" => 400
            ], 400);
        }
        $drink->delete();
        return response()->json([
            "error" => TranslateTextHelper::translate("Drink deleted successfully."),
            "status_code" => 200
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function getAllDrinks(Request $request):JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        try {

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
            $query = Drink::query();

            // If a type is provided and it's not -1, filter the results
            if ($request->type && !$isTypeNull) {
                $query->where('price', '<=', $request->type);
            }

            // Retrieve the food items
            $drinks = $query->orderby('price')->get();

            // Check if any food items were found
            if ($drinks->isEmpty()) {
                $errorMessage = $request->type && !$isTypeNull
                    ? TranslateTextHelper::translate("No drinks found for the specified price")
                    : TranslateTextHelper::translate("No drinks found in application");

                return response()->json([
                    "error" => $errorMessage,
                    "status_code" => 404,
                ], 404);
            }

            $name = $drinks->pluck('name')->toArray();
            $name = TranslateTextHelper::batchTranslate($name);

            $description = $drinks->pluck('description')->toArray();
            $description = TranslateTextHelper::batchTranslate($description);

            $drinksIds = $drinks->pluck('id')->toArray();

            $favorites = Favorite::query()
                ->where('favoritable_type', 'App\Models\Drink')
                ->whereIn('favoritable_id', $drinksIds)
                ->pluck('favoritable_id')
                ->toArray();

            $response = [];

            foreach ($drinks as $drink) {
                $response [] = [
                    'id' => $drink->id,
                    'name' => $name[$drink->name] ?? $drink->name,
                    'price' => (float)number_format(CurrencyConverterScraper::convert($drink->RawPrice) , 2),
                    'currency' =>  $user->profile->preferred_currency,
                    'description' => $description[$drink->description] ?? $drink->description,
                    'picture' => $drink->picture,
                    'is_favorite' => in_array($drink->id, $favorites),
                ];
            }
            return response()->json($response, 200);
        }
        catch (Exception $e){
            return response()->json(['error' => $e , 'status_code' => 422], 422);
        }
    }
}
