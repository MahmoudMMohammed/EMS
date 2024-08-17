<?php

namespace App\Console;

use App\Models\EventSupplement;
use App\Models\User;
use App\Models\UserEvent;
use App\Traits\PriceParsing;
use App\Traits\SalesData;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    use SalesData,PriceParsing;
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(callback: function () {

            $reservations = $this->getFinishedEvents();

            foreach ($reservations as $reservation) {
                try {
                    // Update the verified field to 'Finished'
                    $reservation->verified = "Finished";
                    $reservation->save();

                    // Deduct balance from the user
                    $user = User::find($reservation->user_id);
                    if ($user && $user->profile) {
                        $supplements = EventSupplement::whereUserEventId($reservation->id)->first();
                        $user->profile->balance -= $this->parsePrice($supplements->total_price);
                        $user->save();
                    } else {
                        Log::warning("User or profile not found for reservation ID: " . $reservation->id);
                    }
                } catch (\Exception $e) {
                    Log::error("Error processing reservation ID: " . $reservation->id . ". Error: " . $e->getMessage());
                }
            }
        })->everyTwoMinutes();

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
