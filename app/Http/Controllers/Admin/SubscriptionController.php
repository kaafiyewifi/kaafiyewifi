<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::query();

        if ($this->hasLocationColumn()) {
            if (!$this->isSuperAdmin()) {
                $query->whereIn('location_id', $this->getAssignedLocationIds());

                if ($this->hasCreatedByColumn()) {
                    $query->where('created_by', auth()->id());
                }
            }

            $query->with('location');
        }

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
            'locations' => $this->hasLocationColumn() ? $this->getAllowedLocations() : collect(),
        ]);
    }

    public function create()
    {
        return view('admin.subscriptions.create', [
            'locations' => $this->hasLocationColumn() ? $this->getAllowedLocations() : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $locationRules = $this->hasLocationColumn()
            ? [
                'location_id' => [
                    Rule::requiredIf(!$this->isSuperAdmin()),
                    'nullable',
                    'exists:locations,id',
                    Rule::when(
                        !$this->isSuperAdmin(),
                        ['in:' . ($this->getAssignedLocationIds()->count() ? $this->getAssignedLocationIds()->implode(',') : '0')]
                    ),
                ],
            ]
            : [];

        $data = $request->validate(array_merge([
            'name'           => ['required', 'string', 'max:255'],
            'price'          => ['required', 'numeric', 'min:0'],
            'base_days'      => ['required', 'integer', 'min:1'],
            'upload_speed'   => ['nullable', 'integer', 'min:0'],
            'upload_unit'    => ['required', Rule::in(['Kbps', 'Mbps'])],
            'download_speed' => ['nullable', 'integer', 'min:0'],
            'download_unit'  => ['required', Rule::in(['Kbps', 'Mbps'])],
            'status'         => ['required', Rule::in(['active', 'inactive'])],
            'description'    => ['nullable', 'string'],
        ], $locationRules));

        if ($this->hasCreatedByColumn()) {
            $data['created_by'] = auth()->id();
        }

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
        $this->authorizeSubscriptionAccess($subscription);

        if ($this->hasLocationColumn()) {
            $subscription->load('location');
        }

        return view('admin.subscriptions.show', [
            'subscription' => $subscription,
        ]);
    }

    public function edit(Subscription $subscription)
    {
        $this->authorizeSubscriptionAccess($subscription);

        return view('admin.subscriptions.edit', [
            'subscription' => $subscription,
            'locations' => $this->hasLocationColumn() ? $this->getAllowedLocations() : collect(),
        ]);
    }

    public function update(Request $request, Subscription $subscription)
    {
        $this->authorizeSubscriptionAccess($subscription);

        $locationRules = $this->hasLocationColumn()
            ? [
                'location_id' => [
                    Rule::requiredIf(!$this->isSuperAdmin()),
                    'nullable',
                    'exists:locations,id',
                    Rule::when(
                        !$this->isSuperAdmin(),
                        ['in:' . ($this->getAssignedLocationIds()->count() ? $this->getAssignedLocationIds()->implode(',') : '0')]
                    ),
                ],
            ]
            : [];

        $data = $request->validate(array_merge([
            'name'           => ['required', 'string', 'max:255'],
            'price'          => ['required', 'numeric', 'min:0'],
            'base_days'      => ['required', 'integer', 'min:1'],
            'upload_speed'   => ['nullable', 'integer', 'min:0'],
            'upload_unit'    => ['required', Rule::in(['Kbps', 'Mbps'])],
            'download_speed' => ['nullable', 'integer', 'min:0'],
            'download_unit'  => ['required', Rule::in(['Kbps', 'Mbps'])],
            'status'         => ['required', Rule::in(['active', 'inactive'])],
            'description'    => ['nullable', 'string'],
        ], $locationRules));

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
        $this->authorizeSubscriptionAccess($subscription);

        $subscription->delete();

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Subscription plan waa la tirtiray',
            ]);
    }

    private function isSuperAdmin(): bool
    {
        return auth()->check()
            && method_exists(auth()->user(), 'hasRole')
            && auth()->user()->hasRole('super_admin');
    }

    private function getAssignedLocationIds()
    {
        $user = auth()->user();

        if (!$user || !method_exists($user, 'locations')) {
            return collect();
        }

        return $user->locations()->pluck('locations.id');
    }

    private function getAllowedLocations()
    {
        if ($this->isSuperAdmin()) {
            return Location::orderBy('name')->get();
        }

        return Location::whereIn('id', $this->getAssignedLocationIds())
            ->orderBy('name')
            ->get();
    }

    private function authorizeSubscriptionAccess(Subscription $subscription): void
    {
        if ($this->isSuperAdmin()) {
            return;
        }

        if ($this->hasLocationColumn()) {
            abort_unless(
                $this->getAssignedLocationIds()->contains($subscription->location_id),
                403,
                'Unauthorized access to this subscription.'
            );
        }

        if ($this->hasCreatedByColumn()) {
            abort_unless(
                (int) $subscription->created_by === (int) auth()->id(),
                403,
                'Unauthorized access to this subscription.'
            );
        }
    }

    private function hasLocationColumn(): bool
    {
        return Schema::hasColumn('subscriptions', 'location_id');
    }

    private function hasCreatedByColumn(): bool
    {
        return Schema::hasColumn('subscriptions', 'created_by');
    }
}