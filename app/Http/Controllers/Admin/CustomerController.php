<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use App\Models\Location;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct()
    {
        // Haddii route-ka aad ka saartay middleware-ka, halkan geli:
        // $this->middleware('permission:manage customers');
    }

    /**
     * List customers (filtered by allowed locations + filters/search).
     */
    public function index(Request $request)
    {
        $allowedIds = $request->user()->allowedLocationIds()->toArray();

        // locations dropdown (only allowed)
        $locations = Location::query()
            ->whereIn('id', $allowedIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $q = trim((string) $request->get('q'));
        $status = $request->get('status');          // active|inactive|null
        $locationId = $request->get('location_id'); // id|null

        $customers = Customer::query()
            ->whereIn('location_id', $allowedIds)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('full_name', 'like', "%{$q}%")
                       ->orWhere('username', 'like', "%{$q}%")
                       ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->when($locationId, function ($query) use ($locationId, $allowedIds) {
                // extra safety: locationId must be in allowed
                if (in_array((int) $locationId, $allowedIds, true)) {
                    $query->where('location_id', (int) $locationId);
                }
            })
            ->when($status, function ($query) use ($status) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->latest()
            ->paginate(20);

        return view('admin.customers.index', compact('customers', 'locations'));
    }

    public function create(Request $request)
    {
        $allowedIds = $request->user()->allowedLocationIds()->toArray();

        $locations = Location::query()
            ->whereIn('id', $allowedIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.customers.create', compact('locations'));
    }

   public function store(StoreCustomerRequest $request)
{
    $data = $request->validated();

    // username & password -> model booted() auto
    unset($data['username'], $data['password']);

    // ✅ default active haddii uusan imaan
    $data['is_active'] = $request->boolean('is_active', true);

    Customer::create($data);

    return redirect()
        ->route('admin.customers.index')
        ->with('success', 'Customer created successfully. Default password is 123456.');
}

    public function edit(Request $request, Customer $customer)
    {
        abort_unless($request->user()->canAccessLocation((int) $customer->location_id), 403);

        $allowedIds = $request->user()->allowedLocationIds()->toArray();

        $locations = Location::query()
            ->whereIn('id', $allowedIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.customers.edit', compact('customer', 'locations'));
    }

    public function update(Request $request, Customer $customer)
    {
        abort_unless($request->user()->canAccessLocation((int) $customer->location_id), 403);

        $allowedIds = $request->user()->allowedLocationIds()->toArray();

        $data = $request->validate([
            'full_name'   => ['required', 'string', 'max:255'],
            'phone'       => ['required', 'string', 'unique:customers,phone,' . $customer->id],
            'location_id' => ['required', 'integer', 'in:' . implode(',', $allowedIds)],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        // ✅ username = phone (always)
        $data['username'] = $data['phone'];

        $customer->update($data);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Request $request, Customer $customer)
    {
        abort_unless($request->user()->canAccessLocation((int) $customer->location_id), 403);

        $customer->delete();

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
