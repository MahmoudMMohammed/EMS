<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Accessory;
use App\Models\AccessoryCategory;
use App\Models\Favorite;
use App\Models\Location;
use App\Models\Warehouse;
use App\Models\WarehouseAccessory;
use App\Traits\RegistrationData;
use App\Traits\SalesData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccessoryController extends Controller
{
    use RegistrationData,SalesData;
    public function getAccessoriesByCategory($category_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $accessoryCategory = AccessoryCategory::find($category_id);
        if (!$accessoryCategory){
            return response()->json([
                "error" => "Category not found!",
                "status_code" => "404"
            ],404);
        }

        $accessories = Accessory::select('id','name','price','picture', 'description')->whereAccessoryCategoryId($category_id)->get();

        $names = $accessories->pluck('name')->values()->toArray();
        $descriptions = $accessories->pluck('description')->values()->toArray();

        $translatedNames = TranslateTextHelper::batchTranslate($names);
        $translatedDescriptions = TranslateTextHelper::batchTranslate($descriptions);

        if (!$accessories->count() > 0){
            return response()->json([
                "error" => TranslateTextHelper::translate("No accessories to show!"),
                "status_code" => "404"
            ],404);
        }
        foreach ($accessories as $item){
            $item->name = $translatedNames[$item->name];
            $item->description = $translatedDescriptions[$item->description];
        }
        return response()->json($accessories,200);
    }

    ////////////////////////////////////////////////////////////////////////////////

    public function getAccessoryById($accessory_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $accessory = Accessory::with('accessoriesCategory')->find($accessory_id);
        if (!$accessory){
            return response()->json([
                "error" => "Accessory not found!",
                "status_code" => 404,
            ], 404);
        }
        $accessoryData = [
            "id" => $accessory->id,
            "name" => TranslateTextHelper::translate($accessory->name),
            "price" => $accessory->price,
            "category" => TranslateTextHelper::translate($accessory->accessoriesCategory->category),
            "description" => TranslateTextHelper::translate($accessory->description),
            "picture" => $accessory->picture,
        ];
        return response()->json($accessoryData, 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function getAccessoriesCategories(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $accessoriesCategories = AccessoryCategory::all();
        if (!$accessoriesCategories){
            return response()->json([
                "error" => TranslateTextHelper::translate("No accessories categories to show!"),
                "status_code" => "404"
            ],404);
        }

        $names = $accessoriesCategories->pluck('category')->values()->toArray();
        $translatedNames = TranslateTextHelper::batchTranslate($names);

        foreach ($accessoriesCategories as $category){
            $category->category = $translatedNames[$category->category];
        }
        return response()->json($accessoriesCategories,200);
    }

    ////////////////////////////////////////////////////////////////////////////////
    public function getAccessoriesByCategorySorted(Request $request): JsonResponse
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
            "category_id" => 'required|integer|exists:drink_categories,id',
            "location_id" => 'required|integer|exists:locations,id',
            "type" => 'required|integer|between:-1,999999'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $isTypeNull = $request->type == '-1';

        $location = Location::query()->where('id', $request->location_id)->pluck('governorate')->first();

        $warehouse = Warehouse::query()->where('governorate', $location)->pluck('id')->first();

        $warehouse_accessories = WarehouseAccessory::query()->where('warehouse_id', $warehouse)->get();

        $accessory_ids = $warehouse_accessories->pluck('accessory_id');

        $accessories = Accessory::query()->whereIn('id', $accessory_ids)
            ->where('accessory_category_id', $request->category_id);

        if (!$accessories->count() > 0) {
            return response()->json([
                "error" => TranslateTextHelper::translate("No accessory found for the specified category"),
                "status_code" => 404,
            ], 404);
        }

        if ($request->type && !$isTypeNull) {
            $accessories->where('price', '<=', $request->type);
        }

        $items = $accessories->orderBy('price')->get();

        if ($items->isEmpty() && $request->type && !$isTypeNull) {
            return response()->json([
                'error' => TranslateTextHelper::translate("No accessory found for the specified price"),
                'status_code' => 404,
            ], 404);
        }

        $name = $items->pluck('name')->toArray();
        $name = TranslateTextHelper::batchTranslate($name);

        $description = $items->pluck('description')->toArray();
        $description = TranslateTextHelper::batchTranslate($description);


        $accessoryIds = $items->pluck('id')->toArray();
        $favorites = Favorite::query()
            ->where('favoritable_type' , 'App\Models\Accessory')
            ->whereIn('favoritable_id' , $accessoryIds)
            ->pluck('favoritable_id')
            ->toArray();

        $response = [];
        foreach ($items as $item) {

            $quantity = $warehouse_accessories->where('accessory_id', $item->id)->pluck('quantity')->first();

            $response[] = [
                'id' => $item->id,
                'name' => $name[$item->name],
                'price' => $item->RawPrice,
                'currency' => 'S.P' ,
                'description' => $description[$item->description],
                'picture' => $item->picture,
                'numOfItem' => $quantity ,
                'is_favorite' => in_array($item->id , $favorites),
            ];
        }

        // Return the response
        return response()->json($response, 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function updateAccessoryPicture(Request $request): JsonResponse
    {
        $owner = Auth::user();
        TranslateTextHelper::setTarget($owner->profile->preferred_language);

        $validator = Validator::make($request->all(), [
            'accessory_id' => 'required|exists:accessories,id',
            'picture' => 'required|image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422,
            ], 422);
        }

        $accessory = Accessory::findOrFail($request->accessory_id);
        $picture = $accessory->getRawOriginal('picture');
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

        $accessory->picture = "$directory/$newFilename";
        $accessory->save();

        return response()->json([
            "message" => TranslateTextHelper::translate("Drink picture has been updated successfully"),
            "status_code" => 200,
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////

    public function getAccessoryStatistics($accessory_id): array
    {
        $accessory = Accessory::findOrFail($accessory_id);
        $accessoryRegistration = $this->getRegistrationInfo($accessory);
        $accessorySales = $this->getModelSales($accessory);
        unset($accessorySales['total_sales']);
        return array_merge($accessoryRegistration,$accessorySales);
    }
    ////////////////////////////////////////////////////////////////////////////////

}
