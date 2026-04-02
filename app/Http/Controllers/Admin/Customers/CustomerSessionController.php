<?php

namespace App\Http\Controllers\Admin\Customers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Radius\StaleSessionService;
use Illuminate\Http\RedirectResponse;

class CustomerSessionController extends Controller
{
    public function clearStaleSessions(Customer $customer, StaleSessionService $staleSessionService): RedirectResponse
    {
        $result = $staleSessionService->clearCustomerSessions($customer);

        if (!$result['ok']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }
}