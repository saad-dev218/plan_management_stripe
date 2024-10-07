<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\Feature;
use App\Models\Plan;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;

class PlanController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = Plan::with('features')->paginate(10);
        $features = Feature::all();
        return view('admin.plans.index', compact('plans', 'features'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $features = Feature::all();
        return view('admin.plans.create', compact('features'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlanRequest $request)
    {
        try {
            $plan = Plan::create($request->validated());

            $stripeProduct = \Stripe\Product::create([
                'name' => $plan->name,
                'description' => $plan->description,
            ]);

            $stripePrice = \Stripe\Price::create([
                'unit_amount' => $plan->price * 100,
                'currency' => 'pkr',
                'product' => $stripeProduct->id,
            ]);

            $plan->stripe_product_id = $stripeProduct->id;
            $plan->stripe_price_id = $stripePrice->id;
            $plan->save();

            $plan->features()->attach($request->features);

            return redirect()->route('plans.index')->with('success', 'Plan created successfully.');
        } catch (ApiErrorException $e) {
            return redirect()->back()->with('danger', 'Error creating plan: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('danger', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Plan $plan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plan $plan)
    {
        $plan->load('features');
        $features = Feature::all();
        return view('admin.plans.edit', compact('plan', 'features'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        try {
            $plan->update($request->validated());

            if ($plan->stripe_product_id) {
                \Stripe\Product::update($plan->stripe_product_id, [
                    'name' => $plan->name,
                    'description' => $plan->description,
                ]);
            }

            if ($plan->stripe_price_id) {
                $newStripePrice = \Stripe\Price::create([
                    'unit_amount' => $plan->price * 100,
                    'currency' => 'pkr',
                    'product' => $plan->stripe_product_id,
                ]);

                $plan->stripe_price_id = $newStripePrice->id; // Save new price ID
                $plan->save();
            }

            $plan->features()->sync($request->features);

            return redirect()->route('plans.index')->with('success', 'Plan updated successfully.');
        } catch (ApiErrorException $e) {
            return redirect()->back()->with('danger', 'Error updating plan: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('danger', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan)
    {
        try {
            if ($plan->stripe_price_id) {
                $price = \Stripe\Price::retrieve($plan->stripe_price_id);
                $price->active = false;
                $price->save();
            }

            // Deactivate the product
            if ($plan->stripe_product_id) {
                $product = \Stripe\Product::retrieve($plan->stripe_product_id);
                $product->active = false;
                $product->save();
            }

            $plan->features()->detach();
            $plan->delete();

            return redirect()->route('plans.index')->with('success', 'Plan deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('danger', 'Error deleting plan: ' . $e->getMessage());
        }
    }

}
