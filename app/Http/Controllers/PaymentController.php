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

        $request->validate([
            'plan_id' => 'required',
            'payment_method_id' => 'required|string',
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $newPlan = Plan::where('stripe_product_id', $request->plan_id)->first();

        $existingUserPlan = UserPlan::where('user_id', $user->id)->latest()->first();

        $customer = $user->customer_id ?? \Stripe\Customer::create([
            'email' => $user->email,
            'name' => $user->name,
        ])->id;

        if (!$user->customer_id) {
            $user->customer_id = $customer;
            $user->save();
        }

        $paymentAmount = 0;
        $refundAmount = 0;
        $minimumChargeAmount = 100;

        if ($existingUserPlan) {
            $existingPlan = Plan::find($existingUserPlan->plan_id);

            if ($existingPlan->price < $newPlan->price) {
                $paymentAmount = ($newPlan->price - $existingPlan->price) * 100;
            } elseif ($existingPlan->price > $newPlan->price) {
                $refundAmount = ($existingPlan->price - $newPlan->price) * 100;

                $lastPaymentRecord = PaymentRecord::where('user_id', $user->id)->latest()->first();

                if ($lastPaymentRecord) {
                    $lastPaymentAmount = $lastPaymentRecord->amount * 100;

                    if ($refundAmount > $lastPaymentAmount) {
                        $refundAmount = $lastPaymentAmount;
                    }

                    $lastPaymentDate = $lastPaymentRecord->created_at;
                    $currentDate = now();

                    if ($lastPaymentDate && $lastPaymentDate->diffInDays($currentDate) <= 30) {
                        if ($refundAmount > 0) {
                            try {
                                // Attempt to process refund
                                $refund = \Stripe\Refund::create([
                                    'payment_intent' => $lastPaymentRecord->transaction_id,
                                    'amount' => $refundAmount,
                                ]);

                                // Save the refund data in the database
                                PaymentRecord::create([
                                    'user_id' => $user->id,
                                    'plan_id' => $existingPlan->id,
                                    'transaction_id' => $refund->id,
                                    'amount' => $refundAmount / 100,
                                    'transaction_response' => json_encode($refund),
                                    'status' => 'success',
                                ]);
                            } catch (\Stripe\Exception\InvalidRequestException $e) {
                                // Check if the error is due to an already refunded charge
                                if (strpos($e->getMessage(), 'has already been refunded') !== false) {
                                    // Update the local database as if the refund was successful
                                    PaymentRecord::create([
                                        'user_id' => $user->id,
                                        'plan_id' => $existingPlan->id,
                                        'transaction_id' => $lastPaymentRecord->transaction_id,
                                        'amount' => $refundAmount / 100,
                                        'transaction_response' => json_encode(['error' => 'Already refunded']),
                                        'status' => 'success',
                                    ]);
                                } else {
                                    return redirect()->back()->with(['error' => $e->getMessage()]);
                                }
                            }

                            // Update user plan after refund
                            $existingUserPlan->plan_id = $newPlan->id;
                            $existingUserPlan->stripe_subscription_id = $lastPaymentRecord->transaction_id; // Use the same transaction ID
                            $existingUserPlan->payment_status = 'active';
                            $existingUserPlan->save();

                            return redirect()->back()->with(['success' => 'Plan downgraded, refund processed!', 'plan' => $newPlan]);
                        }
                    }
                }
            }
        } else {
            $paymentAmount = $newPlan->price * 100;
        }

        if ($paymentAmount < $minimumChargeAmount) {
            return redirect()->back()->with(['error' => 'The payment amount is too small to process.']);
        }

        if ($paymentAmount == 0) {
            \Stripe\SetupIntent::create([
                'customer' => $customer,
                'payment_method' => $request->payment_method_id,
                'confirm' => true,
            ]);

            return redirect()->back()->with(['success' => 'Plan updated successfully with no additional charge.', 'plan' => $newPlan]);
        }

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

        PaymentRecord::create([
            'user_id' => $user->id,
            'plan_id' => $newPlan->id,
            'transaction_id' => $paymentIntent->id,
            'amount' => $paymentAmount / 100,
            'transaction_response' => json_encode($paymentIntent),
            'status' => 'success',
        ]);

        return redirect()->back()->with(['success' => 'Purchase successful!', 'plan' => $newPlan]);
    }

}
