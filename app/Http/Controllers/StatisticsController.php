<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\Drink;
use App\Models\Food;
use App\Models\Host;
use App\Models\Location;
use App\Models\User;
use App\Models\UserEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;


class StatisticsController extends Controller
{
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
                throw new \Exception('PDF output is empty.');
            }

            // Save PDF to storage
            if (!Storage::put($filePath, $pdfOutput)) {
                Log::error("Failed to save PDF file at path: $filePath");
                throw new \Exception('Failed to save PDF file.');
            }

            return Storage::download($filePath);

        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'An error occurred while generating the PDF',
                'error' => $e->getMessage(),
            ], 500);
        }

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

        $confirmedEvents = UserEvent::whereVerified("Confirmed")
            ->where("date", '<=' , $yesterday)
            ->get();

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

    private function parsePrice($priceString): float
    {
        $cleanedPrice = preg_replace('/[^0-9.,]/', '', $priceString);
        $cleanedPrice = str_replace(',', '', $cleanedPrice);
        return floatval($cleanedPrice);
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
        $startDate = Carbon::now()->subYear(); // Adjust the start date as needed
        $endDate = Carbon::now();

        $registrations = $this->getUserRegistrationsByMonth($startDate, $endDate);

        return $this->calculateMonthlyGrowth($registrations);
    }
    //////////////////////////////////////////////////////////

}
