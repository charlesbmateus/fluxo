<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Responses\ApiResponse;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Create a payment for a confirmed booking
     *
     * POST /api/v1/payments
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $payment = $this->paymentService->create(
            auth()->user(),
            $request->validated()
        );

        return ApiResponse::success(
            $payment,
            'Payment created successfully',
            201
        );
    }
}
