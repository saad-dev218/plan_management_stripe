@extends('layouts.app')
@section('content')
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
    <!-- Pricing Section -->
    <section class="pricing py-5">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="row">
                @foreach ($plans as $plan)
                    <div class="col-lg-4">
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
                                            $userSubscription = auth()->user()->userPlan;
                                        @endphp
                                        @if ($userSubscription && $userSubscription->plan_id === $plan->id)
                                            <button class="btn btn-secondary mt-3" disabled>Current Package</button>
                                        @else
                                            <button class="btn btn-primary mt-3 purchase-btn"
                                                data-plan-id="{{ $plan->stripe_product_id }}"
                                                data-plan-price="{{ $plan->price }}"
                                                data-plan-stripe-price="{{ $plan->stripe_price_id }}"
                                                data-plan-name="{{ $plan->name }}">
                                                @if ($userSubscription && $plan->price > $userSubscription->plan->price)
                                                    Upgrade Now
                                                @elseif ($userSubscription && $plan->price < $userSubscription->plan->price)
                                                    Downgrade Now
                                                @else
                                                    Buy Now
                                                @endif
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
                    <form id="payment-form" action="{{ route('purchase') }}" method="POST">
                        @csrf
                        <input type="hidden" name="plan_id" id="plan_id" value="">
                        <input type="hidden" name="payment_method_id" id="payment_method_id" value="">
                        <div id="card-element"></div>
                        <div id="card-errors" role="alert"></div>
                        <button type="submit" class="btn btn-primary mt-3">Submit Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const stripe = Stripe('{{ env('STRIPE_KEY') }}');
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');

        $('.purchase-btn').click(function() {
            if (!{{ Auth::check() ? 'true' : 'false' }}) {
                window.location.href = "{{ route('login') }}";
            } else {
                const planId = $(this).data('plan-id');
                const planPrice = $(this).data('plan-price');
                $('#plan_id').val(planId);
                $('#payment_method_id').val(''); // This will be filled when form is submitted
                $('#paymentModal').modal('show');
            }
        });

        $('#payment-form').on('submit', async function(event) {
            event.preventDefault();

            const {
                paymentMethod,
                error
            } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
            });

            if (error) {
                document.getElementById('card-errors').textContent = error.message;
            } else {
                $('#payment_method_id').val(paymentMethod.id); // Set payment method ID before form submission
                this.submit(); // Now submit the form
            }
        });

        cardElement.on('change', (event) => {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
    </script>
@endpush
