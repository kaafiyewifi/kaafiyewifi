<?php

// app/Policies/RouterPolicy.php
namespace App\Policies;

use App\Models\Router;
use App\Models\User;

class RouterPolicy
{
    public function view(User $user, Router $router): bool
    {
        return $router->tenant_id === null || $router->tenant_id === $user->tenant_id;
    }

    public function update(User $user, Router $router): bool
    {
        return $router->tenant_id === null || $router->tenant_id === $user->tenant_id;
    }
}
