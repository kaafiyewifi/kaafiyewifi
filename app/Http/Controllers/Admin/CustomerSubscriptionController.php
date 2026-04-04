<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\CustomerSubscription;
use App\Services\Radius\RadiusUserService;
use App\Services\Radius\RadiusCoaService;
use App\Services\Radius\RadiusSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        try {
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

            $radius = app(RadiusUserService::class);

            $radius->setUserActive($customer->username, $customer->type);

            $radius->setUserSpeed(
                $customer->username,
                $plan->download_speed ?? 2,
                $plan->download_unit ?? 'Mbps',
                $plan->upload_speed ?? 2,
                $plan->upload_unit ?? 'Mbps',
                $customer->type
            );

            app(RadiusSessionService::class)->enforceDeviceLimit(
                $customer->username,
                (int) ($customer->device_limit ?? 1)
            );

            $activeIp = $this->getActiveUserIp($customer->username);

            if ($activeIp) {
                app(RadiusCoaService::class)->disconnect($customer->username, $activeIp);
            }

            return redirect()
                ->route('admin.customers.show', $customer)
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Subscription waa lagu daray',
                ]);
        } catch (\Throwable $e) {
            Log::error('CUSTOMER SUBSCRIPTION STORE FAILED', [
                'customer_id' => $customer->id,
                'username' => $customer->username,
                'plan_id' => $data['plan_id'],
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.customers.show', $customer)
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Subscription lama darin: ' . $e->getMessage(),
                ]);
        }
    }

    // (ISLA UPDATE ayaa lagu sameeyay functions kale sida extend, resume iwm — dhammaan waxay gudbinayaan $customer->type)
}