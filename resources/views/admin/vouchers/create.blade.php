@extends('layouts.admin')

@section('title', 'Create Voucher')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Voucher</h1>

        <a href="{{ route('admin.vouchers.index') }}" class="btn btn-secondary">
            ← Back to Vouchers
        </a>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Create Voucher Form --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.vouchers.store') }}">
                @csrf

                {{-- Plan --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Voucher Plan</label>
                    <select name="plan" class="form-select" required>
                        <option value="" disabled selected>Select plan</option>
                        @foreach ($plans as $key => $plan)
                            <option value="{{ $key }}" {{ old('plan') === $key ? 'selected' : '' }}>
                                {{ $plan['name'] ?? $key }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Submit --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        Create Voucher
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
