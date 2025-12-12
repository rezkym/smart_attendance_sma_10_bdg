<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\IotDevice;
use App\Models\User;

class IotDevicePolicy
{
    /**
     * Determine whether the user can view any IoT devices.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('iot-devices.view');
    }

    /**
     * Determine whether the user can view the IoT device.
     */
    public function view(User $user, IotDevice $iotDevice): bool
    {
        return $user->can('iot-devices.view');
    }

    /**
     * Determine whether the user can create IoT devices.
     */
    public function create(User $user): bool
    {
        return $user->can('iot-devices.create');
    }

    /**
     * Determine whether the user can update the IoT device.
     */
    public function update(User $user, IotDevice $iotDevice): bool
    {
        return $user->can('iot-devices.update');
    }

    /**
     * Determine whether the user can delete the IoT device.
     */
    public function delete(User $user, IotDevice $iotDevice): bool
    {
        return $user->can('iot-devices.delete');
    }

    /**
     * Determine whether the user can regenerate API key.
     */
    public function regenerateKey(User $user, IotDevice $iotDevice): bool
    {
        return $user->can('iot-devices.regenerate-key');
    }

    /**
     * Determine whether the user can view IoT logs.
     */
    public function viewLogs(User $user): bool
    {
        return $user->can('iot-logs.view');
    }

    /**
     * Determine whether the user can export IoT logs.
     */
    public function exportLogs(User $user): bool
    {
        return $user->can('iot-logs.export');
    }
}
