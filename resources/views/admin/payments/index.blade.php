@extends('layouts.app')
@section('title', 'Payments')
@section('content')
    <div class="container">
        @include('components.alerts')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-content-center">
                <h3>All Payments</h3>
            </div>

            <div class="card-body table-responsive">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <th>ID</th>
                            <th>User-Name</th>
                            <th>Plan-Name</th>
                            <th>Transaction ID</th>
                            <th>Status</th>
                            <th>Date Time</th>
                        </tr>
                        @foreach ($payments as $payment)
                            <tr>
                                <td>{{ $payment->id }}</td>
                                <td>{{ $payment->user->name ?? 'N/A' }}</td>
                                <td>{{ $payment->plan->name ?? 'N/A' }}</td>
                                <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
                                <td>{{ $payment->status ?? 'N/A' }}</td>
                                <td>{{ $payment->created_at ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                {{ $payments->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <!-- SweetAlert for Delete Confirmation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    @include('admin.plans.script')
@endpush
