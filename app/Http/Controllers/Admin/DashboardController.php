<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
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
        if (!Schema::hasTable('customers')) return 0;

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

        return (int) DB::connection('radius')
            ->table('radacct')
            ->whereNull('acctstoptime')
            ->count();
    }

    private function getTodaySales(): float
    {
        if (!Schema::hasTable('customer_subscriptions')) {
            return 0;
        }

        return (float) DB::table('customer_subscriptions')
            ->where('status', 'active')
            ->whereDate('created_at', Carbon::today())
            ->sum('calculated_price');
    }

    private function getMonthlySales(): float
    {
        if (!Schema::hasTable('customer_subscriptions')) {
            return 0;
        }

        return (float) DB::table('customer_subscriptions')
            ->where('status', 'active')
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('calculated_price');
    }

    private function getConnectedDevices(): array
    {
        if (!$this->radiusTableExists('radacct')) {
            return [];
        }

        return DB::connection('radius')
            ->table('radacct')
            ->select([
                'framedipaddress',
                'callingstationid',
                'acctstarttime',
                'acctsessiontime',
                'acctinputoctets',
                'acctoutputoctets',
            ])
            ->whereNotNull('callingstationid')
            ->orderByDesc('acctstarttime')
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
            })->toArray();
    }

    private function getIncomeChart(): array
    {
        $labels = [];
        $today = [];
        $monthly = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);

            $labels[] = $day->format('D');

            $today[] = (float) DB::table('customer_subscriptions')
                ->where('status', 'active')
                ->whereDate('created_at', $day->toDateString())
                ->sum('calculated_price');

            $monthly[] = (float) DB::table('customer_subscriptions')
                ->where('status', 'active')
                ->whereYear('created_at', $day->year)
                ->whereMonth('created_at', $day->month)
                ->whereDate('created_at', '<=', $day->toDateString())
                ->sum('calculated_price');
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

        if (!Schema::hasTable('router_metrics')) {
            return $default;
        }

        $rows = DB::table('router_metrics')
            ->orderByDesc('collected_at')
            ->limit(100)
            ->get()
            ->groupBy('router_id')
            ->map(fn ($items) => $items->first())
            ->values();

        if ($rows->isEmpty()) return $default;

        $labels = [];
        $cpu = [];
        $ram = [];
        $storage = [];

        foreach ($rows as $index => $row) {
            if (count($labels) >= 5) break;

            $labels[] = 'R' . ($index + 1);
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
        if ($seconds <= 0) return '-';

        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);

        return "{$h}h {$m}m";
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i];
    }

    private function isSuperAdmin(): bool
    {
        return auth()->check() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('super_admin');
    }

    private function getAssignedLocationIds()
    {
        $user = auth()->user();

        if (!$user || !method_exists($user, 'locations')) {
            return collect();
        }

        return $user->locations()->pluck('locations.id');
    }
}