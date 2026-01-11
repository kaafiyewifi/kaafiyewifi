<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SubscriptionPlan;
use App\Models\CustomerSubscription;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CustomerSubscriptionController extends Controller
{
    public function store(Request $request, Customer $customer)
    {
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        $days = (int)$request->duration;

        $start = now();
        $expire = now()->addDays($days);

        CustomerSubscription::create([
            'customer_id'=>$customer->id,
            'subscription_plan_id'=>$plan->id,
            'price'=>$plan->price * $days,
            'duration_days'=>$days,
            'starts_at'=>$start,
            'expires_at'=>$expire,
            'status'=>'active'
        ]);

        return back()->with('success','Subscription added');
    }

    public function extend(CustomerSubscription $sub, Request $request)
    {
        $days = (int)$request->days;
        $sub->expires_at = $sub->expires_at->addDays($days);
        $sub->duration_days += $days;
        $sub->save();

        return back();
    }

    public function pause(CustomerSubscription $sub){
        $sub->status = 'paused';
        $sub->save();
        return back();
    }

    public function resume(CustomerSubscription $sub){
        $sub->status = 'active';
        $sub->save();
        return back();
    }

    public function cancel(CustomerSubscription $sub){
        $sub->status = 'cancelled';
        $sub->expires_at = now();
        $sub->save();
        return back();
    }
}



