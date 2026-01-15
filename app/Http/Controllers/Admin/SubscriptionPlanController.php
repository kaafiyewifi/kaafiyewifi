<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
   public function index()
{
    $plans = SubscriptionPlan::latest()->paginate(10);
    return view('admin.subscription-plans.index', compact('plans'));
}


    public function create()
    {
        return view('admin.subscription-plans.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        // ✅ ensure boolean
        $data['status'] = (bool) $data['status'];

        SubscriptionPlan::create($data);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Plan created successfully',
            ]);
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        return view('admin.subscription-plans.edit', [
            'plan' => $subscriptionPlan,
        ]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $data = $this->validateData($request);

        // ✅ ensure boolean
        $data['status'] = (bool) $data['status'];

        $subscriptionPlan->update($data);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Plan updated successfully',
            ]);
    }

    public function toggleStatus(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->update([
            'status' => ! $subscriptionPlan->status
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => $subscriptionPlan->status
                ? 'Plan waa la hawlgeliyay'
                : 'Plan waa la damiyay',
        ]);
    }

    /**
     * VALIDATION
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'           => 'required|string|max:100',
            'price'          => 'required|numeric|min:0',

            'download_speed' => 'nullable|integer|min:0',
            'download_unit'  => 'required|string|max:10',

            'upload_speed'   => 'nullable|integer|min:0',
            'upload_unit'    => 'required|string|max:10',

            'data_type'      => 'required|in:limited,unlimited',
            'data_limit'     => 'nullable|integer|min:0',
            'data_unit'      => 'required|string|max:10',

            'devices'        => 'required|integer|min:1',

            // ⚠️ important
            'status'         => 'required|in:0,1',
        ]);
    }
}
