<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = DB::table('invoices')
            ->leftJoin('customers', 'invoices.customer_id', '=', 'customers.id')
            ->select(
                'invoices.id',
                'invoices.customer_id',
                'invoices.amount',
                'invoices.status',
                'invoices.paid_at',
                'invoices.created_at',

                // ✅ sax columns jira
                'customers.full_name',
                'customers.phone',
                'customers.username'
            )
            ->orderByDesc('invoices.id')
            ->paginate(10);

        return view('admin.invoices.index', compact('invoices'));
    }

    public function show($id)
    {
        $invoice = DB::table('invoices')
            ->leftJoin('customers', 'invoices.customer_id', '=', 'customers.id')
            ->select(
                'invoices.*',
                'customers.full_name',
                'customers.phone',
                'customers.username'
            )
            ->where('invoices.id', $id)
            ->first();

        abort_if(!$invoice, 404);

        return view('admin.invoices.show', compact('invoice'));
    }
}