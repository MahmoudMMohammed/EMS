<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Accessory;
use App\Models\AccessoryCategory;
use App\Models\Favorite;
use App\Models\Location;
use App\Models\Warehouse;
use App\Models\WarehouseAccessory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccessoryController extends Controller
{
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
    public function WebGetAccessoriesByCategory($accessory_id) : JsonResponse
    {
        $user = Auth::user();
        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        if($accessory_id < 0 || $accessory_id > 7)
        {
            return response()->json([
                "error" => "invalid accessory ID must be between 0 and 7",
                "status_code" => 422,
            ], 422);
        }

        $results = [];
        if($accessory_id == 0)
        {
            $results = Accessory::query()->get();

            if($results->isEmpty())
            {
                return response()->json([
                    "message" => "There are no accessories.",
                    "status_code" => 404,
                ], 404);
            }
        }
        elseif (in_array($accessory_id , range(1,7)))
        {
            $results = Accessory::query()
                ->where('accessory_category_id' , $accessory_id)
                ->get();

            if($results->isEmpty())
            {
                return response()->json([
                    "message" => "There are no accessories for this specific category.",
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
                'accessory_category' => $result->category->category ,
                'picture' => $result->picture
            ];
        }

        return response()->json($response , 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebGetAccessoriesCount():JsonResponse
    {
        $count = Accessory::query()->count();
        $response = ['count' => $count.' Item'];
        return response()->json($response);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebGetAccessoriesGeneral(Request $request):JsonResponse
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
            'accessory_id' => 'required|integer|exists:accessories,id' ,
            'warehouse' => 'required|in:1,2,3,4,5,6,7' //1:'Damascus' , 2:'Homs' , 3:'Tartus' , 4:'Aleppo' , 5:'Suwayda' , 6:'Daraa' , 7:'Raqqa'
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }


        $governorate = WarehouseAccessory::query()
            ->where('warehouse_id' , $request->warehouse)
            ->where('accessory_id' , $request->accessory_id)
            ->first();

        $details = Accessory::query()->where('id' , $request->accessory_id)->first();

        $response = [
            'id' => $details->id,
            'accessory_category' => $details->category->category ,
            'ware_house' => $governorate->warehouse->governorate
        ];

        return response()->json($response ,200);
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

    public function deleteAccessory($accessory_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $accessory = Accessory::findOrFail($accessory_id);
        if ($this->checkModelUsage($accessory)){
            return response()->json([
                "error" => TranslateTextHelper::translate("Cannot delete this accessory as it is used in pending or confirmed events."),
                "status_code" => 400
            ], 400);
        }
        $accessory->delete();
        return response()->json([
            "error" => TranslateTextHelper::translate("Accessory deleted successfully."),
            "status_code" => 200
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebGetDrinksDetails(Request $request) :JsonResponse
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
            'accessory_id' => 'required|integer|exists:accessories,id' ,
            'warehouse' => 'required|in:1,2,3,4,5,6,7' //1:'Damascus' , 2:'Homs' , 3:'Tartus' , 4:'Aleppo' , 5:'Suwayda' , 6:'Daraa' , 7:'Raqqa'
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $quantity = WarehouseAccessory::query()
            ->where('warehouse_id' , $request->warehouse)
            ->where('accessory_id' , $request->accessory_id)
            ->first();

        $details = Accessory::query()->where('id' , $request->accessory_id)->first();

        $response = [
            'id' => $details->id,
            'name' => $details->name ,
            'price' => $details->price,
            'quantity' => $quantity->quantity ,
            'description' => $details->description
        ];

        return response()->json($response ,200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebEditAccessoriesDetails(Request $request):JsonResponse
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
            'accessory_id' => 'required|integer|exists:accessories,id' ,
            'warehouse' => 'required|in:1,2,3,4,5,6,7' , //1:'Damascus' , 2:'Homs' , 3:'Tartus' , 4:'Aleppo' , 5:'Suwayda' , 6:'Daraa' , 7:'Raqqa'
            'name' => 'sometimes|max:50' ,
            'price' => 'sometimes|integer|doesnt_start_with:0|max:1000000000|min:1' ,
            'quantity' => 'sometimes|integer|doesnt_start_with:0|max:1000000000|min:1',
            'description' => 'sometimes|string' ,
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $accessory = Accessory::query()->where('id' , $request->accessory_id)->first();

        $warehouseAccessory = WarehouseAccessory::query()
            ->where('warehouse_id' , $request->warehouse)
            ->where('accessory_id' , $request->accessory_id)
            ->first();


        $dataToUpdateAccessory = [];
        $dataToUpdateQuantity = [];

        if ($request->has('name')) {
            $dataToUpdateAccessory['name'] = $request->input('name');
        }
        if ($request->has('price')) {
            $dataToUpdateAccessory['price'] = $request->input('price');
        }
        if ($request->has('description')) {
            $dataToUpdateAccessory['description'] = $request->input('description');
        }
        if ($request->has('quantity')) {
            $dataToUpdateQuantity['quantity'] = $request->input('quantity');
        }

        if (empty($dataToUpdateAccessory) && empty($dataToUpdateQuantity)) {
            return response()->json([
                "message" => "You haven't made any changes",
                "status_code" => 404,
            ],404);
        }

        $accessory->update($dataToUpdateAccessory);
        $warehouseAccessory->update($dataToUpdateQuantity);

        return response()->json([
            "message" => "accessory details updated successfully",
            "status_code" => 200,
        ], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function WebAddAccessory(Request $request):JsonResponse
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
            'warehouse' => 'required|in:1,2,3,4,5,6,7' , //1:'Damascus' , 2:'Homs' , 3:'Tartus' , 4:'Aleppo' , 5:'Suwayda' , 6:'Daraa' , 7:'Raqqa'
            'name' => 'required|max:50' ,
            'price' => 'required|integer|doesnt_start_with:0|max:1000000000|min:1' ,
            'quantity' => 'required|integer|doesnt_start_with:0|max:1000000000|min:1',
            'description' => 'required|string' ,
            'accessory_category_id' => 'required|integer|exists:accessory_categories,id',
            'picture' => 'required|image'
        ]);

        if($validator->fails())
        {
            return response()->json([
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ], 422);
        }

        $publicPath = public_path("Accessories/Added");

        $PicturePath = 'Accessories/Added/' . $request->file('picture')->getClientOriginalName();
        $request->file('picture')->move($publicPath, $PicturePath);

        $accessory = Accessory::query()->create([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'accessory_category_id' => $request->input('accessory_category_id'),
            'description' => $request->input('description'),
            'picture' => $PicturePath,
        ]);

        $accessory_warehouse = WarehouseAccessory::query()->create([
            'warehouse_id' => $request->input('warehouse'),
            'accessory_id' => $accessory->id ,
            'quantity' => $request->input('quantity')
        ]);

        if(!$accessory || !$accessory_warehouse)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        return response()->json([
            "message" => "accessory added successfully",
            "status_code" => 201,
        ], 201);
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function getAllAccessories(Request $request):JsonResponse
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
        $query = Accessory::query();

        // If a type is provided and it's not -1, filter the results
        if ($request->type && !$isTypeNull) {
            $query->where('price', '<=', $request->type);
        }

        // Retrieve the food items
        $accessories = $query->orderby('price')->get();

        // Check if any food items were found
        if ($accessories->isEmpty()) {
            $errorMessage = $request->type && !$isTypeNull
                ? TranslateTextHelper::translate("No accessories found for the specified price")
                : TranslateTextHelper::translate("No accessories found in application");

            return response()->json([
                "error" => $errorMessage,
                "status_code" => 404,
            ], 404);
        }

        $name = $accessories->pluck('name')->toArray();
        $name = TranslateTextHelper::batchTranslate($name);

        $description = $accessories->pluck('description')->toArray();
        $description = TranslateTextHelper::batchTranslate($description);

        $foodsIds = $accessories->pluck('id')->toArray();

        $favorites = Favorite::query()
            ->where('favoritable_type', 'App\Models\Food')
            ->whereIn('favoritable_id', $foodsIds)
            ->pluck('favoritable_id')
            ->toArray();

        $response = [];



        foreach ($accessories as $accessory) {
            $response [] = [
                'id' => $accessory->id,
                'name' => $name[$accessory->name],
                'price' => $accessory->RawPrice,
                'currency' => 'S.P',
                'description' => $description[$accessory->description],
                'picture' => $accessory->picture,
                'category' => TranslateTextHelper::translate($accessory->category->category) ,
                'is_favorite' => in_array($accessory->id, $favorites),
            ];
        }
        return response()->json($response, 200);
    }
}
