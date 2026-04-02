<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::query();

        if ($request->filled('q')) {
            $q = trim((string) $request->q);

            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('id', $q);
            });
        }

        $subscriptions = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    public function create()
    {
        return view('admin.subscriptions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'price'          => ['required', 'numeric', 'min:0'],
            'base_days'      => ['required', 'integer', 'min:1'],
            'upload_speed'   => ['nullable', 'integer', 'min:0'],
            'upload_unit'    => ['required', Rule::in(['Kbps', 'Mbps'])],
            'download_speed' => ['nullable', 'integer', 'min:0'],
            'download_unit'  => ['required', Rule::in(['Kbps', 'Mbps'])],
            'status'         => ['required', Rule::in(['active', 'inactive'])],
            'description'    => ['nullable', 'string'],
        ]);

        Subscription::create($data);

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Subscription plan waa la sameeyay',
            ]);
    }

    public function show(Subscription $subscription)
    {
        return view('admin.subscriptions.show', [
            'subscription' => $subscription,
        ]);
    }

    public function edit(Subscription $subscription)
    {
        return view('admin.subscriptions.edit', [
            'subscription' => $subscription,
        ]);
    }

    public function update(Request $request, Subscription $subscription)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'price'          => ['required', 'numeric', 'min:0'],
            'base_days'      => ['required', 'integer', 'min:1'],
            'upload_speed'   => ['nullable', 'integer', 'min:0'],
            'upload_unit'    => ['required', Rule::in(['Kbps', 'Mbps'])],
            'download_speed' => ['nullable', 'integer', 'min:0'],
            'download_unit'  => ['required', Rule::in(['Kbps', 'Mbps'])],
            'status'         => ['required', Rule::in(['active', 'inactive'])],
            'description'    => ['nullable', 'string'],
        ]);

        $subscription->update($data);

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Subscription plan waa la update gareeyay',
            ]);
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Subscription plan waa la tirtiray',
            ]);
    }
}