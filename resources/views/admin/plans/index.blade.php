@extends('layouts.app')
@section('title', 'Manage Plans')
@section('content')
    <div class="container">
        @include('components.alerts')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-content-center">
                <h3>All Plans</h3>
                <a href="{{ route('plans.create') }}" class="btn btn-primary float-end">Create Plan</a>
            </div>

            <div class="card-body table-responsive">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Features Count</th>
                            <th>Action</th>
                        </tr>
                        @foreach ($plans as $plan)
                            <tr>
                                <td>{{ $plan->name ?? 'N/A' }}</td>
                                <td>{{ $plan->price ?? '0' }} PKR</td>
                                <td>{{ $plan->features->count() ?? 0 }} Features</td>
                                <td>
                                    <a href="{{ route('plans.edit', $plan) }}" class="btn btn-warning">Edit</a>
                                    <form action="{{ route('plans.destroy', $plan) }}" method="POST"
                                        id="delete-plan-form-{{ $plan->id }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger delete-plan"
                                            data-id="{{ $plan->id }}">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                {{ $plans->links('pagination::bootstrap-5') }}'
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <!-- SweetAlert for Delete Confirmation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    @include('admin.plans.script')
@endpush
