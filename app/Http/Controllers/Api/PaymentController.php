<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function store(StorePaymentRequest $request): JsonResponse
    {
        $payment = $this->paymentService->create(
            auth()->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Payment created successfully',
            'data'    => $payment,
        ], 201);
    }
}
