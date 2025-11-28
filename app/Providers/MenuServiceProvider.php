<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Routing\Route;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
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
    $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
    $verticalMenuData = json_decode($verticalMenuJson);
    $horizontalMenuJson = file_get_contents(base_path('resources/menu/horizontalMenu.json'));
    $horizontalMenuData = json_decode($horizontalMenuJson);

    $user = Auth::user();

    $verticalMenuData->menu = $this->filterMenuByRole($verticalMenuData->menu ?? [], $user);
    $horizontalMenuData->menu = $this->filterMenuByRole($horizontalMenuData->menu ?? [], $user);

    // Share all menuData to all the views
    $this->app->make('view')->share('menuData', [$verticalMenuData, $horizontalMenuData]);
  }

  private function filterMenuByRole(array $menuItems, $user): array
  {
    return collect($menuItems)
      ->filter(function ($item) use ($user) {
        $roles = Arr::get($item, 'roles');

        if (is_array($roles) && !empty($roles)) {
          if (!$user) {
            return false;
          }

          return collect($roles)->contains(fn ($role) => $user->hasRole($role));
        }

        return true;
      })
      ->map(function ($item) use ($user) {
        if (isset($item->submenu) && is_array($item->submenu)) {
          $item->submenu = $this->filterMenuByRole($item->submenu, $user);
        }

        return $item;
      })
      ->filter(function ($item) {
        if (isset($item->menuHeader)) {
          return true;
        }

        if (isset($item->submenu) && is_array($item->submenu)) {
          return count($item->submenu) > 0 || !isset($item->menuHeader);
        }

        return true;
      })
      ->values()
      ->all();
  }
}
