@extends('layouts.admin')

@section('title', 'Vouchers')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Vouchers</h1>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.vouchers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Voucher
            </a>

            <a href="{{ route('admin.vouchers.bulkForm') }}" class="btn btn-secondary">
                <i class="fas fa-layer-group"></i> Bulk Generate
            </a>
        </div>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Vouchers Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vouchers as $voucher)
                            <tr>
                                <td>{{ $voucher->id }}</td>

                                <td>
                                    <strong>{{ $voucher->code }}</strong>
                                </td>

                                <td>{{ $voucher->username }}</td>

                                <td>{{ $voucher->password }}</td>

                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $voucher->plan }}
                                    </span>
                                </td>

                                <td>
                                    @if($voucher->is_used)
                                        <span class="badge bg-danger">Used</span>
                                    @else
                                        <span class="badge bg-success">Unused</span>
                                    @endif
                                </td>

                                <td>{{ $voucher->created_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No vouchers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($vouchers->hasPages())
            <div class="card-footer">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
