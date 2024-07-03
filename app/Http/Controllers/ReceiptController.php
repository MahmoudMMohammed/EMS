<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\EventSupplement;
use App\Models\Receipt;
use App\Models\UserEvent;
use Barryvdh\DomPDF\Facade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;


class ReceiptController extends Controller
{
    public function generateQRForReceipt($userEventId): JsonResponse
    {
        $user = Auth::user();
        $event = UserEvent::find($userEventId);
        $supplements = $event->supplements;


        $foodDetails = $supplements->food_details;
        $drinksDetails = $supplements->drinks_details;
        $accessoriesDetails = $supplements->accessories_details;

        // Calculate totals
        $totalFood = collect($foodDetails)->sum(function($item) {
            return $this->parsePrice($item['price']) * $item['quantity'];
        });

        $totalDrinks = collect($drinksDetails)->sum(function($item) {
            return $this->parsePrice($item['price']) * $item['quantity'];
        });

        $totalAccessories = collect($accessoriesDetails)->sum(function($item) {
            return $this->parsePrice($item['price']) * $item['quantity'];
        });

        // Add parsed price to each item
        $foodDetails = collect($foodDetails)->map(function($item) {
            $item['parsed_price'] = $this->parsePrice($item['price']);
            return $item;
        })->toArray();

        $drinksDetails = collect($drinksDetails)->map(function($item) {
            $item['parsed_price'] = $this->parsePrice($item['price']);
            return $item;
        })->toArray();

        $accessoriesDetails = collect($accessoriesDetails)->map(function($item) {
            $item['parsed_price'] = $this->parsePrice($item['price']);
            return $item;
        })->toArray();


        $pdfPath = 'public/receipts/' . uniqid() . '.pdf';


        // Generate the QR code with the download link using endroid/qr-code
        $qrCode = new QrCode(url("/api/download-receipt?path={$pdfPath}"));
        $writer = new PngWriter();

        // Generate a unique filename for the QR code image
        $qrCodeFilename = 'qr_codes/' . uniqid() . '.png';
        $qrCodePath = storage_path('app/public/' . $qrCodeFilename);

        // Write the QR code to a file
        $result = $writer->write($qrCode);
        Storage::put('public/' . $qrCodeFilename, $result->getString());

        // Encode the QR code image as base64
        $qrCodeBase64 = base64_encode(file_get_contents($qrCodePath));

        $data = [
            'user' => $user,
            'event' => $event,
            'foodItems' => $foodDetails,
            'drinkItems' => $drinksDetails,
            'accessoryItems' => $accessoriesDetails,
            'totalFood' => $totalFood,
            'totalDrinks' => $totalDrinks,
            'totalAccessories' => $totalAccessories,
            'grandTotal' => $supplements->total_price,
            'qrCodeBase64' => $qrCodeBase64,
        ];

        // Generate the PDF using the instance method
        $pdf = app('dompdf.wrapper')->loadView('receipt_template', $data);

        // Save the PDF to storage
        Storage::put($pdfPath, $pdf->output());

        // Create or update receipt record
        $receipt = Receipt::updateOrCreate(
            ['user_event_id' => $event->id],
            [
                'user_id' => $event->user_id,
                'event_supplement_id' => $event->supplements->id,
                'qr_code' => $qrCodeFilename
            ]
        );

        TranslateTextHelper::setTarget($user->profile->preferred_language);
        if (!$receipt) {
            return response()->json([
                "error" => TranslateTextHelper::translate("Failed to generate the receipt, please try again!"),
                "status_code" => 400,
            ], 400);
        }

        return response()->json([
            "message" => TranslateTextHelper::translate("Receipt generated successfully. Scan the QR to download it or click the button below."),
            "receipt" => $receipt,
            "status_code" => 200
        ], 200);
    }

    ////////////////////////////////////////////////////////////////////////////////////

    public function downloadReceipt(Request $request): StreamedResponse|JsonResponse
    {
        $path = $request->query('path');

        if (Storage::exists($path)) {
            return Storage::download($path);
        }

        return response()->json([
            'message' => 'Receipt not found',
            'status_code' => 404,
        ], 404);
    }
    ////////////////////////////////////////////////////////////////////////////////////

    private function parsePrice($priceString): float
    {
        $cleanedPrice = preg_replace('/[^0-9.,]/', '', $priceString);
        $cleanedPrice = str_replace(',', '', $cleanedPrice);
        return floatval($cleanedPrice);
    }
    ////////////////////////////////////////////////
}
