<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
 use App\Models\SubscriptionPlan;

class CustomerController extends Controller
{
    /**
     * LIST CUSTOMERS
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // SEARCH
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('id', $q);
            });
        }

        // SORT
        $allowedSorts = ['id', 'name', 'phone'];
        $sortBy  = in_array($request->sort, $allowedSorts) ? $request->sort : 'id';
        $sortDir = $request->dir === 'asc' ? 'asc' : 'desc';

        $customers = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(10)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    /**
     * STORE NEW CUSTOMER
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'nullable|string|max:255',
            'location_id' => 'nullable|array',
            'location_id.*' => 'exists:locations,id',
        ]);

        // Hubi customer hore u jiro
        if (Customer::where('phone', $data['phone'])->exists()) {
            return back()->withInput()->with('error', 'Customer-kan horay ayaa loo abuuray.');
        }

        // Hubi user hore u jiro
        if (User::where('email', $data['phone'].'@customer.local')->exists()) {
            return back()->withInput()->with('error', 'Customer-kan horay ayaa u diiwaangashan.');
        }

        // Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['phone'].'@customer.local',
            'password' => Hash::make('123456'),
        ]);

        // Create customer
        $customer = Customer::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'address' => $data['address'] ?? null,
            'status' => 'active',
        ]);

        // Attach locations
        $customer->locations()->sync($data['location_id'] ?? []);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer si guul leh ayaa loo abuuray');
    }

    /**
     * SHOW CUSTOMER
     */


public function show(Customer $customer)
{
    $customer->load([
        'locations',
        'payments',
        'devices',
        'creator',
        'subscriptions.plan'
    ]);

    $plans = \App\Models\SubscriptionPlan::orderBy('price')->get();

    return view('admin.customers.show', compact('customer','plans'));
}




    /**
     * UPDATE CUSTOMER
     */
    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50|unique:customers,phone,' . $customer->id,
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'location_id' => 'nullable|array',
            'location_id.*' => 'exists:locations,id',
        ]);

        $customer->update([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'address' => $data['address'] ?? null,
            'status' => $data['status'],
        ]);

        $customer->locations()->sync($data['location_id'] ?? []);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer updated successfully');
    }
}
