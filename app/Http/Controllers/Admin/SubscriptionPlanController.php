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
        $plans = SubscriptionPlan::latest()->paginate(10);

        return view('admin.subscription-plans.index', compact('plans'));
    }

    /* =========================
     | CREATE FORM
     |=========================*/
    public function create()
    {
        return view('admin.subscription-plans.create');
    }

    /* =========================
     | STORE
     |=========================*/
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'price'           => 'required|numeric|min:0',

            'download_speed'  => 'nullable|integer|min:1',
            'download_unit'   => 'nullable|in:Mbps,Kbps,Gbps',

            'upload_speed'    => 'nullable|integer|min:1',
            'upload_unit'     => 'nullable|in:Mbps,Kbps,Gbps',

            'data_type'       => 'required|in:limited,unlimited',
            'data_limit'      => 'nullable|integer|min:1',
            'data_unit'       => 'nullable|in:MB,GB',

            'devices'         => 'required|integer|min:1',
            'status'          => 'required|boolean',
        ]);

        SubscriptionPlan::create($data);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Subscription Plan si guul leh ayaa loo sameeyay'
            ]);
    }
}
