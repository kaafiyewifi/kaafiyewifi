<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Controllers\Controller;
use App\Models\Radius\Voucher;
use App\Services\Radius\VoucherService;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::query()->orderByDesc('id')->paginate(30);

        return view('admin.vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        $plans = config('voucher_plans');

        return view('admin.vouchers.create', compact('plans'));
    }

    public function store(Request $request, VoucherService $service)
    {
        $data = $request->validate([
            'plan' => ['required', 'string'],
        ]);

        $voucher = $service->create($data['plan']);

        return redirect()->route('admin.vouchers.index')
            ->with('success', "Voucher created: {$voucher->code}");
    }

    public function bulkForm()
    {
        $plans = config('voucher_plans');

        return view('admin.vouchers.bulk', compact('plans'));
    }

    public function bulkStore(Request $request, VoucherService $service)
    {
        $data = $request->validate([
            'plan' => ['required', 'string'],
            'count' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        $service->bulkCreate($data['plan'], $data['count']);

        return redirect()->route('admin.vouchers.index')
            ->with('success', "Bulk vouchers generated: {$data['count']}");
    }
}
