<?php

namespace App\Http\Controllers\Admin;
use App\Models\SubscriptionPlan;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Location;
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

        /* SEARCH */
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('id', $q);
            });
        }

        /* SORT */
        $allowedSorts = ['id','name','phone'];
        $sortBy  = in_array($request->sort, $allowedSorts) ? $request->sort : 'id';
        $sortDir = $request->dir === 'asc' ? 'asc' : 'desc';

        $customers = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(10)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'locations' => Location::orderBy('name')->get(), // modal create/edit
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

        /* CREATE USER */
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['phone'].'@customer.local',
            'password' => Hash::make('123456'),
        ]);

        /* CREATE CUSTOMER */
        $customer = Customer::create([
            'user_id' => $user->id,
            'name'    => $data['name'],
            'phone'   => $data['phone'],
            'address' => $data['address'] ?? null,
            'status'  => 'active',
        ]);

        /* ATTACH LOCATIONS */
        if (!empty($data['location_id'])) {
            $customer->locations()->sync($data['location_id']);
        }

        /* 🔔 TOAST NOTIFICATION */
        return redirect()
            ->route('admin.customers.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Customer si guul leh ayaa loo diiwaan geliyay'
            ]);
    }

    /* ========================
     * SHOW
     * ======================*/
    public function show(Customer $customer)
{
    $customer->load(['locations','subscriptions.plan']);

    return view('admin.customers.show', [
        'customer' => $customer,
        'plans' => SubscriptionPlan::orderBy('price')->get(), // ✅ MUHIIM
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
                'required',
                'string',
                'max:50',
                Rule::unique('customers','phone')->ignore($customer->id),
            ],
            'address'     => 'nullable|string|max:255',
            'status'      => 'required|in:active,inactive',
            'location_id' => 'nullable|array',
            'location_id.*' => 'exists:locations,id',
        ]);

        /* UPDATE CUSTOMER */
        $customer->update([
            'name'    => $data['name'],
            'phone'   => $data['phone'],
            'address' => $data['address'] ?? null,
            'status'  => $data['status'],
        ]);

        /* UPDATE USER NAME */
        if ($customer->user) {
            $customer->user->update([
                'name' => $data['name'],
            ]);
        }

        /* SYNC LOCATIONS */
        if (isset($data['location_id'])) {
            $customer->locations()->sync($data['location_id']);
        }

        /* 🔔 TOAST NOTIFICATION */
        return redirect()
            ->route('admin.customers.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Customer si guul leh ayaa loo update gareeyay'
            ]);
    }
}
