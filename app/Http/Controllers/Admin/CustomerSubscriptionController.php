<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\CustomerSubscription;
use App\Services\Radius\RadiusUserService;
use App\Services\Radius\RadiusCoaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerSubscriptionController extends Controller
{
    private function getActiveUserIp(string $username): ?string
    {
        return DB::connection('radius')
            ->table('radacct')
            ->where('username', $username)
            ->whereNull('acctstoptime')
            ->orderByDesc('radacctid')
            ->value('framedipaddress');
    }

    private function createInvoice(int $customerId, float $amount, string $status = 'paid'): int
    {
        $data = [
            'customer_id' => $customerId,
            'amount' => $amount,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('invoices', 'paid_at')) {
            $data['paid_at'] = now();
        }

        return (int) DB::table('invoices')->insertGetId($data);
    }

    private function customerSubscriptionTableHas(string $column): bool
    {
        return Schema::hasTable('customer_subscriptions')
            && Schema::hasColumn('customer_subscriptions', $column);
    }

    private function buildSubscriptionPayload(
        int $customerId,
        int $planId,
        int $days,
        float $price,
        $expires,
        ?int $invoiceId = null
    ): array {
        $payload = [
            'customer_id' => $customerId,
            'subscription_id' => $planId,
            'selected_days' => $days,
            'calculated_price' => $price,
            'starts_at' => now(),
            'expires_at' => $expires,
            'status' => 'active',
        ];

        if ($invoiceId !== null && $this->customerSubscriptionTableHas('invoice_id')) {
            $payload['invoice_id'] = $invoiceId;
        }

        if ($this->customerSubscriptionTableHas('paid_at')) {
            $payload['paid_at'] = now();
        }

        return $payload;
    }

    private function buildSubscriptionUpdatePayload(array $payload, ?int $invoiceId = null): array
    {
        if ($invoiceId !== null && $this->customerSubscriptionTableHas('invoice_id')) {
            $payload['invoice_id'] = $invoiceId;
        }

        if ($this->customerSubscriptionTableHas('paid_at')) {
            $payload['paid_at'] = now();
        }

        return $payload;
    }

    public function create(Customer $customer)
    {
        $plans = Subscription::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.customers.subscribe', compact('customer', 'plans'));
    }

    public function store(Customer $customer, Request $request)
    {
        $data = $request->validate([
            'plan_id' => 'required|exists:subscriptions,id',
            'type'    => 'required|in:days,hours',
            'value'   => 'required|integer|min:1',
        ]);

        $hasActive = CustomerSubscription::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($hasActive) {
            return redirect()
                ->route('admin.customers.show', $customer)
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Customer-kan wuxuu hore u leeyahay active subscription',
                ]);
        }

        $plan = Subscription::findOrFail($data['plan_id']);
        $value = (int) $data['value'];

        if ($data['type'] === 'days') {
            $expires = now()->addDays($value);
            $price = (float) $plan->calculatePriceForDays($value);
            $days = $value;
        } else {
            $expires = now()->addHours($value);
            $price = (float) $plan->calculatePriceForHours($value);
            $days = max(1, (int) ceil($value / 24));
        }

        DB::transaction(function () use ($customer, $plan, $days, $price, $expires) {
            $invoiceId = $this->createInvoice($customer->id, $price, 'paid');

            CustomerSubscription::create(
                $this->buildSubscriptionPayload(
                    $customer->id,
                    $plan->id,
                    $days,
                    $price,
                    $expires,
                    $invoiceId
                )
            );
        });

        app(RadiusUserService::class)->setUserActive($customer->username);

        app(RadiusUserService::class)->setUserSpeed(
            $customer->username,
            $plan->download_speed ?? 2,
            $plan->download_unit ?? 'Mbps',
            $plan->upload_speed ?? 2,
            $plan->upload_unit ?? 'Mbps'
        );

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Subscription waa lagu daray',
            ]);
    }

    public function extend($subscription)
    {
        $subscription = CustomerSubscription::with(['customer', 'plan'])
            ->findOrFail($subscription);

        if ($subscription->status === 'cancelled') {
            return redirect()
                ->route('admin.customers.show', $subscription->customer_id)
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Subscription cancelled lama extend gareyn karo',
                ]);
        }

        return view('admin.subscriptions.extend', [
            'subscription' => $subscription,
            'customer'     => $subscription->customer,
            'plan'         => $subscription->plan,
        ]);
    }

    public function extendPost(Request $request, $subscription)
    {
        $subscription = CustomerSubscription::with(['customer', 'plan'])
            ->findOrFail($subscription);

        if ($subscription->status === 'cancelled') {
            return redirect()
                ->route('admin.customers.show', $subscription->customer_id)
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Subscription cancelled lama extend gareyn karo',
                ]);
        }

        $data = $request->validate([
            'type'  => 'required|in:days,hours',
            'value' => 'required|integer|min:1',
        ]);

        $value = (int) $data['value'];

        if ($data['type'] === 'days') {
            $price = (float) $subscription->plan->calculatePriceForDays($value);
            $newExpiry = $subscription->expires_at && $subscription->expires_at->isFuture()
                ? $subscription->expires_at->copy()->addDays($value)
                : now()->addDays($value);

            $selectedDays = ($subscription->selected_days ?? 0) + $value;
        } else {
            $price = (float) $subscription->plan->calculatePriceForHours($value);
            $newExpiry = $subscription->expires_at && $subscription->expires_at->isFuture()
                ? $subscription->expires_at->copy()->addHours($value)
                : now()->addHours($value);

            $selectedDays = ($subscription->selected_days ?? 0) + max(1, (int) ceil($value / 24));
        }

        DB::transaction(function () use ($subscription, $price, $newExpiry, $selectedDays) {
            $invoiceId = $this->createInvoice($subscription->customer_id, $price, 'paid');

            $subscription->update(
                $this->buildSubscriptionUpdatePayload([
                    'selected_days' => $selectedDays,
                    'calculated_price' => ($subscription->calculated_price ?? 0) + $price,
                    'expires_at' => $newExpiry,
                    'status' => 'active',
                ], $invoiceId)
            );
        });

        app(RadiusUserService::class)->setUserActive($subscription->customer->username);

        app(RadiusUserService::class)->setUserSpeed(
            $subscription->customer->username,
            $subscription->plan->download_speed ?? 2,
            $subscription->plan->download_unit ?? 'Mbps',
            $subscription->plan->upload_speed ?? 2,
            $subscription->plan->upload_unit ?? 'Mbps'
        );

        return redirect()
            ->route('admin.customers.show', $subscription->customer_id)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Subscription waa la kordhiyay',
            ]);
    }

    public function pause($subscription)
    {
        $sub = CustomerSubscription::with('customer')->findOrFail($subscription);

        if ($sub->status === 'active') {
            $sub->update([
                'status' => 'paused',
            ]);

            app(RadiusUserService::class)->setUserInactive($sub->customer->username);
            app(RadiusUserService::class)->clearUserSpeed($sub->customer->username);

            $ip = $this->getActiveUserIp($sub->customer->username);

            app(RadiusCoaService::class)->disconnect($sub->customer->username, $ip);
        }

        return redirect()
            ->route('admin.customers.show', $sub->customer_id)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Subscription waa la pause gareeyay',
            ]);
    }

    public function resume($subscription)
    {
        $subscription = CustomerSubscription::with(['customer', 'plan'])
            ->findOrFail($subscription);

        if ($subscription->status === 'cancelled') {
            return redirect()
                ->route('admin.customers.show', $subscription->customer_id)
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Subscription cancelled lama resume gareyn karo',
                ]);
        }

        $hasAnotherActiveSubscription = CustomerSubscription::where('customer_id', $subscription->customer_id)
            ->where('id', '!=', $subscription->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($hasAnotherActiveSubscription) {
            return redirect()
                ->route('admin.customers.show', $subscription->customer_id)
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Customer-kan wuxuu hore u leeyahay active subscription kale',
                ]);
        }

        if (in_array($subscription->status, ['paused', 'expired', 'inactive'], true)) {
            $subscription->update([
                'status' => 'active',
            ]);

            app(RadiusUserService::class)->setUserActive($subscription->customer->username);

            app(RadiusUserService::class)->setUserSpeed(
                $subscription->customer->username,
                $subscription->plan->download_speed ?? 2,
                $subscription->plan->download_unit ?? 'Mbps',
                $subscription->plan->upload_speed ?? 2,
                $subscription->plan->upload_unit ?? 'Mbps'
            );
        }

        return redirect()
            ->route('admin.customers.show', $subscription->customer_id)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Subscription waa la resume gareeyay',
            ]);
    }

    public function cancel($subscription)
    {
        $sub = CustomerSubscription::with('customer')->findOrFail($subscription);

        $sub->update([
            'status' => 'cancelled',
        ]);

        app(RadiusUserService::class)->setUserInactive($sub->customer->username);
        app(RadiusUserService::class)->clearUserSpeed($sub->customer->username);

        $ip = $this->getActiveUserIp($sub->customer->username);

        app(RadiusCoaService::class)->disconnect($sub->customer->username, $ip);

        return redirect()
            ->route('admin.customers.show', $sub->customer_id)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Subscription waa la cancel gareeyay',
            ]);
    }
}