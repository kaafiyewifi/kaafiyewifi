<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Subscription;
use App\Services\Radius\RadiusUserService;
use App\Services\Radius\RadiusCoaService;
use App\Services\Radius\RadiusSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()->with('location');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('full_name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    ->orWhere('id', $q);
            });
        }

        $customers = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'                   => 'required|in:hotspot,pppoe',
            'full_name'              => 'required|string|max:255',
            'phone'                  => 'required|string|max:50|unique:customers,phone',
            'location_id'            => 'nullable|exists:locations,id',
            'device_limit'           => 'nullable|integer|min:1',
            'status'                 => 'required|in:active,inactive,suspended',
            'speed_override_enabled' => 'nullable|boolean',
            'download_speed'         => 'nullable|integer|min:1',
            'download_unit'          => 'nullable|string|in:Kbps,Mbps,Gbps',
            'upload_speed'           => 'nullable|integer|min:1',
            'upload_unit'            => 'nullable|string|in:Kbps,Mbps,Gbps',
        ]);

        $phone = trim((string) $data['phone']);
        $defaultPassword = '123456';
        $deviceLimit = (int) ($data['device_limit'] ?? 1);
        $speedOverrideEnabled = (int) $request->input('speed_override_enabled', 0) === 1;

        Log::info('CUSTOMER STORE HIT', ['phone' => $phone]);

        $customer = Customer::create([
            'location_id'            => $data['location_id'] ?? null,
            'type'                   => $data['type'],
            'full_name'              => $data['full_name'],
            'phone'                  => $phone,
            'username'               => $phone,
            'password'               => Hash::make($defaultPassword),
            'device_limit'           => $deviceLimit,
            'status'                 => $data['status'],
            'speed_override_enabled' => $speedOverrideEnabled,
            'download_speed'         => $speedOverrideEnabled ? ($request->input('download_speed') ?: null) : null,
            'download_unit'          => $speedOverrideEnabled ? ($request->input('download_unit', 'Mbps')) : null,
            'upload_speed'           => $speedOverrideEnabled ? ($request->input('upload_speed') ?: null) : null,
            'upload_unit'            => $speedOverrideEnabled ? ($request->input('upload_unit', 'Mbps')) : null,
        ]);

        $customer->refresh();

        $radius = app(RadiusUserService::class);

        $radius->createOrUpdateUser($phone, $defaultPassword);
        $radius->setUserDeviceLimit($phone, $deviceLimit);

        $speed = $radius->resolveEffectiveSpeed($customer);

        if ($speed) {
            $radius->setUserSpeed(
                $phone,
                $speed['download_speed'],
                $speed['download_unit'],
                $speed['upload_speed'],
                $speed['upload_unit']
            );
        }

        app(RadiusSessionService::class)->enforceDeviceLimit($phone, $deviceLimit);

        if ($data['status'] === 'active') {
            $radius->setUserActive($phone);
        } else {
            $radius->setUserInactive($phone);
        }

        Log::info('RADIUS USER CREATED', ['username' => $phone]);

        return redirect()
            ->route('admin.customers.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Customer waa la diiwaan geliyay',
            ]);
    }

    public function show(Customer $customer)
    {
        $customer->load(['location', 'subscriptions.plan']);

        $subscriptions = $customer->subscriptions()
            ->orderByRaw("
                CASE status
                    WHEN 'active' THEN 1
                    WHEN 'pending' THEN 2
                    WHEN 'expired' THEN 3
                    WHEN 'suspended' THEN 4
                    WHEN 'cancelled' THEN 5
                    ELSE 6
                END
            ")
            ->orderByDesc('created_at')
            ->get();

        return view('admin.customers.show', [
            'customer'      => $customer,
            'subscriptions' => $subscriptions,
            'plans'         => Subscription::where('status', 'active')->get(),
        ]);
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', [
            'customer'  => $customer,
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'type'                   => 'required|in:hotspot,pppoe',
            'full_name'              => 'required|string|max:255',
            'phone'                  => [
                'required',
                'string',
                'max:50',
                Rule::unique('customers', 'phone')->ignore($customer->id),
            ],
            'location_id'            => 'nullable|exists:locations,id',
            'device_limit'           => 'nullable|integer|min:1',
            'status'                 => 'required|in:active,inactive,suspended',
            'speed_override_enabled' => 'nullable|boolean',
            'download_speed'         => 'nullable|integer|min:1',
            'download_unit'          => 'nullable|string|in:Kbps,Mbps,Gbps',
            'upload_speed'           => 'nullable|integer|min:1',
            'upload_unit'            => 'nullable|string|in:Kbps,Mbps,Gbps',
        ]);

        $phone = trim((string) $data['phone']);
        $oldUsername = $customer->username;
        $deviceLimit = (int) ($data['device_limit'] ?? 1);
        $speedOverrideEnabled = (int) $request->input('speed_override_enabled', 0) === 1;

        $customer->update([
            'location_id'            => $data['location_id'] ?? null,
            'type'                   => $data['type'],
            'full_name'              => $data['full_name'],
            'phone'                  => $phone,
            'username'               => $phone,
            'device_limit'           => $deviceLimit,
            'status'                 => $data['status'],
            'speed_override_enabled' => $speedOverrideEnabled,
            'download_speed'         => $speedOverrideEnabled ? ($request->input('download_speed') ?: null) : null,
            'download_unit'          => $speedOverrideEnabled ? ($request->input('download_unit', 'Mbps')) : null,
            'upload_speed'           => $speedOverrideEnabled ? ($request->input('upload_speed') ?: null) : null,
            'upload_unit'            => $speedOverrideEnabled ? ($request->input('upload_unit', 'Mbps')) : null,
        ]);

        $customer->refresh();

        $radius = app(RadiusUserService::class);

        if ($oldUsername !== $phone) {
            $radius->deleteUser($oldUsername);
            $radius->createOrUpdateUser($phone, '123456');
        }

        $radius->setUserDeviceLimit($phone, $deviceLimit);

        $speed = $radius->resolveEffectiveSpeed($customer);

        if ($speed) {
            $radius->setUserSpeed(
                $phone,
                $speed['download_speed'],
                $speed['download_unit'],
                $speed['upload_speed'],
                $speed['upload_unit']
            );

            $activeIp = DB::connection('radius')
                ->table('radacct')
                ->where('username', $phone)
                ->whereNull('acctstoptime')
                ->orderByDesc('radacctid')
                ->value('framedipaddress');

            if ($activeIp) {
                app(RadiusCoaService::class)->disconnect($phone, $activeIp);
            }
        } else {
            $radius->clearUserSpeed($phone);
        }

        app(RadiusSessionService::class)->enforceDeviceLimit($phone, $deviceLimit);

        if ($data['status'] === 'active') {
            $radius->setUserActive($phone);
        } else {
            $radius->setUserInactive($phone);
        }

        return redirect()
            ->route('admin.customers.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Customer waa la update gareeyay',
            ]);
    }

    public function updatePassword(Request $request, Customer $customer)
    {
        $data = $request->validate(
            [
                'password' => 'required|string|min:6|same:password_confirmation',
                'password_confirmation' => 'required|string|min:6',
            ]
        );

        $customer->update([
            'password' => Hash::make($data['password']),
        ]);

        app(RadiusUserService::class)->createOrUpdateUser($customer->username, $data['password']);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Customer password waa la beddelay',
            ]);
    }

    public function disconnectDevice(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'ip' => 'required|ip',
        ]);

        app(RadiusCoaService::class)->disconnect($data['username'], $data['ip']);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Device waa la disconnect gareeyay',
        ]);
    }

    public function destroy(Customer $customer)
    {
        $activeIp = DB::connection('radius')
            ->table('radacct')
            ->where('username', $customer->username)
            ->whereNull('acctstoptime')
            ->orderByDesc('radacctid')
            ->value('framedipaddress');

        if ($activeIp) {
            app(RadiusCoaService::class)
                ->disconnect($customer->username, $activeIp);
        }

        app(RadiusUserService::class)->deleteUser($customer->username);

        $customer->delete();

        return redirect()
            ->route('admin.customers.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Customer waa la tirtiray, internet-kana waa la jaray',
            ]);
    }

    private function buildMikrotikRateLimit(
        int $downloadSpeed,
        string $downloadUnit,
        int $uploadSpeed,
        string $uploadUnit
    ): string {
        $download = $this->convertToBitsPerSecond($downloadSpeed, $downloadUnit);
        $upload = $this->convertToBitsPerSecond($uploadSpeed, $uploadUnit);

        return $download . '/' . $upload;
    }

    private function convertToBitsPerSecond(int $speed, string $unit): int
    {
        return match (strtolower($unit)) {
            'kbps' => $speed * 1000,
            'gbps' => $speed * 1000000000,
            default => $speed * 1000000,
        };
    }
}