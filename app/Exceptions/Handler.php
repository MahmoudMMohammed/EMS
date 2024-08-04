<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Exception|Throwable $e): \Illuminate\Http\Response|JsonResponse|RedirectResponse|Response
    {
        if(get_class($e) == "Illuminate\Database\Eloquent\ModelNotFoundException") {
            return \response()->json([
                "error" => "Model not Found!",
                "status_code" => 404
            ],404);
        }
        return parent::render($request, $e);
    }
}
