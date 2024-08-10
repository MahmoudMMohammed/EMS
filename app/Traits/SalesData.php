<?php

namespace App\Traits;

use App\Models\Accessory;
use App\Models\Drink;
use App\Models\EventSupplement;
use App\Models\Food;
use App\Models\UserEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

trait SalesData
{
    use PriceParsing;
    public function getModelSales($model): JsonResponse|array
    {
        // Determine the model class and corresponding details field
        $modelClass = get_class($model);
        $detailsField = $this->getDetailsField($modelClass);

        // If the model type is not supported, return an error
        if (!$detailsField) {
            return response()->json([
                "error" => "Unsupported model type!",
                "status_code" => 400
            ], 400);
        }
        $events = $this->getFinishedAndConfirmedReservations();


        // Initialize counters
        $totalOrders = 0;
        $totalQuantity = 0;

        // Loop through each supplement to calculate sales
        foreach ($events as $event) {
            $supplements = $event->supplements->$detailsField;
            if ($supplements){
                foreach ($supplements as $item) {
                    if ($item['id'] == $model->id) {
                        $totalOrders++;
                        $totalQuantity += $item['quantity'];
                    }
                }
            }
        }
        // Calculate total sales
        $totalSales = $totalQuantity * $this->parsePrice($model->price);

        return [
            'total_orders' => $totalOrders,
            'total_quantity' => $totalQuantity,
            'total_sales' => $totalSales,
        ];
    }
    ///////////////////////////////////////////////////////////
    private function getDetailsField($modelClass): ?string
    {
        return match ($modelClass) {
            Food::class => 'food_details',
            Drink::class => 'drinks_details',
            Accessory::class => 'accessories_details',
            default => null,
        };
    }
    ///////////////////////////////////////////////////////////
    private function getFinishedAndConfirmedReservations()
    {

        // Fetch all finished events
        $finishedEvents = UserEvent::whereVerified("Finished")->get();

        $yesterday = Carbon::parse(now())->subDay();

        // Fetch all confirmed events where the parsed start_date is <= yesterday
        $confirmedEvents = UserEvent::whereVerified("Confirmed")
            ->get()
            ->filter(function ($event) use ($yesterday) {
                $startTime = Carbon::parse($event->date . ' ' . $event->start_time);
                return $startTime->greaterThanOrEqualTo($yesterday);
            });
        return $finishedEvents->merge($confirmedEvents);
    }
    ///////////////////////////////////////////////////////////

}
