<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\MikroTikService;
use Illuminate\Support\Str;

class CustomerSubscriptionController extends Controller
{
    /**
     * SHOW ADD SUBSCRIPTION FORM
     */
    public function create(Customer $customer)
    {
        return view('admin.customers.subscribe', [
            'customer' => $customer,
            'plans'    => SubscriptionPlan::where('status', true)->orderBy('price')->get(),
        ]);
    }

    /**
     * STORE SUBSCRIPTION + AUTO CREATE ROUTER USER
     */
    public function store(Request $request, Customer $customer, MikroTikService $mikrotik)
    {
        $data = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'value'   => 'required|integer|min:1',
            'unit'    => 'required|in:days,hours',
        ]);

        // ❗ HAL ACTIVE SUBSCRIPTION OO KALIYA
        $customer->subscriptions()
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        $plan = SubscriptionPlan::findOrFail($data['plan_id']);

        // ⏰ DATES
        $startsAt = Carbon::now();
        $expiresAt = $data['unit'] === 'hours'
            ? $startsAt->copy()->addHours($data['value'])
            : $startsAt->copy()->addDays($data['value']);

        // 💰 PRICE
        $pricePerDay  = $plan->price / 30;
        $pricePerHour = $pricePerDay / 24;

        $finalPrice = $data['unit'] === 'hours'
            ? round($pricePerHour * $data['value'], 2)
            : round($pricePerDay * $data['value'], 2);

        // 🔐 ROUTER USER
        $routerUsername = 'sub_'.$customer->id.'_'.time();
        $routerPassword = Str::random(8);

        $mikrotik->createHotspotUser(
            $routerUsername,
            $routerPassword,
            $plan->router_profile
        );

        // ✅ CREATE SUBSCRIPTION
        Subscription::create([
            'customer_id'      => $customer->id,
            'plan_id'          => $plan->id,
            'price'            => $finalPrice,
            'starts_at'        => $startsAt,
            'expires_at'       => $expiresAt,
            'status'           => 'active',
            'auto_renew'       => false,
            'router_username'  => $routerUsername,
            'router_password'  => $routerPassword,
        ]);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Subscription + Router user waa la sameeyay',
            ]);
    }

    /**
     * EXTEND SUBSCRIPTION
     */
    public function extend(Request $request, Subscription $sub)
    {
        $data = $request->validate([
            'value' => 'required|integer|min:1',
            'unit'  => 'required|in:days,hours',
        ]);

        $newExpire = $data['unit'] === 'hours'
            ? $sub->expires_at->copy()->addHours($data['value'])
            : $sub->expires_at->copy()->addDays($data['value']);

        $sub->update(['expires_at' => $newExpire]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Subscription waa la kordhiyay',
        ]);
    }

    /**
     * PAUSE SUBSCRIPTION + DISABLE ROUTER USER
     */
    public function pause(Subscription $sub, MikroTikService $mikrotik)
    {
        if ($sub->router_username) {
            $mikrotik->disableUser($sub->router_username);
        }

        $sub->update(['status' => 'paused']);

        return back()->with('toast', [
            'type' => 'warning',
            'message' => 'Subscription waa la hakiyay (Router disabled)',
        ]);
    }

    /**
     * RESUME SUBSCRIPTION + ENABLE ROUTER USER
     */
    public function resume(Subscription $sub, MikroTikService $mikrotik)
    {
        if ($sub->router_username) {
            $mikrotik->enableUser($sub->router_username);
        }

        $sub->update(['status' => 'active']);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Subscription waa la sii waday (Router enabled)',
        ]);
    }

    /**
     * CANCEL SUBSCRIPTION + DELETE ROUTER USER
     */
    public function cancel(Subscription $sub, MikroTikService $mikrotik)
    {
        if ($sub->router_username) {
            $mikrotik->deleteUser($sub->router_username);
        }

        $sub->update([
            'status'     => 'cancelled',
            'auto_renew' => false,
        ]);

        return back()->with('toast', [
            'type' => 'error',
            'message' => 'Subscription waa la joojiyay (Router user removed)',
        ]);
    }
}
