<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        try {
            if (!$request->has('stripeToken') || !$request->has('plan_id')) {
                throw new \Exception('Invalid payment or plan details.');
            }

            // Create or retrieve customer
            if (!$user->customer_id) {
                $paymentMethod = $stripe->paymentMethods->create([
                    'type' => 'card',
                    'card' => ['token' => $request->stripeToken],
                ]);

                $customer = $stripe->customers->create([
                    'email' => $user->email,
                    'payment_method' => $paymentMethod->id,
                    'invoice_settings' => [
                        'default_payment_method' => $paymentMethod->id,
                    ],
                ]);

                $user->customer_id = $customer->id;
                $user->save();
            } else {
                $customer = $stripe->customers->retrieve($user->customer_id);
                $paymentMethod = $stripe->paymentMethods->create([
                    'type' => 'card',
                    'card' => ['token' => $request->stripeToken],
                ]);

                $stripe->paymentMethods->attach($paymentMethod->id, ['customer' => $customer->id]);

                $stripe->customers->update($customer->id, [
                    'invoice_settings' => [
                        'default_payment_method' => $paymentMethod->id,
                    ],
                ]);
            }

            $plan = Plan::where('stripe_product_id', $request->plan_id)->first();

            if (!$plan) {
                throw new \Exception('Invalid plan selected.');
            }

            $existingSubscription = UserSubscription::where('user_id', $user->id)
                ->where('payment_status', 'active')
                ->first();

            if ($existingSubscription) {
                // Get the existing subscription from Stripe
                $existingStripeSubscription = $stripe->subscriptions->retrieve($existingSubscription->stripe_subscription_id);

                // If upgrading/downgrading, use Stripe's proration settings
                $updatedSubscription = $stripe->subscriptions->update($existingStripeSubscription->id, [
                    'items' => [
                        [
                            'id' => $existingStripeSubscription->items->data[0]->id,
                            'price' => $plan->stripe_price_id,
                        ]
                    ],
                    'proration_behavior' => 'create_prorations',
                ]);

                // Update the local database
                $existingSubscription->plan_id = $plan->id;
                $existingSubscription->stripe_price_id = $plan->stripe_price_id;
                $existingSubscription->save();

            } else {
                // New subscription creation
                $subscription = $stripe->subscriptions->create([
                    'customer' => $customer->id,
                    'items' => [['price' => $plan->stripe_price_id]],
                ]);

                // Store the user subscription
                UserSubscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'stripe_subscription_id' => $subscription->id,
                    'stripe_customer_id' => $customer->id,
                    'stripe_price_id' => $plan->stripe_price_id,
                    'payment_status' => 'active',
                ]);
            }

            // Store the payment record
            \App\Models\PaymentRecord::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'transaction_id' => $updatedSubscription->id ?? $subscription->id,
                'transaction_response' => json_encode($updatedSubscription ?? $subscription),
                'status' => 'success',
            ]);

            return redirect()->route('home')->with('success', 'Subscription created/updated successfully!');
        } catch (\Stripe\Exception\CardException $e) {
            return back()->withErrors(['message' => 'Payment error: ' . $e->getMessage()]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return back()->withErrors(['message' => 'Stripe API error: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error creating subscription: ' . $e->getMessage()]);
        }
    }
}
