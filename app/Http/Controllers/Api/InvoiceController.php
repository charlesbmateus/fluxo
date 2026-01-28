<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected InvoiceService $invoiceService
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

        return response()->json($invoices);
    }

    /**
     * Show single invoice
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        return response()->json($invoice->load(['booking', 'client', 'provider']));
    }

    /**
     * Pay invoice
     */
    public function pay(Invoice $invoice): JsonResponse
    {
        $this->authorize('pay', $invoice);

        $invoice->markAsPaid();

        return response()->json([
            'message' => 'Invoice paid successfully',
            'data'    => $invoice->refresh(),
        ]);
    }

    /**
     * Cancel invoice
     */
    public function cancel(Invoice $invoice): JsonResponse
    {
        $this->authorize('cancel', $invoice);

        $invoice->cancel();

        return response()->json([
            'message' => 'Invoice cancelled',
            'data'    => $invoice->refresh(),
        ]);
    }

    public function issue(Invoice $invoice): JsonResponse
    {
        $this->authorize('issue', $invoice);

        $invoice->markAsIssued();

        return response()->json([
            'message' => 'Invoice issued',
            'data' => $invoice,
        ]);
    }
}
