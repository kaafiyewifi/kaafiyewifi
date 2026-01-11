<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /* =========================
         * 👥 USERS STATS
         * ========================= */
        $usersCount = User::count();

        // USERS PER MONTH
        $usersByMonth = User::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $userLabels = [];
        $userData   = [];

        foreach ($usersByMonth as $row) {
            $userLabels[] = date('M', mktime(0, 0, 0, $row->month, 1));
            $userData[]   = $row->total;
        }

        /* =========================
         * 🔐 LOGIN ACTIVITY (LAST 7 DAYS)
         * ========================= */
        $loginByDay = User::whereNotNull('last_login_at')
            ->selectRaw('DATE(last_login_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->limit(7)
            ->get();

        $loginLabels = $loginByDay->pluck('date');
        $loginData   = $loginByDay->pluck('total');

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
         * 💰 FINANCIAL / PAYMENT STATS (PHASE 2.3)
         * ========================= */
        $todayRevenue = Payment::whereDate('created_at', today())
            ->where('status', 'paid')
            ->sum('amount');

        $monthRevenue = Payment::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('status', 'paid')
            ->sum('amount');

        $totalRevenue = Payment::where('status', 'paid')->sum('amount');

        // MONTHLY REVENUE CHART
        $revenueByMonth = Payment::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->whereYear('created_at', now()->year)
            ->where('status', 'paid')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $revenueLabels = [];
        $revenueData   = [];

        foreach ($revenueByMonth as $row) {
            $revenueLabels[] = date('M', mktime(0, 0, 0, $row->month, 1));
            $revenueData[]   = $row->total;
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
