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
            'total_active_customers' => $this->getTotalActiveCustomers(),
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
        return Schema::hasTable('customers')
            ? (int) DB::table('customers')->count()
            : 0;
    }

    private function getTotalActiveCustomers(): int
    {
        if (!Schema::hasTable('customers') || !Schema::hasColumn('customers', 'status')) {
            return 0;
        }

        return (int) DB::table('customers')
            ->where('status', 'active')
            ->count();
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
            ->map(function ($items) {
                return $items->first();
            })
            ->values();

        if ($rows->isEmpty()) {
            return $default;
        }

        $routerNames = [];
        if (Schema::hasTable('routers')) {
            $routerNames = DB::table('routers')
                ->select('id', 'name', 'identity')
                ->get()
                ->keyBy('id')
                ->toArray();
        }

        $labels = [];
        $cpu = [];
        $ram = [];
        $storage = [];

        foreach ($rows as $index => $row) {
            if (count($labels) >= 5) {
                break;
            }

            $routerInfo = $routerNames[$row->router_id] ?? null;

            $labels[] = $routerInfo
                ? ($routerInfo->name ?: $routerInfo->identity ?: 'R' . ($index + 1))
                : 'R' . ($index + 1);

            $cpu[] = isset($row->cpu_load)
                ? round((float) $row->cpu_load, 2)
                : 0;

            if (!empty($row->total_memory) && (int) $row->total_memory > 0) {
                $ram[] = round(
                    (((int) $row->total_memory - (int) ($row->free_memory ?? 0)) / (int) $row->total_memory) * 100,
                    2
                );
            } else {
                $ram[] = 0;
            }

            if (!empty($row->total_hdd_space) && (int) $row->total_hdd_space > 0) {
                $storage[] = round(
                    (((int) $row->total_hdd_space - (int) ($row->free_hdd_space ?? 0)) / (int) $row->total_hdd_space) * 100,
                    2
                );
            } else {
                $storage[] = 0;
            }
        }

        while (count($labels) < 5) {
            $labels[] = 'R' . (count($labels) + 1);
            $cpu[] = 0;
            $ram[] = 0;
            $storage[] = 0;
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
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);

        return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i];
    }
}