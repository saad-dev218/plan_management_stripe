@extends('layouts.app')
@section('title', 'Home')
@push('styles')
    <style>
        .card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .responsive-card-body {
            min-height: 250px;
        }

        .plan-features {
            max-height: 200px;
            min-height: 100px;
            overflow-y: auto;
        }

        .card-footer {
            margin-top: auto;
        }
    </style>
@endpush
@section('content')
    <!-- Pricing Section -->
    <section class="pricing py-5">
        <div class="container">
            @include('components.alerts')
            <div class="row">
                <!-- Loop through the plans -->
                @foreach ($plans as $plan)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card mb-5 mb-lg-0">
                            <div class="card-header text-center">
                                <h2 class="card-title">{{ $plan->name ?? 'Not Available' }}</h2>
                                <h3 class="card-price">{{ $plan->price ?? 'Not Available' }} PKR/Month</h3>
                            </div>
                            <div class="card-body responsive-card-body">
                                <ul class="list-group list-group-flush plan-features">
                                    @foreach ($plan->features as $feature)
                                        <li class="list-group-item">
                                            <span class="fa-li"><i class="fas fa-check"></i></span>
                                            {{ $feature->name ?? 'Not Available' }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="card-footer">
                                <div class="d-grid">
                                    @if (auth()->check() && auth()->user()->role !== 'admin')
                                        @php
                                            $userSubscription = auth()->user()->userSubs; // Get user's subscription
                                        @endphp
                                        @if ($userSubscription && $userSubscription->plan_id === $plan->id)
                                            <button class="btn btn-secondary mt-3" disabled>Current Package</button>
                                        @else
                                            <button class="btn btn-primary mt-3 purchase-btn"
                                                data-plan-id="{{ $plan->stripe_product_id }}"
                                                data-plan-price="{{ $plan->price }}"
                                                data-plan-stripe-price="{{ $plan->stripe_price_id }}">
                                                Buy Now
                                            </button>
                                        @endif
                                    @elseif(!auth()->check())
                                        <a href="{{ route('register') }}" class="btn btn-primary mt-3">Register to Buy</a>
                                    @else
                                        <button class="btn btn-primary mt-3 disabled">Admin Can't Buy</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>


    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Payment Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="payment-form" method="POST" action="{{ route('subscription.create') }}">
                        @csrf
                        <div id="card-element" class="mb-3">

                        </div>
                        <input type="hidden" name="plan_id" id="plan_id">
                        <input type="hidden" name="stripe_price_id" id="stripe_price_id">
                        <input type="hidden" name="plan_price" id="plan_price">
                        <button type="submit" class="btn btn-primary mt-3">Submit Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            var stripe = Stripe('{{ env('STRIPE_KEY') }}');
            var elements = stripe.elements();
            var card = elements.create('card');
            card.mount('#card-element');

            // Open modal and pass plan info
            $('.purchase-btn').on('click', function() {
                $('#plan_id').val($(this).data('plan-id'));
                $('#plan_price').val($(this).data('plan-price'));
                $('#stripe_price_id').val($(this).data('plan-stripe-price'));
                $('#paymentModal').modal('show');
            });

            // Handle form submission
            var form = document.getElementById('payment-form');
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                stripe.createToken(card).then(function(result) {
                    if (result.error) {
                        // Display error
                        alert(result.error.message);
                    } else {
                        // Add Stripe token to form
                        var hiddenInput = document.createElement('input');
                        hiddenInput.setAttribute('type', 'hidden');
                        hiddenInput.setAttribute('name', 'stripeToken');
                        hiddenInput.setAttribute('value', result.token.id);
                        form.appendChild(hiddenInput);

                        // Submit the form
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
