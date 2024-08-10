<?php

namespace App\Traits;

use App\Models\Accessory;
use App\Models\Drink;
use App\Models\Food;
use App\Models\UserEvent;

trait ModelUsageCheck
{
    use PriceParsing; // Assuming you might need this if the traits are combined

    public function checkModelUsage($model): bool
    {
        // Determine the model class and corresponding details field
        $modelClass = get_class($model);
        $detailsField = $this->resolveDetailsField($modelClass);

        // If the model type is not supported, return false
        if (!$detailsField) {
            return false;
        }

        // Fetch all pending or confirmed events
        $events = UserEvent::whereIn('verified', ['Pending', 'Confirmed'])->get();

        // Check if the model exists in any of these events' supplements
        foreach ($events as $event) {
            $supplements = $event->supplements->$detailsField;
            if ($supplements) {
                foreach ($supplements as $item) {
                    if ($item['id'] == $model->id) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
    /////////////////////////////////////////////////////////////////////////
    private function resolveDetailsField($modelClass): ?string
    {
        return match ($modelClass) {
            Food::class => 'food_details',
            Drink::class => 'drinks_details',
            Accessory::class => 'accessories_details',
            default => null,
        };
    }
    /////////////////////////////////////////////////////////////////////////

}

