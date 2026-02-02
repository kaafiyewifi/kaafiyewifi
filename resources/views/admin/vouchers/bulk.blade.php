@extends('layouts.admin')

@section('title', 'Bulk Generate Vouchers')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Bulk Generate Vouchers</h1>
        <a href="{{ route('admin.vouchers.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.vouchers.bulkStore') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Plan</label>
                    <select name="plan" class="form-select" required>
                        @foreach($plans as $key => $p)
                            <option value="{{ $key }}">{{ $p['name'] ?? $key }}</option>
                        @endforeach
                    </select>
                    @error('plan')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Count (max 500)</label>
                    <input type="number" name="count" class="form-control" min="1" max="500" value="50" required>
                    @error('count')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Generate</button>
            </form>
        </div>
    </div>

</div>
@endsection
