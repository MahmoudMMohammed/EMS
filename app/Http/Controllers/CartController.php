<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Accessory;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Drink;
use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);
        $validationError = $this->validateCartItem($request);
        if ($validationError) {
            return response()->json(TranslateTextHelper::translate($validationError), 422);
        }

        $data = $request->all();
        $cart = $this->getCart();
        $item = $this->getItem($data['type'], $data['item_id']);

        if ($item) {
            $cartItem = $cart->items()
                ->where('itemable_type', get_class($item))
                ->where('itemable_id', $item->id)
                ->first();

            if ($cartItem) {
                // Update the quantity
                $cartItem->quantity += $data['quantity'];
                $cartItem->save();
                return response()->json([
                    "message" => TranslateTextHelper::translate("Item quantity updated successfully"),
                    "status_code" => 200,
                ],200);
            } else {
                // Add new item to cart
                $cartItem = new CartItem(['quantity' => $data['quantity']]);
                $cartItem->itemable()->associate($item);
                $cart->items()->save($cartItem);
                return response()->json([
                    "message" => TranslateTextHelper::translate("Item added to cart successfully"),
                    "status_code" => 200,
                ],200);
            }

        }
        return response()->json([
            "error" => "Item not found",
            "status_code" => 404,
        ], 404);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////

    public function removeFromCart(Request $request)
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);
        $validationError = $this->validateCartItem($request);
        if ($validationError) {
            return response()->json(TranslateTextHelper::translate($validationError), 422);
        }

        $data = $request->all();
        $cart = $this->getCart();
        $item = $this->getItem($data['type'], $data['item_id']);

        if ($item) {
            $cartItem = $cart->items()
                ->where('itemable_type', get_class($item))
                ->where('itemable_id', $item->id)
                ->first();

            if ($cartItem) {
                $cartItem->delete();

                return response()->json([
                    "message" => TranslateTextHelper::translate("Item removed from cart"),
                    "status_code" => 200,
                ], 200);
            } else {
                return response()->json([
                    "error" => TranslateTextHelper::translate("Item not found in cart"),
                    "status_code" => 404,
                ], 404);
            }
        }

        return response()->json([
            "error" => "Item not found",
            "status_code" => 404,
        ], 404);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////
    public function updateCartQuantity(Request $request)
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $validationError = $this->validateCartItem($request);
        if ($validationError) {
            return response()->json(TranslateTextHelper::translate($validationError), 422);
        }

        $data = $request->all();
        $cart = $this->getCart();
        $item = $this->getItem($data['type'], $data['item_id']);

        if ($item) {
            $cartItem = $cart->items()
                ->where('itemable_type', get_class($item))
                ->where('itemable_id', $item->id)
                ->first();

            if ($cartItem) {
                $cartItem->quantity = $data['quantity'];
                $cartItem->save();

                return response()->json([
                    "message" => TranslateTextHelper::translate("Item quantity updated"),
                    "status_code" => 200,
                ], 200);
            } else {
                return response()->json([
                    "error" => TranslateTextHelper::translate("Item not found in cart"),
                    "status_code" => 404,
                ], 404);
            }
        }

        return response()->json([
            "error" => "Item not found",
            "status_code" => 404,
        ], 404);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////
    private function validateCartItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|integer|min:1',
            'type' => 'required|string|in:food,drink,accessory',
            'quantity' => 'sometimes|required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        return null;
    }
    //////////////////////////////////////////////////
    private function getItem($type, $itemId)
    {
        switch ($type) {
            case 'food':
                return Food::find($itemId);
            case 'drink':
                return Drink::find($itemId);
            case 'accessory':
                return Accessory::find($itemId);
            default:
                return null;
        }
    }
    //////////////////////////////////////////////////
    private function getCart()
    {
        $user = Auth::user();
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    //////////////////////////////////////////////////
    public function getCartItemSorted(Request $request): JsonResponse
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

        $cart = Cart::query()->where('user_id' , $user->id)->pluck('id');

        if(! $cart->count() > 0)
        {
            return response()->json([
                "error" => TranslateTextHelper::translate("You don't have a cart yet"),
                "status_code" => 404,
            ], 404);
        }

        $validator = Validator::make($request->all() , [
            'type' => 'required|string|in:all,food,drink,accessory'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'error' => $validator->errors()->first(),
                'status_code' => 422
            ] , 422);
        }

        $isTypeAll = strtolower($request->type) === 'all' ;

        $results = CartItem::query()->where('cart_id' , $cart);

        if($request->type && !$isTypeAll)
        {
            if(strtolower($request->type) == 'food') {
                $results->where('itemable_type', 'App\Models\Food');
            }
            elseif(strtolower($request->type) == 'drink') {
                $results->where('itemable_type' , 'App\Models\Drink');
            }
            elseif(strtolower($request->type) == 'accessory'){
                $results->where('itemable_type' , 'App\Models\Accessory');
            }
        }

        $items = $results->orderBy('quantity')->with('itemable')->get();

        if ($items->isEmpty() && $request->type && $isTypeAll) {
            return response()->json([
                'error' => TranslateTextHelper::translate("No Items found , Add some"),
                'status_code' => 404,
            ], 404);
        }

        if ($items->isEmpty() && $request->type && !$isTypeAll) {
            return response()->json([
                'error' => TranslateTextHelper::translate("No Items found for specific category , Add some"),
                'status_code' => 404,
            ], 404);
        }

        $response = [];
        $totalPrice = 0;
        $totalItems = 0;

        $names = [];
        foreach ($items as $item)
        {
            $names [] = $item->itemable->name ;
        }

        $translate = TranslateTextHelper::batchTranslate($names);

        foreach ($items as $item)
        {
            $itemTotalPrice = $item->itemable->raw_price * $item->quantity;
            $totalPrice += $itemTotalPrice;
            $totalItems ++;

            $response [] = [
                'id' => $item->itemable->id ,
                'name' => $translate[$item->itemable->name] ,
                'quantity' => $item->quantity ,
                'price' => number_format($itemTotalPrice , 2 , '.' , ',') . ' S.P',
                'picture' => $item->itemable->picture ,
            ];
        }

        return response()->json([
            'data' => $response ,
            'total_price' => number_format($totalPrice , 2 , '.' , ',') . ' S.P' ,
            'total_Items' => $totalItems ,
        ] , 200);
    }
    //////////////////////////////////////////////////
    public function DeleteAllItemsCart (): JsonResponse
    {
        $user = Auth::user();

        if(!$user)
        {
            return response()->json([
                "error" => "Something went wrong , try again later",
                "status_code" => 422,
            ], 422);
        }

        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $Cart_id = Cart::query()->where('user_id' , $user->id)->pluck('id');

        if(! $Cart_id->count() > 0)
        {
            return response()->json([
                "error" => TranslateTextHelper::translate("You don't have a cart yet"),
                "status_code" => 404,
            ], 404);
        }

        $cart_item = CartItem::query()->where('cart_id' , $Cart_id)->get();
        if(! $cart_item->count() > 0)
        {
            return response()->json([
                "error" => TranslateTextHelper::translate("No item was found to be deleted"),
                "status_code" => 404,
            ], 404);
        }

        CartItem::query()->where('cart_id', $Cart_id)->delete();

        return response()->json([
            "message" => TranslateTextHelper::translate("All items have been successfully deleted from your cart"),
            "status_code" => 200,
        ], 200);
    }
}
