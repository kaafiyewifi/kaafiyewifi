<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
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
use Illuminate\Support\Facades\Schema;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()->with('location');

        if (!$this->isSuperAdmin()) {
            $query->whereIn('location_id', $this->getAssignedLocationIds());
        }

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
            'locations' => $this->getAllowedLocations(),
        ]);
    }

    public function store(Request $request)
    {
        $locationIds = $this->getAssignedLocationIds();

        $data = $request->validate([
            'type'                   => 'required|in:hotspot,pppoe',
            'full_name'              => 'required|string|max:255',
            'phone'                  => 'required|string|max:50|unique:customers,phone',
            'location_id'            => [
                'nullable',
                'exists:locations,id',
                Rule::when(!$this->isSuperAdmin(), ['in:' . ($locationIds->count() ? $locationIds->implode(',') : '0')]),
            ],
            'password'               => 'required|string|min:6',
            'device_limit'           => 'nullable|integer|min:1',
            'status'                 => 'required|in:active,inactive,suspended',
            'speed_override_enabled' => 'nullable|boolean',
            'download_speed'         => 'nullable|integer|min:1',
            'download_unit'          => 'nullable|string|in:Kbps,Mbps,Gbps',
            'upload_speed'           => 'nullable|integer|min:1',
            'upload_unit'            => 'nullable|string|in:Kbps,Mbps,Gbps',
        ]);

        $phone = trim((string) $data['phone']);

        // Set by the admin at creation time. FreeRADIUS PAP needs the
        // cleartext value, so it is stored in radius_password alongside the
        // hash used for portal login.
        $defaultPassword = $data['password'];
        $deviceLimit = (int) ($data['device_limit'] ?? 1);
        $speedOverrideEnabled = (int) $request->input('speed_override_enabled', 0) === 1;

        Log::info('CUSTOMER STORE HIT', ['phone' => $phone]);

        DB::beginTransaction();

        try {
            $customer = Customer::create([
                'location_id'            => $data['location_id'] ?? null,
                'type'                   => $data['type'],
                'full_name'              => $data['full_name'],
                'phone'                  => $phone,
                'username'               => $phone,
                'password'               => Hash::make($defaultPassword),
                'radius_password'        => $defaultPassword,
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

            $radius->createOrUpdateUser($phone, $defaultPassword, $data['type']);
            $radius->setUserDeviceLimit($phone, $deviceLimit);

            $speed = $radius->resolveEffectiveSpeed($customer);

            if ($speed) {
                $radius->setUserSpeed(
                    $phone,
                    $speed['download_speed'],
                    $speed['download_unit'],
                    $speed['upload_speed'],
                    $speed['upload_unit'],
                    $data['type']
                );
            } else {
                $radius->clearUserSpeed($phone);
            }

            app(RadiusSessionService::class)->enforceDeviceLimit($phone, $deviceLimit);

            if ($data['status'] === 'active') {
                $radius->setUserActive($phone, $data['type']);
            } else {
                $radius->setUserInactive($phone);
            }

            Log::info('RADIUS USER CREATED', ['username' => $phone]);

            $this->writeAuditLog(
                $request,
                'customer.created',
                $customer,
                'Customer created: ' . $customer->full_name,
                [
                    'type' => $customer->type,
                    'phone' => $customer->phone,
                    'username' => $customer->username,
                    'status' => $customer->status,
                    'device_limit' => $customer->device_limit,
                ]
            );

            DB::commit();

            return redirect()
                ->route('admin.customers.index')
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Customer waa la diiwaan geliyay',
                ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('CUSTOMER STORE FAILED', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Customer lama diiwaan gelin: ' . $e->getMessage(),
                ]);
        }
    }

    public function show(Customer $customer)
    {
        $this->authorizeCustomerAccess($customer);

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

        $plansQuery = Subscription::query()->where('status', 'active');

        if (Schema::hasColumn('subscriptions', 'location_id')) {
            if ($this->isSuperAdmin()) {
                $plansQuery->where('location_id', $customer->location_id);
            } else {
                $plansQuery->whereIn('location_id', $this->getAssignedLocationIds())
                    ->where('location_id', $customer->location_id);
            }
        }

        if (!$this->isSuperAdmin() && Schema::hasColumn('subscriptions', 'created_by')) {
            $plansQuery->where('created_by', auth()->id());
        }

        $plans = $plansQuery
            ->orderBy('name')
            ->get();

        return view('admin.customers.show', [
            'customer'      => $customer,
            'subscriptions' => $subscriptions,
            'plans'         => $plans,
        ]);
    }

    public function edit(Customer $customer)
    {
        $this->authorizeCustomerAccess($customer);

        return view('admin.customers.edit', [
            'customer'  => $customer,
            'locations' => $this->getAllowedLocations(),
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorizeCustomerAccess($customer);

        $locationIds = $this->getAssignedLocationIds();

        $data = $request->validate([
            'type'                   => 'required|in:hotspot,pppoe',
            'full_name'              => 'required|string|max:255',
            'phone'                  => [
                'required',
                'string',
                'max:50',
                Rule::unique('customers', 'phone')->ignore($customer->id),
            ],
            'location_id'            => [
                'nullable',
                'exists:locations,id',
                Rule::when(!$this->isSuperAdmin(), ['in:' . ($locationIds->count() ? $locationIds->implode(',') : '0')]),
            ],
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
        $oldType = $customer->type;
        $deviceLimit = (int) ($data['device_limit'] ?? 1);
        $speedOverrideEnabled = (int) $request->input('speed_override_enabled', 0) === 1;

        DB::beginTransaction();

        try {
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
                $oldActiveIp = DB::connection('radius')
                    ->table('radacct')
                    ->where('username', $oldUsername)
                    ->whereNull('acctstoptime')
                    ->orderByDesc('radacctid')
                    ->value('framedipaddress');

                if ($oldActiveIp) {
                    app(RadiusCoaService::class)->disconnect($oldUsername, $oldActiveIp);
                }

                $radius->deleteUser($oldUsername);
                $this->recreateRadiusUser($radius, $customer, $phone, $data['type']);
            } else {
                if ($oldType !== $data['type']) {
                    $this->recreateRadiusUser($radius, $customer, $phone, $data['type']);
                }
            }

            $radius->setUserDeviceLimit($phone, $deviceLimit);

            $speed = $radius->resolveEffectiveSpeed($customer);

            if ($speed) {
                $radius->setUserSpeed(
                    $phone,
                    $speed['download_speed'],
                    $speed['download_unit'],
                    $speed['upload_speed'],
                    $speed['upload_unit'],
                    $data['type']
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
                $radius->setUserActive($phone, $data['type']);
            } else {
                $radius->setUserInactive($phone);
            }

            $this->writeAuditLog(
                $request,
                'customer.updated',
                $customer,
                'Customer updated: ' . $customer->full_name,
                [
                    'type' => $customer->type,
                    'phone' => $customer->phone,
                    'username' => $customer->username,
                    'status' => $customer->status,
                    'device_limit' => $customer->device_limit,
                    'old_username' => $oldUsername,
                    'old_type' => $oldType,
                ]
            );

            DB::commit();

            return redirect()
                ->route('admin.customers.index')
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Customer waa la update gareeyay',
                ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('CUSTOMER UPDATE FAILED', [
                'customer_id' => $customer->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Customer lama update gareyn: ' . $e->getMessage(),
                ]);
        }
    }

    public function updatePassword(Request $request, Customer $customer)
    {
        $this->authorizeCustomerAccess($customer);

        $data = $request->validate(
            [
                'password' => 'required|string|min:6|same:password_confirmation',
                'password_confirmation' => 'required|string|min:6',
            ]
        );

    $customer->update([
        'password'        => Hash::make($data['password']),
        'radius_password' => $data['password'],
        ]);


        app(RadiusUserService::class)->createOrUpdateUser($customer->username, $data['password'], $customer->type);

        $this->writeAuditLog(
            $request,
            'customer.password_updated',
            $customer,
            'Customer password updated: ' . $customer->full_name,
            [
                'username' => $customer->username,
                'type' => $customer->type,
            ]
        );

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

        $customer = Customer::where('username', $data['username'])->first();

        if ($customer) {
            $this->authorizeCustomerAccess($customer);
        }

        app(RadiusCoaService::class)->disconnect($data['username'], $data['ip']);

        $this->writeAuditLog(
            $request,
            'customer.device_disconnected',
            $customer,
            'Customer device disconnected: ' . $data['username'],
            [
                'username' => $data['username'],
                'ip' => $data['ip'],
            ]
        );

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Device waa la disconnect gareeyay',
        ]);
    }

    public function destroy(Customer $customer)
    {
        $this->authorizeCustomerAccess($customer);

        DB::beginTransaction();

        try {
            $customerData = [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'username' => $customer->username,
                'phone' => $customer->phone,
                'type' => $customer->type,
            ];

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

            $this->writeAuditLog(
                request(),
                'customer.deleted',
                null,
                'Customer deleted: ' . $customerData['full_name'],
                [
                    'customer_id' => $customerData['id'],
                    'username' => $customerData['username'],
                    'phone' => $customerData['phone'],
                    'type' => $customerData['type'],
                ]
            );

            DB::commit();

            return redirect()
                ->route('admin.customers.index')
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Customer waa la tirtiray, internet-kana waa la jaray',
                ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('CUSTOMER DELETE FAILED', [
                'customer_id' => $customer->id,
                'username' => $customer->username,
                'error' => $e->getMessage(),
            ]);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Customer lama tirtirin: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Recreate a customer's RADIUS entry under a new username or service type,
     * carrying over the password they already have.
     *
     * Deliberately does NOT fall back to a default when radius_password is
     * empty: any constant here becomes a credential shared by every such
     * account. The customer is instead left unable to authenticate until an
     * admin sets a password, which is the safe direction to fail.
     */
    private function recreateRadiusUser(
        RadiusUserService $radius,
        Customer $customer,
        string $username,
        string $serviceType
    ): void {
        $password = (string) ($customer->radius_password ?? '');

        if ($password === '') {
            Log::error('RADIUS user not recreated: no stored password', [
                'customer_id' => $customer->id,
                'username'    => $username,
                'action'      => 'admin must set a password via the customer password form',
            ]);

            return;
        }

        $radius->createOrUpdateUser($username, $password, $serviceType);
    }

    private function writeAuditLog(Request $request, string $action, ?Customer $customer, string $description, array $properties = []): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'target_type' => $customer ? Customer::class : null,
                'target_id' => $customer?->id,
                'description' => $description,
                'ip_address' => $request->ip(),
                'properties' => !empty($properties) ? json_encode($properties, JSON_UNESCAPED_UNICODE) : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('AUDIT LOG WRITE FAILED', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
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

    private function getAllowedLocations()
    {
        if ($this->isSuperAdmin()) {
            return Location::orderBy('name')->get();
        }

        return Location::whereIn('id', $this->getAssignedLocationIds())
            ->orderBy('name')
            ->get();
    }

    private function authorizeCustomerAccess(Customer $customer): void
    {
        if ($this->isSuperAdmin()) {
            return;
        }

        abort_unless(
            $this->getAssignedLocationIds()->contains($customer->location_id),
            403,
            'Unauthorized access to this customer.'
        );
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