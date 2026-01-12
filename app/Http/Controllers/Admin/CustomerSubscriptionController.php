<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CustomerSubscriptionController extends Controller
{
    /* =========================
     * STORE SUBSCRIPTION (POPUP)
     * =======================*/
    public function store(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'plan_id'   => 'required|exists:subscription_plans,id',
            'days'      => 'required|integer|min:1',
            'starts_at' => 'required|date',
        ]);

        $plan = SubscriptionPlan::findOrFail($data['plan_id']);

        $startsAt  = Carbon::parse($data['starts_at']);
        $expiresAt = (clone $startsAt)->addDays($data['days']);

        Subscription::create([
            'customer_id' => $customer->id,
            'plan_id'     => $plan->id,
            'price'       => $plan->price,
            'starts_at'   => $startsAt,
            'expires_at'  => $expiresAt,
            'status'      => 'active',
            'auto_renew'  => false,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Subscription si guul leh ayaa loogu daray customer-ka',
        ]);
    }

    /* =========================
     * EXTEND SUBSCRIPTION
     * =======================*/
    public function extend(Request $request, Subscription $sub)
    {
        $request->validate([
            'days' => 'required|integer|min:1',
        ]);

        $sub->update([
            'expires_at' => $sub->expires_at->copy()->addDays($request->days),
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Subscription waa la kordhiyay',
        ]);
    }

    /* =========================
     * PAUSE SUBSCRIPTION
     * =======================*/
    public function pause(Subscription $sub)
    {
        $sub->update([
            'status' => 'paused',
        ]);

        return back()->with('toast', [
            'type' => 'warning',
            'message' => 'Subscription waa la hakiyay',
        ]);
    }

    /* =========================
     * RESUME SUBSCRIPTION
     * =======================*/
    public function resume(Subscription $sub)
    {
        $sub->update([
            'status' => 'active',
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Subscription waa la sii waday',
        ]);
    }

    /* =========================
     * CANCEL SUBSCRIPTION
     * =======================*/
    public function cancel(Subscription $sub)
    {
        $sub->update([
            'status' => 'cancelled',
            'auto_renew' => false,
        ]);

        return back()->with('toast', [
            'type' => 'error',
            'message' => 'Subscription waa la joojiyay',
        ]);
    }
}
