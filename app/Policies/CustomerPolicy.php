<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage customers');
    }

    public function view(User $user, Customer $customer): bool
    {
        if ($user->hasRole('super_admin')) return true;

        return $user->can('manage customers')
            && $user->canAccessLocation($customer->location_id);
    }

    public function create(User $user): bool
    {
        return $user->can('manage customers');
    }

    public function update(User $user, Customer $customer): bool
    {
        if ($user->hasRole('super_admin')) return true;

        return $user->can('manage customers')
            && $user->canAccessLocation($customer->location_id);
    }

    public function delete(User $user, Customer $customer): bool
    {
        if ($user->hasRole('super_admin')) return true;

        return $user->can('manage customers')
            && $user->canAccessLocation($customer->location_id);
    }
}
