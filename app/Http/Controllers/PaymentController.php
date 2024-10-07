<?php

namespace App\Http\Controllers;

use App\Models\PaymentRecord;
use App\Models\Plan;
use App\Models\UserPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function purchase(Request $request)
    {
        $user = Auth::user();

        // Validate incoming request
        $request->validate([
            'plan_id' => 'required',
            'payment_method_id' => 'required|string',
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $newPlan = Plan::where('stripe_product_id', $request->plan_id)->first();

        // Begin database transaction
        DB::beginTransaction();

        $existingUserPlan = UserPlan::where('user_id', $user->id)->latest()->first();

        // Create a new customer if not exists
        $customer = $user->customer_id ?? \Stripe\Customer::create([
            'email' => $user->email,
            'name' => $user->name,
        ])->id;

        // Update user customer_id if it was newly created
        if (!$user->customer_id) {
            $user->customer_id = $customer;
            $user->save();
        }

        // Initialize payment and refund amounts
        $paymentAmount = 0;
        $refundAmount = 0;

        // Get Stripe's minimum charge amount for your currency (pkr = 100 minimum)
        $minimumChargeAmount = 100; // in smallest currency unit (e.g., cents)

        // Determine payment or refund amount based on existing plan
        if ($existingUserPlan) {
            $existingPlan = Plan::find($existingUserPlan->plan_id);
            if ($existingPlan->price < $newPlan->price) {
                // Calculate payment amount for upgrade
                $paymentAmount = ($newPlan->price - $existingPlan->price) * 100; // in smallest currency unit
            } elseif ($existingPlan->price > $newPlan->price) {
                // Calculate refund amount for downgrade
                $refundAmount = ($existingPlan->price - $newPlan->price) * 100; // in smallest currency unit

                // Fetch last payment record to process refund
                $lastPaymentRecord = PaymentRecord::where('user_id', $user->id)->latest()->first();
                if ($lastPaymentRecord) {
                    $lastPaymentAmount = $lastPaymentRecord->amount * 100; // Convert to the smallest currency unit

                    // Cap refund amount to the amount that was charged
                    if ($refundAmount > $lastPaymentAmount) {
                        $refundAmount = $lastPaymentAmount; // Cap refund amount to last payment amount
                    }

                    $lastPaymentDate = $lastPaymentRecord->created_at;
                    $currentDate = now();

                    // Process refund if within 30 days
                    if ($lastPaymentDate && $lastPaymentDate->diffInDays($currentDate) <= 30) {
                        // Only create a refund if refundAmount is greater than 0
                        if ($refundAmount > 0) {
                            \Stripe\Refund::create([
                                'payment_intent' => $lastPaymentRecord->transaction_id,
                                'amount' => $refundAmount,
                            ]);
                        }
                    }
                }
            }
        } else {
            // New plan purchase
            $paymentAmount = $newPlan->price * 100; // in smallest currency unit
        }

        // Ensure the paymentAmount is above Stripe's minimum allowed charge
        if ($paymentAmount < $minimumChargeAmount) {
            DB::rollBack();
            return redirect()->back()->with(['error' => 'The payment amount is too small to process.']);
        }
        // If paymentAmount is zero (upgrade/downgrade scenario), use a Setup Intent
        if ($paymentAmount == 0) {
            $setupIntent = \Stripe\SetupIntent::create([
                'customer' => $customer,
                'payment_method' => $request->payment_method_id,
                'confirm' => true,
            ]);


            // Complete the transaction with no immediate payment but save the payment method for future use
            DB::commit();
            return redirect()->back()->with(['success' => 'Plan updated successfully with no additional charge.', 'plan' => $newPlan]);
        }

        // Create payment intent for payment
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $paymentAmount,
            'currency' => 'pkr',
            'payment_method' => $request->payment_method_id,
            'customer' => $customer,
            'confirmation_method' => 'manual',
            'confirm' => true,
            'description' => 'Payment for plan: ' . $newPlan->name,
            'return_url' => route('home'),
        ]);



        // Check payment intent status
        if ($paymentIntent->status == 'succeeded') {
            // Update or create user plan record
            if ($existingUserPlan) {
                $existingUserPlan->plan_id = $newPlan->id;
                $existingUserPlan->stripe_subscription_id = $paymentIntent->id;
                $existingUserPlan->payment_status = 'active';
                $existingUserPlan->save();
            } else {
                UserPlan::create([
                    'user_id' => $user->id,
                    'plan_id' => $newPlan->id,
                    'stripe_subscription_id' => $paymentIntent->id,
                    'payment_status' => 'active',
                ]);
            }

            // Record the payment
            PaymentRecord::create([
                'user_id' => $user->id,
                'plan_id' => $newPlan->id,
                'transaction_id' => $paymentIntent->id,
                'amount' => $paymentAmount / 100,
                'transaction_response' => json_encode($paymentIntent),
                'status' => 'success',
            ]);

            // Commit the transaction
            DB::commit();

            return redirect()->back()->with(['message' => 'Purchase successful!', 'plan' => $newPlan]);
        } else {
            // Rollback the transaction if payment requires further action
            DB::rollBack();
            return redirect()->back()->with(['error' => 'Payment requires further action: ' . $paymentIntent->status], 400);
        }
    }
}
