<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\AccessoryCategory;
use App\Models\Cart;
use App\Models\DrinkCategory;
use App\Models\EventSupplement;
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


        if (!empty($declinedItems)) {
            $numberOfDeclinedItems = sizeof($declinedItems);
            return response()->json([
                "message" => TranslateTextHelper::translate("Process saved, But there are $numberOfDeclinedItems declined items because they are not suitable for the host, if you want to see them Click Here!"),
                "status_code" => 200,
            ], 200);
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
                "status_code" => 200,
            ], 200);
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

    private function parsePrice($priceString): float
    {
        $cleanedPrice = preg_replace('/[^0-9.,]/', '', $priceString);
        $cleanedPrice = str_replace(',', '', $cleanedPrice);
        return floatval($cleanedPrice);
    }
    /////////////////////////////////////////////////
}
