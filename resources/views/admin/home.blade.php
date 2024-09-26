@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header h4">{{ __('All Plans') }}</div>
                    <div class="card-body">
                        <h5 class="card-title">Total Plans: <strong>{{ $plans_count }}</strong></h5>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('plans.index') }}" class="btn btn-primary float-end"> {{ __('Manage Plans') }}</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header h4">{{ __('All Users') }}</div>

                    <div class="card-body">
                        <h5 class="card-title">Total Users: <strong>{{ $users_count }}</strong></h5>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="btn btn-primary float-end"> {{ __('Manage Users') }}</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
