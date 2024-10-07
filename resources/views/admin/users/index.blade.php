@extends('layouts.app')
@section('title', 'Manage Users')

@section('content')
    <div class="container">
        @include('components.alerts')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>All Users</h3>
                <a href="{{ route('users.create') }}" class="btn btn-primary float-end">Add User</a>
            </div>

            <div class="card-body table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->userPlan)
                                        {{ $user->userPlan->first()->plan->name }}
                                    @else
                                        No Plan
                                    @endif
                                </td>
                                <td>
                                    @if ($user->userPlan)
                                        {{ $user->userPlan->first()->payment_status }}
                                    @else
                                        No Plan / No Status
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
