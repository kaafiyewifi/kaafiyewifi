<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Location;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /* ========================
     * INDEX
     * ======================*/
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('id', $q);
            });
        }

        $customers = $query
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    /* ========================
     * STORE
     * ======================*/
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'phone'       => 'required|string|max:50|unique:customers,phone',
            'address'     => 'nullable|string|max:255',
            'location_id' => 'nullable|array',
            'location_id.*' => 'exists:locations,id',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['phone'].'@customer.local',
            'password' => Hash::make('123456'),
        ]);

        $customer = Customer::create([
            'user_id' => $user->id,
            'name'    => $data['name'],
            'phone'   => $data['phone'],
            'address' => $data['address'] ?? null,
            'status'  => 'active',
        ]);

        if (!empty($data['location_id'])) {
            $customer->locations()->sync($data['location_id']);
        }

        return redirect()
            ->route('admin.customers.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Customer waa la diiwaan geliyay',
            ]);
    }

    /* ========================
     * SHOW  ✅ SINGLE METHOD
     * ======================*/
    public function show(Customer $customer)
    {
        $customer->load(['locations', 'subscriptions.plan']);

        $subscriptions = $customer->subscriptions()
            ->orderByRaw("
                CASE status
                    WHEN 'active' THEN 1
                    WHEN 'paused' THEN 2
                    WHEN 'expired' THEN 3
                    WHEN 'cancelled' THEN 4
                    ELSE 5
                END
            ")
            ->orderByDesc('created_at')
            ->get();

        return view('admin.customers.show', [
            'customer'      => $customer,
            'subscriptions' => $subscriptions,
            'plans'         => SubscriptionPlan::where('status', true)->get(),
        ]);
    }

    /* ========================
     * UPDATE
     * ======================*/
    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'phone'       => [
                'required','string','max:50',
                Rule::unique('customers','phone')->ignore($customer->id),
            ],
            'address'     => 'nullable|string|max:255',
            'status'      => 'required|in:active,inactive',
            'location_id' => 'nullable|array',
            'location_id.*' => 'exists:locations,id',
        ]);

        $customer->update($data);

        if ($customer->user) {
            $customer->user->update(['name' => $data['name']]);
        }

        if (isset($data['location_id'])) {
            $customer->locations()->sync($data['location_id']);
        }

        return redirect()
            ->route('admin.customers.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Customer waa la update gareeyay',
            ]);
    }
}
