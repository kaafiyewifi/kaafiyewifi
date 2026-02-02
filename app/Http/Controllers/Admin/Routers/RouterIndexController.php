<?php
// app/Http/Controllers/Admin/Routers/RouterIndexController.php
namespace App\Http\Controllers\Admin\Routers;

use App\Http\Controllers\Controller;
use App\Models\Router;
use Illuminate\Http\Request;

class RouterIndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $status = $request->get('status'); // connected/offline/...
        $q = trim((string)$request->get('q'));

        $base = Router::query()
            ->with(['latestMetric'])
            ->orderByDesc('last_seen_at')
            ->orderByDesc('id');

        if ($status) {
            $base->where('status', $status);
        }

        if ($q !== '') {
            $base->where(function($w) use ($q) {
                $w->where('name','like',"%{$q}%")
                  ->orWhere('identity','like',"%{$q}%")
                  ->orWhere('mgmt_host','like',"%{$q}%");
            });
        }

        $routers = $base->paginate((int)($request->get('per_page', 10)))->withQueryString();

        // Counters (no filters)
        $total = Router::count();
        $onlineCount = Router::where('status','connected')->count();
        $offlineCount = Router::where('status','offline')->count();

        return view('admin.routers.index', compact('routers','total','onlineCount','offlineCount'));
    }
}
