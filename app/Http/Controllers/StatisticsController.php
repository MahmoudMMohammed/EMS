<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\Accessory;
use App\Models\Drink;
use App\Models\Favorite;
use App\Models\Feedback;
use App\Models\Food;
use App\Models\Host;
use App\Models\Location;
use App\Models\User;
use App\Models\UserEvent;
use App\Traits\PriceParsing;
use App\Traits\RegistrationData;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;



class StatisticsController extends Controller
{
    use RegistrationData,PriceParsing;

    public function getWeeklyStatistics(): JsonResponse
    {
        $events = $this->getFinishedAndConfirmedEvents();

        // Initialize an array to store results for each day of the week
        $weeklyStatistics = [
            'Sunday' => ['food' => 0, 'drinks' => 0, 'accessories' => 0],
            'Monday' => ['food' => 0, 'drinks' => 0, 'accessories' => 0],
            'Tuesday' => ['food' => 0, 'drinks' => 0, 'accessories' => 0],
            'Wednesday' => ['food' => 0, 'drinks' => 0, 'accessories' => 0],
            'Thursday' => ['food' => 0, 'drinks' => 0, 'accessories' => 0],
            'Friday' => ['food' => 0, 'drinks' => 0, 'accessories' => 0],
            'Saturday' => ['food' => 0, 'drinks' => 0, 'accessories' => 0],
        ];

        // Loop through each event and their supplements
        foreach ($events as $event) {
            $dayOfWeek = Carbon::parse($event->date)->format('l'); // Get the day of the week

            $food = $event->supplements->food_details;
            $drinks = $event->supplements->drinks_details;
            $accessories = $event->supplements->accessories_details;

            foreach ($food as $item){
                $weeklyStatistics[$dayOfWeek]['food'] += $item['quantity'];
            }

            foreach ($drinks as $item){
                $weeklyStatistics[$dayOfWeek]['drinks'] += $item['quantity'];
            }

            foreach ($accessories as $item){
                $weeklyStatistics[$dayOfWeek]['accessories'] += $item['quantity'];
            }
        }

        return response()->json($weeklyStatistics);
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function getWeeklyReport(): StreamedResponse|JsonResponse
    {
        $statistics = $this->getWeeklyReportData();
        $today = Carbon::parse(now())->toDateString();


        try {
            $pdf = app('dompdf.wrapper')->loadView('weekly_report_template', $statistics);
            $filePath = 'reports/weekly/' . $today . '_weekly_report.pdf';

            // Generate PDF output
            $pdfOutput = $pdf->output();
            if (empty($pdfOutput)) {
                Log::error('PDF output is empty.');
                throw new Exception('PDF output is empty.');
            }

            // Save PDF to storage
            if (!Storage::put($filePath, $pdfOutput)) {
                Log::error("Failed to save PDF file at path: $filePath");
                throw new Exception('Failed to save PDF file.');
            }

            return Storage::download($filePath);

        } catch (Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'An error occurred while generating the PDF',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function getDigitalStatisticsForUser(): JsonResponse
    {
        $user = Auth::user();
        $userRegistrationData = $this->getRegistrationInfo($user);
        $userFeedbacksCounts = $this->getUserFeedbacksCount();
        $userFavoritesCounts = $this->getUserFavoritesCount();

        return response()->json([
            "registration_data" => $userRegistrationData,
            "feedbacks_data" => $userFeedbacksCounts,
            "favorites_data" => $userFavoritesCounts,
        ]);

    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////
    public function getPurchasesDistributions(): JsonResponse
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);
        $events = $user->events;

        $items = [
            'food' => 0,
            'drinks' => 0,
            'accessories' => 0,
            'total' => 0
        ];

        // Loop through each event and their supplements
        foreach ($events as $event) {
            $food = $event->supplements->food_details ?? [];
            $drinks = $event->supplements->drinks_details ?? [];
            $accessories = $event->supplements->accessories_details ?? [];

            if (!empty($food)) {
                foreach ($food as $item) {
                    $items['food'] += $item['quantity'];
                }
            }

            if (!empty($drinks)) {
                foreach ($drinks as $item) {
                    $items['drinks'] += $item['quantity'];
                }
            }

            if (!empty($accessories)) {
                foreach ($accessories as $item) {
                    $items['accessories'] += $item['quantity'];
                }
            }

            $items['total'] = $items['accessories'] + $items['drinks'] + $items['food'];
        }


        return response()->json($items);
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    public function getUserStatisticsCount()
    {
        $user = Auth::user();

        $userReservations = $this->getUserFinishedAndConfirmedReservations();
        $userBlockedReservations = $this->getUserBlockedReservations();
        $blockedComments = $this->getUserBlockedComments();

        $lastLogin = "";
        if ($user->last_login){
            $date = Carbon::parse($user->last_login);
            $lastLogin = $date->diffForHumans();
        }

        $userInfo = [
            "message" => 'You have logged '. $user->number_of_logins . ' to the app',
            'date' => $lastLogin,
        ];

        return response()->json([
            "confirmed_and_finished_reservations" => $userReservations,
            "blocked_reservations" => $userBlockedReservations,
            "user_logins" => $userInfo,
            "blocked_comments" => $blockedComments,
            ]);

    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////

    private function getWeeklyReportData(): array
    {
        $events = $this->getFinishedAndConfirmedEvents();
        $aggregatedData = $this->aggregateSupplements($events);

        $itemStatistics = $aggregatedData['itemStatistics'];
        $locationProfits = $aggregatedData['locationProfits'];
        $hostProfits = $aggregatedData['hostProfits'];

        return [
            'top_profitable_hosts' => $this->getTopHosts($hostProfits),
            'top_profitable_locations' => $this->getTopLocations($locationProfits),
            'top_ordered_food' => $this->getTopItems($itemStatistics, 'food'),
            'top_ordered_drinks' => $this->getTopItems($itemStatistics, 'drinks'),
            'top_ordered_accessories' => $this->getTopItems($itemStatistics, 'accessories'),
            'monthly_user_growth' => $this->getMonthlyUserGrowth(),

        ];
    }

    //////////////////////////////////////////////////////////

    private function getFinishedAndConfirmedEvents()
    {
        // Calculate the start and end dates for the previous week
        $startOfWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfWeek = Carbon::now()->subWeek()->endOfWeek();

        // Fetch all finished events for the past week
        $finishedEvents = UserEvent::whereVerified("Finished")
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->get();

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
    //////////////////////////////////////////////////////////

    private function aggregateSupplements($events): array
    {
        $itemStatistics = [
            'food' => [],
            'drinks' => [],
            'accessories' => [],
        ];

        $locationProfits = [];
        $hostProfits = [];

        foreach ($events as $event) {
            $locationId = $event->location_id;
            $hostId = $event->location->host_id; // Assuming Location model has a host relationship

            $foodDetails = $event->supplements->food_details;
            $drinksDetails = $event->supplements->drinks_details;
            $accessoriesDetails = $event->supplements->accessories_details;

            foreach ($foodDetails as $food) {
                $itemStatistics['food'][$food['id']] = ($itemStatistics['food'][$food['id']] ?? 0) + $food['quantity'];
            }
            foreach ($drinksDetails as $drink) {
                $itemStatistics['drinks'][$drink['id']] = ($itemStatistics['drinks'][$drink['id']] ?? 0) + $drink['quantity'];
            }
            foreach ($accessoriesDetails as $accessory) {
                $itemStatistics['accessories'][$accessory['id']] = ($itemStatistics['accessories'][$accessory['id']] ?? 0) + $accessory['quantity'];
            }

            $locationProfits[$locationId] = ($locationProfits[$locationId] ?? 0) + $this->parsePrice($event->supplements->total_price);

            $hostProfits[$hostId] = ($hostProfits[$hostId] ?? 0) + ($locationProfits[$locationId] ?? 0);
        }

        return [
            'itemStatistics' => $itemStatistics,
            'locationProfits' => $locationProfits,
            'hostProfits' => $hostProfits,
        ];
    }

    //////////////////////////////////////////////////////////

    private function getTopItems($itemStatistics, $type, $limit = 3): array
    {
        arsort($itemStatistics[$type]);
        $topItemIds = array_slice($itemStatistics[$type], 0, $limit, true);

        $itemModel = null;
        switch ($type) {
            case 'food':
                $itemModel = Food::class;
                break;
            case 'drinks':
                $itemModel = Drink::class;
                break;
            case 'accessories':
                $itemModel = Accessory::class;
                break;
        }

        $topItems = $itemModel::whereIn('id', array_keys($topItemIds))->get(['id', 'name']);

        $result = [];
        foreach ($topItems as $item) {
            $result[] = [
                'id' => $item->id,
                'name' => $item->name,
                'quantity' => $topItemIds[$item->id],
            ];
        }

        return $result;
    }

    //////////////////////////////////////////////////////////

    private function getTopLocations($locationProfits, $limit = 3): array
    {
        arsort($locationProfits);

        $top3 = array_slice($locationProfits, 0, $limit, true);
        $locations = [];

        foreach ($top3 as $key => $value) {
            $location = Location::find($key);
            $locations [] = [
                'id' => $location->id,
                'name' => $location->name,
                'profit' => $value
            ];
        }

        return $locations;

    }
    //////////////////////////////////////////////////////////

    private function getTopHosts($hostProfits, $limit = 3): array
    {
        arsort($hostProfits);
        $top3 = array_slice($hostProfits, 0, $limit, true);

        $hosts = [];

        foreach ($top3 as $key => $value) {
            $host = Host::find($key);
            $hosts [] = [
                'id' => $host->id,
                'name' => $host->name,
                'profit' => $value
            ];
        }

        return $hosts;
    }
    //////////////////////////////////////////////////////////


    private function getUserRegistrationsByMonth($startDate, $endDate)
    {
        return User::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }
    //////////////////////////////////////////////////////////

    private function calculateMonthlyGrowth($registrations): array
    {
        $growthData = [];
        $previousMonthCount = null;

        foreach ($registrations as $registration) {
            $currentMonthCount = $registration->count;
            $growthRate = null;

            if ($previousMonthCount !== null) {
                $growthRate = (($currentMonthCount - $previousMonthCount) / $previousMonthCount) * 100;
            }

            $growthData[] = [
                'year' => $registration->year,
                'month' => $registration->month,
                'count' => $currentMonthCount,
                'growth_rate' => $growthRate,
            ];

            $previousMonthCount = $currentMonthCount;
        }

        return $growthData;
    }
    //////////////////////////////////////////////////////////

    private function getMonthlyUserGrowth(): array
    {
        $startDate = Carbon::now()->subYear();
        $endDate = Carbon::now();

        $registrations = $this->getUserRegistrationsByMonth($startDate, $endDate);

        return $this->calculateMonthlyGrowth($registrations);
    }
    //////////////////////////////////////////////////////////
    public function WebGetLocationCount(): JsonResponse
    {
        $count = Location::query()->count();
        $response = ['count' => $count.' Place'];
        return response()->json($response);
    }
    //////////////////////////////////////////////////////////

    private function getUserFeedbacksCount(): array
    {
        $user = Auth::user();
        $feedbacks = Feedback::whereUserId($user->id)->pluck('rate');

        return [
            "number_of_ratings" => $feedbacks->count(),
            "average_ratings" => 'average ratings: ' . number_format($feedbacks->avg(),2),
        ];
    }
    //////////////////////////////////////////////////////////

    private function getUserFavoritesCount(): array
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $favorites = Favorite::whereUserId($user->id)->get();

        $items = [
            'locations' => [],
            'foods' => [],
            'drinks' => [],
            'accessories' => []
        ];
        static $counter = 0;

        foreach ($favorites as $favorite) {
            $item = $favorite->favoritable;
            if ($item) {
                switch (class_basename($item)) {
                    case 'Location':
                        $items['locations'][] = $item;
                        break;
                    case 'Food':
                        $items['foods'][] = $item;
                        break;
                    case 'Drink':
                        $items['drinks'][] = $item;
                        break;
                    case 'Accessory':
                        $items['accessories'][] = $item;
                        break;
                }
            }
            $counter++;
        }

        $foodCount = count($items['foods']);
        $drinksCount = count($items['drinks']);
        $accessoriesCount = count($items['accessories']);
        $locationsCount = count($items['locations']);
        return [
            "data" => "food:$foodCount, drinks:$drinksCount, accessories:$accessoriesCount, locations:$locationsCount",
            "total_count" => count($items['foods']) + count($items['drinks']) + count($items['accessories']) + count($items['locations']),
        ];
    }
    //////////////////////////////////////////////////////////
    private function getUserFinishedAndConfirmedReservations(): array
    {
        $user = Auth::user();
        // Fetch all finished events
        $finishedEvents = UserEvent::whereUserId($user->id)
            ->whereVerified("Finished")
            ->get();

        $yesterday = Carbon::parse(now())->subDay();

        // Fetch all confirmed events where the parsed start_date is <= yesterday
        $confirmedEvents = UserEvent::whereUserId($user->id)
            ->whereVerified("Confirmed")
            ->get()
            ->filter(function ($event) use ($yesterday) {
                $startTime = Carbon::parse($event->date . ' ' . $event->start_time);
                return $startTime->greaterThanOrEqualTo($yesterday);
            });
        $combined = $finishedEvents->merge($confirmedEvents)->sortByDesc('created_at');
        $latest = $combined->first();

        $latestDate = "";
        if ($latest){
            $date = Carbon::parse($latest->created_at);
            $latestDate = $date->diffForHumans();
        }

        return [
            'message' => 'You have '. count($combined) . ' reservations inside the app.',
            'date' => $latestDate,
        ];
    }
    //////////////////////////////////////////////////////////
    private function getUserBlockedReservations(): array
    {
        $user = Auth::user();

        // Fetch all rejected events
        $rejectedEvents = UserEvent::whereUserId($user->id)
            ->whereVerified("Rejected")
            ->get();

        $latestEvent = $rejectedEvents->sortByDesc('created_at')->first();
        $eventCreationDate = "";
        if ($latestEvent){
            $date = Carbon::parse($latestEvent->created_at);
            $eventCreationDate = $date->diffForHumans();
        }



        return [
            "message" => 'You have '. count($rejectedEvents) . ' blocked events.',
            "date" => $eventCreationDate,
        ];
    }
    //////////////////////////////////////////////////////////
    private function getUserBlockedComments(): array
    {
        $user = Auth::user();

        $blockedComments = Feedback::withTrashed() // Include soft deleted models
            ->whereUserId($user->id)
            ->whereNotNull('deleted_at') // Check that 'deleted_at' is not null
            ->get();

        $latestComment = "";
        if (!$blockedComments){
            $comment = $blockedComments->sortByDesc('created_at')->first();
            $latestComment = $comment->date;
        }


        return [
            "message" => 'You have '. count($blockedComments) . ' blocked comments.',
            "date" => $latestComment,
        ];
    }
    //////////////////////////////////////////////////////////

}
