<?php

namespace App\Http\Controllers;

use App\Helpers\TranslateTextHelper;
use App\Models\EventSupplement;
use App\Models\Receipt;
use App\Models\UserEvent;
use Barryvdh\DomPDF\Facade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;




class ReceiptController extends Controller
{
    public function generateQRForReceipt($userEventId)
    {
        $user = Auth::user();
        $event = UserEvent::find($userEventId);

        $data = [
            'user' => $user,
            'event' => $event,
            'supplement' => $event->supplements,
        ];

        // Generate the PDF using the instance method
        $pdf = app('dompdf.wrapper')->loadView('receipt_template', $data);

        // Save the PDF to storage
        $pdfPath = 'public/receipts/' . uniqid() . '.pdf';
        Storage::put($pdfPath, $pdf->output());

        // Generate the QR code with the download link using endroid/qr-code
        $qrCode = new QrCode(url("/api/download-receipt?path={$pdfPath}"));
        $writer = new PngWriter();
        $qrCodePath = 'qr_codes/' . uniqid() . '.png';

        // Write the QR code to a file
        $result = $writer->write($qrCode);
        Storage::put('public/' . $qrCodePath, $result->getString());

        // Create or update receipt record
        $receipt = Receipt::updateOrCreate(
            ['user_event_id' => $event->id],
            [
                'user_id' => $event->user_id,
                'event_supplement_id' => $event->supplements->id,
                'qr_code' => $qrCodePath
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

    public function downloadReceipt(Request $request)
    {
        $user = Auth::user();
        TranslateTextHelper::setTarget($user->profile->preferred_language);

        $path = $request->query('path');

        if (Storage::exists($path)) {
            return Storage::download($path);
        }

        return response()->json([
            'message' => TranslateTextHelper::translate('Receipt not found')
        ], 404);
    }
    ////////////////////////////////////////////////////////////////////////////////////
}
