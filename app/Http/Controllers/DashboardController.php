<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        /* =========================
         * 👥 USERS STATS
         * ========================= */
        $usersCount = User::count();

        $userLabels = [];
        $userData = [];

        $usersByMonth = User::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        for ($month = 1; $month <= 12; $month++) {
            $userLabels[] = Carbon::create()->month($month)->format('M');
            $userData[] = (int) ($usersByMonth[$month]->total ?? 0);
        }

        /* =========================
         * 🔐 LOGIN ACTIVITY (LAST 7 DAYS)
         * ========================= */
        $loginLabels = [];
        $loginData = [];

        $loginByDay = User::whereNotNull('last_login_at')
            ->selectRaw('DATE(last_login_at) as login_date, COUNT(*) as total')
            ->whereDate('last_login_at', '>=', now()->subDays(6)->toDateString())
            ->groupBy('login_date')
            ->orderBy('login_date')
            ->get()
            ->keyBy('login_date');

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $loginLabels[] = Carbon::parse($day)->format('d M');
            $loginData[] = (int) ($loginByDay[$day]->total ?? 0);
        }

        /* =========================
         * 🔥 ACTIVE SESSIONS
         * ========================= */
        $activeSessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select(
                'sessions.id',
                'users.name',
                'users.email',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity'
            )
            ->orderByDesc('sessions.last_activity')
            ->limit(10)
            ->get();

        /* =========================
         * 💰 FINANCIAL / PAYMENT STATS
         * ========================= */
        $todayRevenue = Payment::query()
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereDate('paid_at', today())
            ->sum('amount');

        $monthRevenue = Payment::query()
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('amount');

        $totalRevenue = Payment::query()
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->sum('amount');

        /* =========================
         * 📈 MONTHLY REVENUE CHART
         * ========================= */
        $revenueLabels = [];
        $revenueData = [];

        $revenueByMonth = Payment::selectRaw('MONTH(paid_at) as month, SUM(amount) as total')
            ->whereYear('paid_at', now()->year)
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        for ($month = 1; $month <= 12; $month++) {
            $revenueLabels[] = Carbon::create()->month($month)->format('M');
            $revenueData[] = (float) ($revenueByMonth[$month]->total ?? 0);
        }

        /* =========================
         * 📦 RETURN VIEW
         * ========================= */
        return view('dashboard', compact(
            'usersCount',
            'userLabels',
            'userData',
            'loginLabels',
            'loginData',
            'activeSessions',
            'todayRevenue',
            'monthRevenue',
            'totalRevenue',
            'revenueLabels',
            'revenueData'
        ));
    }
}