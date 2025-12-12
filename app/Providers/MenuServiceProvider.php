<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Arr;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Use View Composer to ensure user is available when menu is rendered
        View::composer('*', function ($view) {
            // Only share menuData if not already shared
            if (!isset($view->getData()['menuData']) || $view->getData()['menuData'] === null) {
                $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
                $verticalMenuData = json_decode($verticalMenuJson);
                $horizontalMenuJson = file_get_contents(base_path('resources/menu/horizontalMenu.json'));
                $horizontalMenuData = json_decode($horizontalMenuJson);

                $user = Auth::user();

                $verticalMenuData->menu = $this->filterMenuByAccess($verticalMenuData->menu ?? [], $user);
                $horizontalMenuData->menu = $this->filterMenuByAccess($horizontalMenuData->menu ?? [], $user);

                $view->with('menuData', [$verticalMenuData, $horizontalMenuData]);
            }
        });
    }

    /**
     * Filter menu items by user roles and permissions.
     *
     * @param array $menuItems
     * @param mixed $user
     * @return array
     */
    private function filterMenuByAccess(array $menuItems, $user): array
    {
        return collect($menuItems)
            ->filter(function ($item) use ($user) {
                return $this->hasAccess($item, $user);
            })
            ->map(function ($item) use ($user) {
                if (isset($item->submenu) && is_array($item->submenu)) {
                    $item->submenu = $this->filterMenuByAccess($item->submenu, $user);
                }

                return $item;
            })
            ->filter(function ($item) {
                // Keep menu headers
                if (isset($item->menuHeader)) {
                    return true;
                }

                // Filter out parent items with empty submenus
                if (isset($item->submenu) && is_array($item->submenu)) {
                    return count($item->submenu) > 0;
                }

                return true;
            })
            ->values()
            ->all();
    }

    /**
     * Check if user has access to a menu item based on roles or permissions.
     *
     * @param object $item Menu item object
     * @param mixed $user User instance or null
     * @return bool
     */
    private function hasAccess(object $item, $user): bool
    {
        // Guest users can only see items without roles/permissions restrictions
        if (!$user) {
            $hasRoles = isset($item->roles) && !empty($item->roles);
            $hasPermissions = isset($item->permissions) && !empty($item->permissions);

            return !$hasRoles && !$hasPermissions;
        }

        // Check permissions first (more granular)
        if (isset($item->permissions) && is_array($item->permissions) && !empty($item->permissions)) {
            return $user->hasAnyPermission($item->permissions);
        }

        // Fallback to role check (backwards compatibility)
        if (isset($item->roles) && is_array($item->roles) && !empty($item->roles)) {
            return collect($item->roles)->contains(fn ($role) => $user->hasRole($role));
        }

        // No restrictions defined, allow access
        return true;
    }
}
