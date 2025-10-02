<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCheckoutSession(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|in:pro,enterprise',
        ]);

        $organization = Organization::find($request->user()->current_organization_id);

        $prices = [
            'pro' => [
                'amount' => 2900,
                'name' => 'Pro Plan',
            ],
            'enterprise' => [
                'amount' => 9900,
                'name' => 'Enterprise Plan',
            ],
        ];

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $prices[$validated['plan']]['name'],
                            'description' => 'CloudTask Pro ' . ucfirst($validated['plan']) . ' subscription',
                        ],
                        'unit_amount' => $prices[$validated['plan']]['amount'],
                        'recurring' => [
                            'interval' => 'month',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => env('FRONTEND_URL') . '/settings?success=true&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => env('FRONTEND_URL') . '/settings?canceled=true',
                'client_reference_id' => $organization->id,
                'metadata' => [
                    'organization_id' => $organization->id,
                    'plan' => $validated['plan'],
                ],
            ]);

            return response()->json([
                'sessionId' => $session->id,
                'url' => $session->url,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $this->handleCheckoutCompleted($session);
                break;

            case 'customer.subscription.updated':
                $subscription = $event->data->object;
                $this->handleSubscriptionUpdated($subscription);
                break;

            case 'customer.subscription.deleted':
                $subscription = $event->data->object;
                $this->handleSubscriptionDeleted($subscription);
                break;

            case 'invoice.payment_succeeded':
                $invoice = $event->data->object;
                $this->handlePaymentSucceeded($invoice);
                break;

            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                $this->handlePaymentFailed($invoice);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    private function handleCheckoutCompleted($session)
    {
        $organizationId = $session->metadata->organization_id;
        $plan = $session->metadata->plan;
        $organization = Organization::find($organizationId);

        $limits = [
            'pro' => ['projects' => 50, 'users' => 20],
            'enterprise' => ['projects' => 999999, 'users' => 999999],
        ];

        $amounts = [
            'pro' => 29.00,
            'enterprise' => 99.00,
        ];

        $organization->update([
            'plan' => $plan,
            'project_limit' => $limits[$plan]['projects'],
            'user_limit' => $limits[$plan]['users'],
        ]);

        Subscription::create([
            'organization_id' => $organizationId,
            'plan' => $plan,
            'status' => 'active',
            'amount' => $amounts[$plan],
            'billing_cycle' => 'monthly',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
            'stripe_subscription_id' => $session->subscription,
        ]);
    }

    private function handleSubscriptionUpdated($subscription)
    {
        $sub = Subscription::where('stripe_subscription_id', $subscription->id)->first();
        if ($sub) {
            $sub->update([
                'status' => $subscription->status,
                'current_period_start' => date('Y-m-d H:i:s', $subscription->current_period_start),
                'current_period_end' => date('Y-m-d H:i:s', $subscription->current_period_end),
            ]);
        }
    }

    private function handleSubscriptionDeleted($subscription)
    {
        $sub = Subscription::where('stripe_subscription_id', $subscription->id)->first();
        if ($sub) {
            $sub->update(['status' => 'cancelled']);
            $sub->organization->update([
                'plan' => 'free',
                'project_limit' => 3,
                'user_limit' => 5,
            ]);
        }
    }

    private function handlePaymentSucceeded($invoice)
    {
        // Log successful payment
        \Log::info('Payment succeeded', ['invoice' => $invoice->id]);
    }

    private function handlePaymentFailed($invoice)
    {
        // Notify organization about failed payment
        \Log::error('Payment failed', ['invoice' => $invoice->id]);
    }

    public function getSubscription(Request $request)
    {
        $organization = Organization::find($request->user()->current_organization_id);
        $subscription = $organization->activeSubscription()->first();

        return response()->json([
            'subscription' => $subscription,
            'organization' => $organization,
        ]);
    }

    public function cancelSubscription(Request $request)
    {
        $organization = Organization::find($request->user()->current_organization_id);
        $subscription = $organization->activeSubscription()->first();

        if (!$subscription || !$subscription->stripe_subscription_id) {
            return response()->json(['error' => 'No active subscription'], 400);
        }

        try {
            \Stripe\Subscription::update($subscription->stripe_subscription_id, [
                'cancel_at_period_end' => true,
            ]);

            return response()->json([
                'message' => 'Subscription will be cancelled at period end',
                'cancel_at' => $subscription->current_period_end,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function resumeSubscription(Request $request)
    {
        $organization = Organization::find($request->user()->current_organization_id);
        $subscription = $organization->activeSubscription()->first();

        if (!$subscription || !$subscription->stripe_subscription_id) {
            return response()->json(['error' => 'No subscription found'], 400);
        }

        try {
            \Stripe\Subscription::update($subscription->stripe_subscription_id, [
                'cancel_at_period_end' => false,
            ]);

            return response()->json([
                'message' => 'Subscription resumed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
