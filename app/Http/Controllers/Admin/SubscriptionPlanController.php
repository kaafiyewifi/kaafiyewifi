<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /* =========================
     | LIST
     |=========================*/
    public function index()
    {
        $plans = SubscriptionPlan::latest()->get();

        return view('admin.subscription_plans.index', compact('plans'));
    }

    /* =========================
     | STORE
     |=========================*/
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'price'           => 'required|numeric|min:0',

            'download_speed'  => 'nullable|integer|min:0',
            'download_unit'   => 'nullable|string|max:10',

            'upload_speed'    => 'nullable|integer|min:0',
            'upload_unit'     => 'nullable|string|max:10',

            'data_type'       => 'required|in:limited,unlimited',
            'data_limit'      => 'nullable|integer|min:0',
            'data_unit'       => 'nullable|string|max:10',

            'devices'         => 'required|integer|min:1',
        ]);

        // haddii unlimited → data_limit null
        if ($data['data_type'] === 'unlimited') {
            $data['data_limit'] = null;
            $data['data_unit']  = null;
        }

        SubscriptionPlan::create($data);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('success','Subscription plan created successfully');
    }

    /* =========================
     | UPDATE
     |=========================*/
    public function update(Request $request, SubscriptionPlan $subscription_plan)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'price'           => 'required|numeric|min:0',

            'download_speed'  => 'nullable|integer|min:0',
            'download_unit'   => 'nullable|string|max:10',

            'upload_speed'    => 'nullable|integer|min:0',
            'upload_unit'     => 'nullable|string|max:10',

            'data_type'       => 'required|in:limited,unlimited',
            'data_limit'      => 'nullable|integer|min:0',
            'data_unit'       => 'nullable|string|max:10',

            'devices'         => 'required|integer|min:1',
        ]);

        if ($data['data_type'] === 'unlimited') {
            $data['data_limit'] = null;
            $data['data_unit']  = null;
        }

        $subscription_plan->update($data);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('success','Subscription plan updated successfully');
    }

    /* =========================
     | DELETE
     |=========================*/
    public function destroy(SubscriptionPlan $subscription_plan)
    {
        $subscription_plan->delete();

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('success','Subscription plan deleted successfully');
    }
}
