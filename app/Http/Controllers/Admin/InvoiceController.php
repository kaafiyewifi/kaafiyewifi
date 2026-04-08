<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index()
    {
        $query = DB::table('invoices')
            ->leftJoin('customers', 'invoices.customer_id', '=', 'customers.id')
            ->select(
                'invoices.id',
                'invoices.customer_id',
                'invoices.amount',
                'invoices.status',
                'invoices.paid_at',
                'invoices.created_at',
                'customers.full_name',
                'customers.phone',
                'customers.username'
            );

        if (!$this->isSuperAdmin()) {
            $query->whereIn('customers.location_id', $this->getAssignedLocationIds());
        }

        $invoices = $query
            ->orderByDesc('invoices.id')
            ->paginate(10);

        return view('admin.invoices.index', compact('invoices'));
    }

    public function show($id)
    {
        $query = DB::table('invoices')
            ->leftJoin('customers', 'invoices.customer_id', '=', 'customers.id')
            ->select(
                'invoices.*',
                'customers.full_name',
                'customers.phone',
                'customers.username'
            )
            ->where('invoices.id', $id);

        if (!$this->isSuperAdmin()) {
            $query->whereIn('customers.location_id', $this->getAssignedLocationIds());
        }

        $invoice = $query->first();

        abort_if(!$invoice, 404);

        return view('admin.invoices.show', compact('invoice'));
    }

    private function isSuperAdmin(): bool
    {
        return auth()->check()
            && method_exists(auth()->user(), 'hasRole')
            && auth()->user()->hasRole('super_admin');
    }

    private function getAssignedLocationIds()
    {
        $user = auth()->user();

        if (!$user || !method_exists($user, 'locations')) {
            return collect();
        }

        return $user->locations()->pluck('locations.id');
    }
}