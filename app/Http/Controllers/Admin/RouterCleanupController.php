<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Routers\RouterCleanupService;
use Illuminate\Http\Request;

class RouterCleanupController extends Controller
{
    public function preview(RouterCleanupService $svc)
    {
        $result = $svc->deleteDuplicates(true);
        return view('admin.routers.cleanup', $result);
    }

    public function run(Request $request, RouterCleanupService $svc)
    {
        // CSRF protected POST
        $result = $svc->deleteDuplicates(false);

        return redirect()
            ->route('admin.routers.cleanup.preview')
            ->with('status', "Deleted {$result['deleted_count']} duplicate router(s).");
    }
}
