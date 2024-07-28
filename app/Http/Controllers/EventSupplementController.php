<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Accessory;
use App\Models\AccessoryCategory;
use App\Models\Cart;
use App\Models\Drink;
use App\Models\DrinkCategory;
use App\Models\EventSupplement;
use App\Models\Food;
use App\Models\FoodCategory;
use App\Models\HostDrinkCategory;
use App\Models\HostFoodCategory;
use App\Models\Location;
use App\Models\MainEventHost;
use App\Models\MEHAC;
use App\Models\UserEvent;
use App\Models\Warehouse;
use App\Models\WarehouseAccessory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use function Symfony\Component\String\s;

class EventSupplementController extends Controller
{
    public function getSupplementsForEvent($event_id): JsonResponse
    {
        $user = Auth::user();
        $event = UserEvent::find($event_id);
        TranslateTextHelper::setTarget($user->profile->preferred_language);
        if (!$event){
            return response()->json([
                "error" => TranslateTextHelper::translate("Event not found!"),
                "status_code" => 404
            ],404);
        }
        if ($event->user_id != $user->id ){
            return response()->json([
                "error" => TranslateTextHelper::translate("Event is not yours to show!"),
                "status_code" => 403
            ],403);
        }
        $supplements = EventSupplement::whereUserEventId($event->id)->first();

        if (!$supplements){
            return response()->json([
                "error" => TranslateTextHelper::translate("You have not ordered any supplements for your event!"),
                "status_code" => 404
            ],404);
        }

        unset($supplements['id']);
        unset($supplements['user_event_id']);
        unset($supplements['warehouse_id']);

        return response()->json($supplements);
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////
    public function processFoodAndDrinksSupplements($event_id): JsonResponse
    {
        $event = UserEvent::find($event_id);
        if (!$event) {
            return response()->json([
                "error" => "Event not found!",
                "status_code" => 404
            ], 404);
        }

        $user = Auth::user();
        $cart = Cart::whereUserId($user->id)->first();
        TranslateTextHelper::setTarget($user->profile->preferred_language);
        if (!$cart || $cart->items()->count() == 0) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Cart is empty"),
                "status_code" => 400,
            ], 400);
        }

        $location = Location::find($event->location_id);

        $foodDetails = [];
        $drinksDetails = [];
        $declinedItems = [];
        $totalPrice = 0;

        foreach ($cart->items as $cartItem) {
            $item = $cartItem->itemable;
            $itemType = strtolower(class_basename($item));
            $approved = false;

            switch ($itemType) {
                case 'food':

                    if (HostFoodCategory::where('food_category_id', $item->food_category_id)->where('host_id', $location->host->id)->exists()) {
                        $category = FoodCategory::find($item->food_category_id);
                        $item['category'] = $category->category;
                        unset($item['food_category_id']);
                        $item['quantity'] = $cartItem->quantity;
                        $foodDetails[] = $item;

                        $approved = true;
                    } else {
                        $declinedItems['food'][] = $item;
                    }
                    break;
                case 'drink':
                    if (HostDrinkCategory::where('drink_category_id', $item->drink_category_id)->where('host_id', $location->host->id)->exists()) {
                        $category = DrinkCategory::find($item->drink_category_id);
                        $item['category'] = $category->category;
                        unset($item['drink_category_id']);
                        $item['quantity'] = $cartItem->quantity;
                        $drinksDetails[] = $item;
                        $approved = true;
                    } else {
                        $declinedItems['drink'][] = $item;
                    }
                    break;
                case 'accessory':
                    break;
            }

            if ($approved) {
                $price = $this->parsePrice($cartItem->itemable->price);
                $totalPrice += $price * $cartItem->quantity;
                $cartItem->delete();
            }
        }

        $eventSupplements = EventSupplement::whereUserEventId($event_id)->first();
        $eventSupplements->food_details = json_encode($foodDetails);
        $eventSupplements->drinks_details = json_encode($drinksDetails);
        $eventSupplements->total_price = $totalPrice + $this->parsePrice($eventSupplements->total_price);
        $eventSupplements->save();


        // Merging all arrays into one
        $allItems = array_merge($declinedItems['food'], $declinedItems['drink']);

        if (!empty($allItems)) {
            $numberOfDeclinedItems = count($allItems);
            return response()->json([
                "message" => TranslateTextHelper::translate("Process saved, But there are $numberOfDeclinedItems declined items because they are not suitable for the host, if you want to see them Click Here!"),
                "status_code" => 202,
            ], 202);
        }

        if (empty($foodDetails) && empty($drinksDetails)){
            return response()->json([
                "message" => TranslateTextHelper::translate("No food or drinks found in cart to add them!"),
                "status_code" => 404,
            ], 404);
        }

        return response()->json([
            "message" => TranslateTextHelper::translate("Items added to event's supplements successfully"),
            "status_code" => 200,
        ], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////
    public function processAccessoriesSupplements($event_id): JsonResponse
    {
        $event = UserEvent::find($event_id);
        if (!$event){
            return response()->json([
                "error" => "Event not found!",
                "status_code" => 404
            ],404);
        }

        $user = Auth::user();
        $cart = Cart::whereUserId($user->id)->first();
        TranslateTextHelper::setTarget($user->profile->preferred_language);
        if (!$cart || $cart->items()->count() == 0) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Cart is empty"),
                "status_code" => 400,
            ], 400);
        }

        $location = Location::find($event->location_id);

        $accessoriesDetails = [];
        $declinedItems = [];
        $totalPrice = 0;

        foreach ($cart->items as $cartItem) {
            $item = $cartItem->itemable;
            $itemType = strtolower(class_basename($item));
            $approved = false;

            switch ($itemType) {
                case 'drink':
                case 'food':
                    break;
                case 'accessory':
                    $mainEventHost = MainEventHost::whereHostId($location->host->id)->pluck('id');
                    $warehouse = Warehouse::whereGovernorate($location->governorate)->first();
                    $availableQuantityInWarehouse = WarehouseAccessory::whereAccessoryId($item->id)
                        ->whereWarehouseId($warehouse->id)
                        ->pluck('quantity');


                    if (MEHAC::where('accessory_category_id', $item->accessory_category_id)->whereIn('main_event_host_id', $mainEventHost)->exists() &&
                        $cartItem->quantity <= $availableQuantityInWarehouse[0]) {
                        $category = AccessoryCategory::find($item->accessory_category_id);
                        $item['category'] = $category->category;
                        unset($item['accessory_category_id']);
                        $item['quantity'] = $cartItem->quantity;
                        $accessoriesDetails[] = $item;
                        $approved = true;
                    } else {
                        $declinedItems[] = $item;
                    }
                    break;
            }

            if ($approved) {
                $price = $this->parsePrice($cartItem->itemable->price);
                $totalPrice += $price * $cartItem->quantity;
                $cartItem->delete();
            }
        }
        $eventSupplements = EventSupplement::whereUserEventId($event_id)->first();
        $eventSupplements->accessories_details = json_encode($accessoriesDetails);
        $eventSupplements->total_price = $totalPrice + $this->parsePrice($eventSupplements->total_price);
        $eventSupplements->save();

        if (!empty($declinedItems)){
            $numberOfDeclinedItems = sizeof($declinedItems);
            return response()->json([
                "message" => TranslateTextHelper::translate("Process saved, But there are $numberOfDeclinedItems declined items because they are not suitable for the host or quantity not available, if you want to see them Click Here!"),
                "status_code" => 202,
            ], 202);
        }

        if (empty($accessoriesDetails)){
            return response()->json([
                "message" => TranslateTextHelper::translate("No accessories found in cart to add them!"),
                "status_code" => 404,
            ], 404);
        }

        return response()->json([
            "message" => TranslateTextHelper::translate("Items added to event's supplements successfully"),
            "status_code" => 200,
        ], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function getDeclinedFoodAndDrinks(): JsonResponse
    {
        $user = Auth::user();
        $cart = Cart::whereUserId($user->id)->first();
        TranslateTextHelper::setTarget($user->profile->preferred_language);
        if (!$cart || $cart->items()->count() == 0) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Cart is empty, No declined items!"),
                "status_code" => 400,
            ], 400);
        }

        $declinedItems = [
            'food' => [],
            'drink' => [],
        ];

        foreach ($cart->items as $cartItem) {
            $item = $cartItem->itemable;
            $itemType = strtolower(class_basename($item));

            switch ($itemType) {
                case 'food':
                    unset($item['price']);
                    unset($item['food_category_id']);
                    unset($item['description']);
                    unset($item['country_of_origin']);
                    $declinedItems['food'][] = $item;
                    break;
                case 'drink':
                    unset($item['price']);
                    unset($item['drink_category_id']);
                    unset($item['description']);
                    $declinedItems['drink'][] = $item;
                    break;
                case 'accessory':
                    break;
            }
        }

        return response()->json($declinedItems, 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function getDeclinedAccessories()
    {
        $user = Auth::user();
        $cart = Cart::whereUserId($user->id)->first();
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);
        if (!$cart || $cart->items()->count() == 0) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Cart is empty, No declined items!"),
                "status_code" => 400,
            ], 400);
        }

        $declinedItems = [];

        foreach ($cart->items as $cartItem) {
            $item = $cartItem->itemable;
            $itemType = strtolower(class_basename($item));

            switch ($itemType) {
                case 'food':
                case 'drink':
                    break;
                case 'accessory':
                    unset($item['price']);
                    unset($item['accessory_category_id']);
                    unset($item['description']);
                    $declinedItems[] = $item;
                    break;
            }
        }

        return response()->json($declinedItems, 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function updateSupplement(Request $request): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $validator = Validator::make($request->all(), [
            'event_id' => 'required|integer|exists:user_events,id',
            'item_id' => 'required|integer',
            'item_type' => 'required|string|in:food,drink,accessory',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422
            ], 422);
        }

        $event = UserEvent::find($request->event_id);

        $validationResponse = $this->validateEvent($event, $user);
        if ($validationResponse !== null) {
            return $validationResponse;
        }

        $item = $this->getItem($request->item_type, $request->item_id);

        if (!$item) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Item not found!"),
                "status_code" => 404
            ], 404);
        }

        $supplements = $event->supplements;

        if (!$this->itemExistsInSupplements($item, $supplements)) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Item does not exist in supplements!"),
                "status_code" => 404
            ], 404);
        }

        $itemSupplements = $this->getItemSupplements($item, $supplements);

        foreach ($itemSupplements as &$supplement) {
            if ($supplement['id'] == $item->id) {
                $supplement['quantity'] = $request->quantity;
                break;
            }
        }

        $updated = $this->updateEventSupplements($itemSupplements, $supplements->id, $request->item_type);

        if (!$updated) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Failed to update item quantity!"),
                "status_code" => 400
            ], 400);
        }

        return response()->json([
            "message" => TranslateTextHelper::translate("Item quantity updated successfully"),
            "status_code" => 200
        ], 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function removeSupplement(Request $request): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $validator = Validator::make($request->all(), [
            'event_id' => 'required|integer|exists:user_events,id',
            'item_id' => 'required|integer',
            'item_type' => 'required|string|in:food,drink,accessory'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422
            ], 422);
        }

        $event = UserEvent::find($request->event_id);

        $validationResponse = $this->validateEvent($event, $user);
        if ($validationResponse !== null) {
            return $validationResponse;
        }

        $item = $this->getItem($request->item_type, $request->item_id);

        if (!$item) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Item not found!"),
                "status_code" => 404
            ], 404);
        }

        $supplements = $event->supplements;

        if (!$this->itemExistsInSupplements($item, $supplements)) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Item does not exist in supplements!"),
                "status_code" => 404
            ], 404);
        }

        $itemSupplements = $this->getItemSupplements($item, $supplements);

        // Filter out the item to be removed
        $itemSupplements = array_filter($itemSupplements, function ($supplement) use ($item) {
            return $supplement['id'] !== $item->id;
        });

        $updated = $this->updateEventSupplements($itemSupplements, $supplements->id, $request->item_type);

        if (!$updated) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Failed to remove item!"),
                "status_code" => 400
            ], 400);
        }

        return response()->json([
            "message" => TranslateTextHelper::translate("Item removed successfully"),
            "status_code" => 200
        ], 200);
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function addSupplement(Request $request): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        // Validation rules
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|integer|exists:user_events,id',
            'item_id' => 'required|integer',
            'item_type' => 'required|string|in:food,drink,accessory',
            'quantity' => 'required|integer|min:1'
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                "error" => TranslateTextHelper::translate($validator->errors()->first()),
                "status_code" => 422
            ], 422);
        }

        // Find the event
        $event = UserEvent::find($request->event_id);

        // Check if the event exists
        $validationResponse = $this->validateEvent($event, $user);
        if ($validationResponse !== null) {
            return $validationResponse;
        }

        // Retrieve the item
        $item = $this->getItem($request->item_type, $request->item_id);

        // Check if the item exists
        if (!$item) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Item not found!"),
                "status_code" => 404
            ], 404);
        }

        $type = strtolower(class_basename($item));
        $itemCategoryId = $type . "_category_id";

        $category = $this->getItemCategory($request->item_type, $item[$itemCategoryId]);

        unset($item["$itemCategoryId"]);

        $item['category'] = $category->category;
        $item['quantity'] = intval($request->quantity);


        // Retrieve supplements
        $supplements = $event->supplements;

        // Check if the item already exists in the supplements
        if ($this->itemExistsInSupplements($item, $supplements)) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Item already exists in supplements!"),
                "status_code" => 400
            ], 400);
        }

        // Retrieve the existing item supplements
        $itemSupplements = $this->getItemSupplements($item, $supplements);

        // Add the new item to the supplements
        $itemSupplements[] = $item;

        // Update the event supplements
        $updated = $this->updateEventSupplements($itemSupplements, $supplements->id, $request->item_type);

        // Check if the update was successful
        if (!$updated) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Failed to add item!"),
                "status_code" => 400
            ], 400);
        }

        return response()->json([
            "message" => TranslateTextHelper::translate("Item added successfully"),
            "status_code" => 200
        ], 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function getFoodSupplementsForEvent($event_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);
        $event = UserEvent::find($event_id);

        $validationResponse = $this->validateEvent($event, $user);
        if ($validationResponse !== null) {
            return $validationResponse;
        }

        $foodSupplements = $event->supplements->food_details;
        if (!$foodSupplements) {
            return response()->json([
                "error" => TranslateTextHelper::translate("No food supplements added to this event!"),
                "status_code" => 404
            ], 404);
        }

        return response()->json($foodSupplements);
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////
    public function getDrinksSupplementsForEvent($event_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);
        $event = UserEvent::find($event_id);

        $validationResponse = $this->validateEvent($event, $user);
        if ($validationResponse !== null) {
            return $validationResponse;
        }

        $drinksSupplements = $event->supplements->drinks_details;
        if (!$drinksSupplements){
            return response()->json([
                "error" => TranslateTextHelper::translate("No drinks supplements added to this event!"),
                "status_code" => 404
            ],404);
        }
        return response()->json($drinksSupplements);

    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////
    public function getAccessoriesSupplementsForEvent($event_id): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);
        $event = UserEvent::find($event_id);

        $validationResponse = $this->validateEvent($event, $user);
        if ($validationResponse !== null) {
            return $validationResponse;
        }

        $accessoriesSupplements = $event->supplements->accessories_details;
        if (!$accessoriesSupplements){
            return response()->json([
                "error" => TranslateTextHelper::translate("No accessories supplements added to this event!"),
                "status_code" => 404
            ],404);
        }
        return response()->json($accessoriesSupplements);

    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    private function parsePrice($priceString): float
    {
        $cleanedPrice = preg_replace('/[^0-9.,]/', '', $priceString);
        $cleanedPrice = str_replace(',', '', $cleanedPrice);
        return floatval($cleanedPrice);
    }
    /////////////////////////////////////////////////

    private function getItem($type, $itemId)
    {
        return match ($type) {
            'food' => Food::find($itemId),
            'drink' => Drink::find($itemId),
            'accessory' => Accessory::find($itemId),
            default => null,
        };
    }
    /////////////////////////////////////////////////
    private function getItemCategory($type, $itemCategoryId)
    {
        return match ($type) {
            'food' => FoodCategory::find($itemCategoryId),
            'drink' => DrinkCategory::find($itemCategoryId),
            'accessory' => AccessoryCategory::find($itemCategoryId),
            default => null,
        };
    }
    /////////////////////////////////////////////////

    private function getItemSupplements($item, $supplements)
    {
        return match (class_basename($item)) {
            'Food' => $supplements->food_details,
            'Drink' => $supplements->drinks_details,
            'Accessory' => $supplements->accessories_details,
            default => [],
        };
    }
    /////////////////////////////////////////////////

    private function updateEventSupplements($updatedSupplements, $supplement_id, $type)
    {
        return match ($type) {
            'food' => EventSupplement::where('id', $supplement_id)->update(
                ["food_details" => json_encode($updatedSupplements)]
            ),
            'drink' => EventSupplement::where('id', $supplement_id)->update(
                ["drinks_details" => json_encode($updatedSupplements)]
            ),
            'accessory' => EventSupplement::where('id', $supplement_id)->update(
                ["accessories_details" => json_encode($updatedSupplements)]
            ),
            default => false,
        };
    }
    /////////////////////////////////////////////////

    private function itemExistsInSupplements($item, $supplements): bool
    {
        $itemSupplements = $this->getItemSupplements($item, $supplements);

        // Check if the item supplements are empty
        if (empty($itemSupplements)) {
            return false; // Indicates that the supplements are empty
        }

        foreach ($itemSupplements as $supplement) {
            if ($supplement['id'] == $item->id) {
                return true;
            }
        }

        return false;
    }
    /////////////////////////////////////////////////
    private function validateEvent($event, $user): ?JsonResponse
    {
        if (!$event) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Event not found!"),
                "status_code" => 404
            ], 404);
        }

        if ($event->user_id != $user->id) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Event is no yours to show!"),
                "status_code" => 403
            ], 403);
        }

        return null; // Indicating validation passed
    }
    /////////////////////////////////////////////////

}
