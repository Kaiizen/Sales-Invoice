<?php

namespace App\Policies;

use App\User;
use App\CustomOrder;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomOrderPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function view(User $user, CustomOrder $customOrder)
    {
        return $user->id === $customOrder->user_id;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, CustomOrder $customOrder)
    {
        return $user->id === $customOrder->user_id;
    }

    public function delete(User $user, CustomOrder $customOrder)
    {
        return $user->id === $customOrder->user_id;
    }
}