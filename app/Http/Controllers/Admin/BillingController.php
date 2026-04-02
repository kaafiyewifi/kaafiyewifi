<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'plan_id' => 'required|exists:plans,id',
            'amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // 1. Create Invoice
            $invoiceId = DB::table('invoices')->insertGetId([
                'customer_id' => $request->customer_id,
                'amount' => $request->amount,
                'status' => 'paid',
                'paid_at' => Carbon::now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Create Subscription (REAL billing)
            DB::table('subscriptions')->insert([
                'customer_id' => $request->customer_id,
                'plan_id' => $request->plan_id,
                'price' => $request->amount,
                'status' => 'active',
                'starts_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addMonth(),
                'paid_at' => Carbon::now(), // 🔥 muhiim
                'invoice_id' => $invoiceId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Subscription + Invoice created successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function todaySales()
    {
        return (float) DB::table('subscriptions')
            ->whereDate('paid_at', Carbon::today())
            ->sum('price');
    }

    public function monthlySales()
    {
        return (float) DB::table('subscriptions')
            ->whereYear('paid_at', Carbon::now()->year)
            ->whereMonth('paid_at', Carbon::now()->month)
            ->sum('price');
    }
}