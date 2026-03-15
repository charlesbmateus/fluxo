<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Stripe\Stripe;
use Stripe\Account;
use Stripe\AccountLink;

class StripeController extends Controller
{
    /**
     * Create a Stripe Connect account for the provider
     */
    public function createAccount(): JsonResponse
    {
        $user = auth()->user();

        if (! $user->isProvider()) {
            return response()->json([
                'message' => 'Only providers can create Stripe accounts.'
            ], 403);
        }

        // If already created
        if ($user->stripe_account_id) {
            return response()->json([
                'message' => 'Stripe account already exists.'
            ]);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        // Create a Stripe Connect account
        $account = Account::create([
            'type' => 'standard',
            'email' => $user->email,
        ]);

        // Save Stripe account ID
        $user->update([
            'stripe_account_id' => $account->id,
        ]);

        // Create onboarding link
        $accountLink = AccountLink::create([
            'account' => $account->id,
            'refresh_url' => config('app.frontend_url') . '/stripe/refresh',
            'return_url'  => config('app.frontend_url') . '/stripe/return',
            'type' => 'account_onboarding',
        ]);

        return response()->json([
            'onboarding_url' => $accountLink->url,
        ]);
    }
}
