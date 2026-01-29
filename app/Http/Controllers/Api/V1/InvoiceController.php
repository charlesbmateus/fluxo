<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\NotificationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected InvoiceService $invoiceService,
        protected NotificationService $notificationService
    ) {}

    /**
     * List invoices for authenticated user
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $invoices = $user->isProvider()
            ? Invoice::forProvider($user->id)->latest()->get()
            : Invoice::forClient($user->id)->latest()->get();

        return ApiResponse::success(
            $invoices,
            'Invoices retrieved successfully'
        );
    }

    /**
     * Show single invoice
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        return ApiResponse::success(
            $invoice->load(['booking', 'client', 'provider']),
            'Invoice retrieved successfully'
        );
    }

    /**
     * Pay invoice
     */
    public function pay(Invoice $invoice): JsonResponse
    {
        $this->authorize('pay', $invoice);

        $invoice->markAsPaid();

        // Notify provider
        $this->notificationService->notify(
            $invoice->provider,
            'invoice_paid',
            'An invoice has been paid'
        );

        return ApiResponse::success(
            $invoice->refresh(),
            'Invoice paid successfully'
        );
    }

    /**
     * Cancel invoice
     */
    public function cancel(Invoice $invoice): JsonResponse
    {
        $this->authorize('cancel', $invoice);

        $invoice->cancel();

        return ApiResponse::success(
            $invoice->refresh(),
            'Invoice cancelled successfully'
        );
    }

    /**
     * Issue invoice
     */
    public function issue(Invoice $invoice): JsonResponse
    {
        $this->authorize('issue', $invoice);

        $invoice->markAsIssued();

        return ApiResponse::success(
            $invoice->refresh(),
            'Invoice issued successfully'
        );
    }
}
