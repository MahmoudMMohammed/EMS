<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Drink;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $validationError = $this->validateCartItem($request);
        if ($validationError) {
            return response()->json($validationError, 422);
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
                    "message" => "Item quantity updated successfully",
                    "status_code" => 200,
                ],200);
            } else {
                // Add new item to cart
                $cartItem = new CartItem(['quantity' => $data['quantity']]);
                $cartItem->itemable()->associate($item);
                $cart->items()->save($cartItem);
                return response()->json([
                    "message" => "Item added to cart successfully",
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
    public function getCartItems()
    {
        $user = Auth::user();
        $cart = Cart::whereUserId($user->id)->first();

        if(!$cart || $cart->items()->count() == 0){
            return response()->json([
                "error" => "You have not added anything to your cart yet!",
                "status_code" => 404,
            ],404);
        }
        return response()->json($cart->load('items.itemable'));
    }

    //////////////////////////////////////////////////////////////////////////////////////////////

    public function removeFromCart(Request $request)
    {
        $validationError = $this->validateCartItem($request);
        if ($validationError) {
            return response()->json($validationError, 422);
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
                    "message" => "Item removed from cart",
                    "status_code" => 200,
                ], 200);
            } else {
                return response()->json([
                    "error" => "Item not found in cart",
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
        $validationError = $this->validateCartItem($request);
        if ($validationError) {
            return response()->json($validationError, 422);
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
                    "message" => "Item quantity updated",
                    "status_code" => 200,
                ], 200);
            } else {
                return response()->json([
                    "error" => "Item not found in cart",
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
            return [
                "error" => $validator->errors()->first(),
                "status_code" => 422,
            ];
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
}
