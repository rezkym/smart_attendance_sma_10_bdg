<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RfidCard;
use App\Models\User;

class RfidCardPolicy
{
    /**
     * Determine whether the user can view any RFID cards.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('rfid-cards.view');
    }

    /**
     * Determine whether the user can view the RFID card.
     */
    public function view(User $user, RfidCard $rfidCard): bool
    {
        return $user->can('rfid-cards.view');
    }

    /**
     * Determine whether the user can create RFID cards.
     */
    public function create(User $user): bool
    {
        return $user->can('rfid-cards.create');
    }

    /**
     * Determine whether the user can update the RFID card.
     */
    public function update(User $user, RfidCard $rfidCard): bool
    {
        return $user->can('rfid-cards.edit');
    }

    /**
     * Determine whether the user can delete the RFID card.
     */
    public function delete(User $user, RfidCard $rfidCard): bool
    {
        return $user->can('rfid-cards.delete');
    }

    /**
     * Determine whether the user can block/unblock the RFID card.
     */
    public function block(User $user, RfidCard $rfidCard): bool
    {
        return $user->can('rfid-cards.block');
    }
}
