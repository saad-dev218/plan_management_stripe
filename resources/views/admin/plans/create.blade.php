@extends('layouts.app')
@section('title', 'Create Plan')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
@endpush
@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-content-center">
                <h3>Create Plan</h3>
                <a href="{{ route('plans.index') }}" class="btn btn-primary float-end">Manage Plans</a>
            </div>
            <form action="{{ route('plans.store') }}" method="POST" id="create-plan-form">
                <div class="card-body">
                    @csrf
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" placeholder="Enter Plan Name" class="form-control" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mt-2">
                        <label for="description">Description</label>
                        <textarea class="form-control" placeholder="Enter Plan Description" name="description">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mt-2">
                        <label for="price">Price</label>
                        <input type="number" class="form-control" placeholder="Enter Plan Price" name="price"
                            step="0.01" value="{{ old('price') }}" required>
                        @error('price')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mt-2">
                        <label for="features">Features</label>
                        <select class="form-control select2" name="features[]" data-placeholder="Select Features" multiple="multiple" required>
                            @foreach ($features as $feature)
                                <option value="{{ $feature->id }}">{{ $feature->name }}</option>
                            @endforeach
                        </select>
                        @error('features')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('plans.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success">Create Plan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Select Features',
                allowClear: true,
            });
        });
    </script>
@endpush
