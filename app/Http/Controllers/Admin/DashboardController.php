<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_customers' => $this->getTotalCustomers(),
            'total_hotspot_customers' => $this->getTotalHotspotCustomers(),
            'total_pppoe_customers' => $this->getTotalPppoeCustomers(),
            'online_users' => $this->getOnlineUsers(),
            'today_sales' => $this->getTodaySales(),
            'monthly_sales' => $this->getMonthlySales(),
        ];

        $devices = $this->getConnectedDevices();
        $incomeChart = $this->getIncomeChart();
        $routerResources = $this->getRouterResources();

        return view('admin.home', compact('stats', 'devices', 'incomeChart', 'routerResources'));
    }

    private function getTotalCustomers(): int
    {
        if (!Schema::hasTable('customers')) {
            return 0;
        }

        $query = DB::table('customers');

        if (!$this->isSuperAdmin()) {
            $query->whereIn('location_id', $this->getAssignedLocationIds());
        }

        return (int) $query->count();
    }

    private function getTotalHotspotCustomers(): int
    {
        if (!Schema::hasTable('customers') || !Schema::hasColumn('customers', 'type')) {
            return 0;
        }

        $query = DB::table('customers')->where('type', 'hotspot');

        if (!$this->isSuperAdmin()) {
            $query->whereIn('location_id', $this->getAssignedLocationIds());
        }

        return (int) $query->count();
    }

    private function getTotalPppoeCustomers(): int
    {
        if (!Schema::hasTable('customers') || !Schema::hasColumn('customers', 'type')) {
            return 0;
        }

        $query = DB::table('customers')->where('type', 'pppoe');

        if (!$this->isSuperAdmin()) {
            $query->whereIn('location_id', $this->getAssignedLocationIds());
        }

        return (int) $query->count();
    }

    private function getOnlineUsers(): int
    {
        if (!$this->radiusTableExists('radacct')) {
            return 0;
        }

        $query = DB::connection('radius')
            ->table('radacct')
            ->whereNull('acctstoptime');

        if (!$this->isSuperAdmin()) {
            $allowedUsernames = $this->getAssignedCustomerUsernames();

            if ($allowedUsernames->isEmpty()) {
                return 0;
            }

            $query->whereIn('username', $allowedUsernames);
        }

        return (int) $query->count();
    }

    private function getTodaySales(): float
    {
        if (!Schema::hasTable('customer_subscriptions') || !Schema::hasTable('customers')) {
            return 0;
        }

        $query = DB::table('customer_subscriptions')
            ->join('customers', 'customer_subscriptions.customer_id', '=', 'customers.id')
            ->whereDate('customer_subscriptions.created_at', Carbon::today());

        if (!$this->isSuperAdmin()) {
            $query->whereIn('customers.location_id', $this->getAssignedLocationIds());
        }

        return (float) $query->sum('customer_subscriptions.calculated_price');
    }

    private function getMonthlySales(): float
    {
        if (!Schema::hasTable('customer_subscriptions') || !Schema::hasTable('customers')) {
            return 0;
        }

        $query = DB::table('customer_subscriptions')
            ->join('customers', 'customer_subscriptions.customer_id', '=', 'customers.id')
            ->whereYear('customer_subscriptions.created_at', Carbon::now()->year)
            ->whereMonth('customer_subscriptions.created_at', Carbon::now()->month);

        if (!$this->isSuperAdmin()) {
            $query->whereIn('customers.location_id', $this->getAssignedLocationIds());
        }

        return (float) $query->sum('customer_subscriptions.calculated_price');
    }

    private function getConnectedDevices(): array
    {
        if (!$this->radiusTableExists('radacct')) {
            return [];
        }

        $query = DB::connection('radius')
            ->table('radacct')
            ->select([
                'username',
                'framedipaddress',
                'callingstationid',
                'acctstarttime',
                'acctsessiontime',
                'acctinputoctets',
                'acctoutputoctets',
            ])
            ->whereNotNull('callingstationid')
            ->whereNull('acctstoptime')
            ->orderByDesc('acctstarttime');

        if (!$this->isSuperAdmin()) {
            $allowedUsernames = $this->getAssignedCustomerUsernames();

            if ($allowedUsernames->isEmpty()) {
                return [];
            }

            $query->whereIn('username', $allowedUsernames);
        }

        return $query
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $usage = (int) ($row->acctinputoctets ?? 0) + (int) ($row->acctoutputoctets ?? 0);

                return [
                    'ip' => $row->framedipaddress ?? '-',
                    'mac' => $row->callingstationid ?? '-',
                    'uptime' => $this->formatSeconds((int) ($row->acctsessiontime ?? 0)),
                    'usage' => $this->formatBytes($usage),
                    'last_connected' => $row->acctstarttime
                        ? Carbon::parse($row->acctstarttime)->format('d M Y H:i')
                        : '-',
                ];
            })
            ->toArray();
    }

    private function getIncomeChart(): array
    {
        $labels = [];
        $today = [];
        $monthly = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);

            $labels[] = $day->format('D');

            if (!Schema::hasTable('customer_subscriptions') || !Schema::hasTable('customers')) {
                $today[] = 0;
                $monthly[] = 0;
                continue;
            }

            $todayQuery = DB::table('customer_subscriptions')
                ->join('customers', 'customer_subscriptions.customer_id', '=', 'customers.id')
                ->whereDate('customer_subscriptions.created_at', $day->toDateString());

            $monthlyQuery = DB::table('customer_subscriptions')
                ->join('customers', 'customer_subscriptions.customer_id', '=', 'customers.id')
                ->whereYear('customer_subscriptions.created_at', $day->year)
                ->whereMonth('customer_subscriptions.created_at', $day->month)
                ->whereDate('customer_subscriptions.created_at', '<=', $day->toDateString());

            if (!$this->isSuperAdmin()) {
                $locationIds = $this->getAssignedLocationIds();

                $todayQuery->whereIn('customers.location_id', $locationIds);
                $monthlyQuery->whereIn('customers.location_id', $locationIds);
            }

            $today[] = (float) $todayQuery->sum('customer_subscriptions.calculated_price');
            $monthly[] = (float) $monthlyQuery->sum('customer_subscriptions.calculated_price');
        }

        return [
            'labels' => $labels,
            'today' => $today,
            'monthly' => $monthly,
        ];
    }

    private function getRouterResources(): array
    {
        $default = [
            'labels' => ['R1'],
            'cpu' => [0],
            'ram' => [0],
            'storage' => [0],
        ];

        if (!Schema::hasTable('router_metrics') || !Schema::hasTable('routers')) {
            return $default;
        }

        $query = DB::table('router_metrics')
            ->join('routers', 'routers.id', '=', 'router_metrics.router_id')
            ->select([
                'router_metrics.router_id',
                'router_metrics.cpu_load',
                'router_metrics.total_memory',
                'router_metrics.free_memory',
                'router_metrics.total_hdd_space',
                'router_metrics.free_hdd_space',
                'router_metrics.collected_at',
                'routers.name',
            ])
            ->orderByDesc('router_metrics.collected_at');

        if (!$this->isSuperAdmin()) {
            $query->whereIn('routers.location_id', $this->getAssignedLocationIds());
        }

        $rows = $query
            ->limit(100)
            ->get()
            ->groupBy('router_id')
            ->map(fn ($items) => $items->first())
            ->values();

        if ($rows->isEmpty()) {
            return $default;
        }

        $labels = [];
        $cpu = [];
        $ram = [];
        $storage = [];

        foreach ($rows as $index => $row) {
            if (count($labels) >= 5) {
                break;
            }

            $labels[] = $row->name ?: 'R' . ($index + 1);
            $cpu[] = round((float) ($row->cpu_load ?? 0), 2);

            $ram[] = !empty($row->total_memory)
                ? round((($row->total_memory - ($row->free_memory ?? 0)) / $row->total_memory) * 100, 2)
                : 0;

            $storage[] = !empty($row->total_hdd_space)
                ? round((($row->total_hdd_space - ($row->free_hdd_space ?? 0)) / $row->total_hdd_space) * 100, 2)
                : 0;
        }

        return [
            'labels' => $labels,
            'cpu' => $cpu,
            'ram' => $ram,
            'storage' => $storage,
        ];
    }

    private function getAssignedCustomerUsernames(): Collection
    {
        if (!Schema::hasTable('customers') || !Schema::hasColumn('customers', 'username')) {
            return collect();
        }

        $locationIds = $this->getAssignedLocationIds();

        if ($locationIds->isEmpty()) {
            return collect();
        }

        return DB::table('customers')
            ->whereIn('location_id', $locationIds)
            ->whereNotNull('username')
            ->pluck('username')
            ->filter()
            ->values();
    }

    private function radiusTableExists(string $table): bool
    {
        try {
            return Schema::connection('radius')->hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function formatSeconds(int $seconds): string
    {
        if ($seconds <= 0) {
            return '-';
        }

        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);

        return "{$h}h {$m}m";
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i];
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
}