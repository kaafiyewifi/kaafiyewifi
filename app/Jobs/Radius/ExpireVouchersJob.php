<?php
// app/Jobs/Radius/ExpireVouchersJob.php
namespace App\Jobs\Radius;

use App\Models\Voucher;
use App\Services\Routers\RadiusSyncService;
use App\Services\Radius\RadiusCoaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExpireVouchersJob implements ShouldQueue
{
    use Queueable;

    public function handle(RadiusSyncService $radius, RadiusCoaService $coa): void
    {
        // Example: expire active vouchers only
        $vouchers = Voucher::query()->where('status', 'active')->get();

        foreach ($vouchers as $v) {
            // total seconds used from radacct
            $usedSeconds = (int) \DB::connection('radius')
                ->table('radacct')
                ->where('username', $v->code)
                ->sum('acctsessiontime');

            $usedMinutes = intdiv($usedSeconds, 60);
            $v->minutes_used = $usedMinutes;

            if ($usedMinutes >= $v->minutes_total) {
                $v->status = 'used';
                $v->save();

                // Disable in RADIUS
                $radius->disableUser($v->code);

                // Kick live (if router known)
                if ($v->router_id) {
                    $router = \App\Models\Router::find($v->router_id);
                    if ($router) $coa->disconnectUser($router, $v->code);
                }
            } else {
                $v->save();
            }
        }
    }
}
