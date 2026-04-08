<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : $today->copy()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : $today->copy()->endOfDay();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $locationIds = $this->getScopedLocationIds();

        $salesSummary = $this->getSalesSummary($today, $startDate, $endDate, $locationIds);
        $customerSummary = $this->getCustomerSummary($locationIds);
        $subscriptionSummary = $this->getSubscriptionSummary($locationIds);
        $salesByLocation = $this->getSalesByLocation($startDate, $endDate, $locationIds);
        $salesByAdmin = $this->getSalesByAdmin($startDate, $endDate, $locationIds);
        $salesChart = $this->getSalesChart($startDate, $endDate, $locationIds);

        return view('admin.reports.index', [
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'salesSummary' => $salesSummary,
            'customerSummary' => $customerSummary,
            'subscriptionSummary' => $subscriptionSummary,
            'salesByLocation' => $salesByLocation,
            'salesByAdmin' => $salesByAdmin,
            'salesChart' => $salesChart,
            'locations' => $this->getAllowedLocations(),
        ]);
    }

    private function getSalesSummary(Carbon $today, Carbon $startDate, Carbon $endDate, Collection $locationIds): array
    {
        $todaySales = $this->subscriptionRevenueQuery($locationIds)
            ->whereDate('customer_subscriptions.created_at', $today->toDateString())
            ->sum('customer_subscriptions.calculated_price');

        $monthSales = $this->subscriptionRevenueQuery($locationIds)
            ->whereYear('customer_subscriptions.created_at', $today->year)
            ->whereMonth('customer_subscriptions.created_at', $today->month)
            ->sum('customer_subscriptions.calculated_price');

        $rangeSales = $this->subscriptionRevenueQuery($locationIds)
            ->whereBetween('customer_subscriptions.created_at', [$startDate, $endDate])
            ->sum('customer_subscriptions.calculated_price');

        return [
            'today_sales' => (float) $todaySales,
            'month_sales' => (float) $monthSales,
            'range_sales' => (float) $rangeSales,
        ];
    }

    private function getCustomerSummary(Collection $locationIds): array
    {
        if (!Schema::hasTable('customers')) {
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'suspended' => 0,
                'hotspot' => 0,
                'pppoe' => 0,
            ];
        }

        $base = DB::table('customers');
        if (!$this->isSuperAdmin()) {
            $base->whereIn('location_id', $locationIds);
        }

        $rows = (clone $base)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended,
                SUM(CASE WHEN type = 'hotspot' THEN 1 ELSE 0 END) as hotspot,
                SUM(CASE WHEN type = 'pppoe' THEN 1 ELSE 0 END) as pppoe
            ")
            ->first();

        return [
            'total' => (int) ($rows->total ?? 0),
            'active' => (int) ($rows->active ?? 0),
            'inactive' => (int) ($rows->inactive ?? 0),
            'suspended' => (int) ($rows->suspended ?? 0),
            'hotspot' => (int) ($rows->hotspot ?? 0),
            'pppoe' => (int) ($rows->pppoe ?? 0),
        ];
    }

    private function getSubscriptionSummary(Collection $locationIds): array
    {
        if (!Schema::hasTable('customer_subscriptions') || !Schema::hasTable('customers')) {
            return [
                'total' => 0,
                'active' => 0,
                'expired' => 0,
                'paused' => 0,
                'cancelled' => 0,
            ];
        }

        $query = DB::table('customer_subscriptions')
            ->join('customers', 'customers.id', '=', 'customer_subscriptions.customer_id');

        if (!$this->isSuperAdmin()) {
            $query->whereIn('customers.location_id', $locationIds);
        }

        $rows = (clone $query)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN customer_subscriptions.status = 'active' AND (customer_subscriptions.expires_at IS NULL OR customer_subscriptions.expires_at >= NOW()) THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN customer_subscriptions.expires_at IS NOT NULL AND customer_subscriptions.expires_at < NOW() THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN customer_subscriptions.status = 'paused' THEN 1 ELSE 0 END) as paused,
                SUM(CASE WHEN customer_subscriptions.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            ")
            ->first();

        return [
            'total' => (int) ($rows->total ?? 0),
            'active' => (int) ($rows->active ?? 0),
            'expired' => (int) ($rows->expired ?? 0),
            'paused' => (int) ($rows->paused ?? 0),
            'cancelled' => (int) ($rows->cancelled ?? 0),
        ];
    }

    private function getSalesByLocation(Carbon $startDate, Carbon $endDate, Collection $locationIds): Collection
    {
        if (!Schema::hasTable('customer_subscriptions') || !Schema::hasTable('customers') || !Schema::hasTable('locations')) {
            return collect();
        }

        $query = DB::table('customer_subscriptions')
            ->join('customers', 'customers.id', '=', 'customer_subscriptions.customer_id')
            ->leftJoin('locations', 'locations.id', '=', 'customers.location_id')
            ->whereBetween('customer_subscriptions.created_at', [$startDate, $endDate])
            ->selectRaw('
                customers.location_id,
                COALESCE(locations.name, "No Location") as location_name,
                COUNT(customer_subscriptions.id) as total_subscriptions,
                COALESCE(SUM(customer_subscriptions.calculated_price), 0) as total_sales
            ')
            ->groupBy('customers.location_id', 'locations.name')
            ->orderByDesc('total_sales');

        if (!$this->isSuperAdmin()) {
            $query->whereIn('customers.location_id', $locationIds);
        }

        return $query->get();
    }

    private function getSalesByAdmin(Carbon $startDate, Carbon $endDate, Collection $locationIds): Collection
    {
        if (!Schema::hasTable('subscriptions') || !Schema::hasColumn('subscriptions', 'created_by')) {
            return collect();
        }

        $query = DB::table('customer_subscriptions')
            ->join('subscriptions', 'subscriptions.id', '=', 'customer_subscriptions.subscription_id')
            ->join('customers', 'customers.id', '=', 'customer_subscriptions.customer_id')
            ->leftJoin('users', 'users.id', '=', 'subscriptions.created_by')
            ->whereBetween('customer_subscriptions.created_at', [$startDate, $endDate])
            ->selectRaw('
                subscriptions.created_by,
                COALESCE(users.name, "System") as admin_name,
                COUNT(customer_subscriptions.id) as total_subscriptions,
                COALESCE(SUM(customer_subscriptions.calculated_price), 0) as total_sales
            ')
            ->groupBy('subscriptions.created_by', 'users.name')
            ->orderByDesc('total_sales');

        if (!$this->isSuperAdmin()) {
            $query->whereIn('customers.location_id', $locationIds)
                ->where('subscriptions.created_by', auth()->id());
        }

        return $query->get();
    }

    private function getSalesChart(Carbon $startDate, Carbon $endDate, Collection $locationIds): array
    {
        if (!Schema::hasTable('customer_subscriptions') || !Schema::hasTable('customers')) {
            return [
                'labels' => [],
                'values' => [],
            ];
        }

        $query = DB::table('customer_subscriptions')
            ->join('customers', 'customers.id', '=', 'customer_subscriptions.customer_id')
            ->whereBetween('customer_subscriptions.created_at', [$startDate, $endDate])
            ->selectRaw('DATE(customer_subscriptions.created_at) as report_date')
            ->selectRaw('COALESCE(SUM(customer_subscriptions.calculated_price), 0) as total_sales')
            ->groupBy('report_date')
            ->orderBy('report_date');

        if (!$this->isSuperAdmin()) {
            $query->whereIn('customers.location_id', $locationIds);
        }

        $rows = $query->get();

        return [
            'labels' => $rows->pluck('report_date')->values()->all(),
            'values' => $rows->pluck('total_sales')->map(fn ($v) => (float) $v)->values()->all(),
        ];
    }

    private function subscriptionRevenueQuery(Collection $locationIds)
    {
        $query = DB::table('customer_subscriptions')
            ->join('customers', 'customers.id', '=', 'customer_subscriptions.customer_id');

        if (!$this->isSuperAdmin()) {
            $query->whereIn('customers.location_id', $locationIds);
        }

        return $query;
    }

    private function isSuperAdmin(): bool
    {
        return auth()->check()
            && method_exists(auth()->user(), 'hasRole')
            && auth()->user()->hasRole('super_admin');
    }

    private function getAssignedLocationIds(): Collection
    {
        $user = auth()->user();

        if (!$user || !method_exists($user, 'locations')) {
            return collect();
        }

        return $user->locations()->pluck('locations.id');
    }

    private function getScopedLocationIds(): Collection
    {
        if ($this->isSuperAdmin()) {
            return Location::query()->pluck('id');
        }

        return $this->getAssignedLocationIds();
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
}