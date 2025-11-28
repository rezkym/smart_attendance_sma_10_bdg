@php
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Str;
  $configData = Helper::appClasses();

  $isMenuActive = function ($item) use (&$isMenuActive) {
      $currentRouteName = Route::currentRouteName();
      $slugs = $item->slug ?? null;

      $matchesSlug = function ($slug) use ($currentRouteName) {
          if (! $slug) {
              return false;
          }

          return $currentRouteName === $slug || Str::startsWith($currentRouteName, $slug . '.');
      };

      if (is_array($slugs)) {
          foreach ($slugs as $slug) {
              if ($matchesSlug($slug)) {
                  return true;
              }
          }

          return false;
      }

      if ($matchesSlug($slugs)) {
          return true;
      }

      if (isset($item->submenu)) {
          foreach ($item->submenu as $submenu) {
              if ($isMenuActive($submenu)) {
                  return true;
              }
          }
      }

      return false;
  };
@endphp
<!-- Horizontal Menu -->
<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal  menu flex-grow-0"
  @foreach ($configData['menuAttributes'] as $attribute => $value)
  {{ $attribute }}="{{ $value }}" @endforeach>
  <div class="{{ $containerNav }} d-flex h-100">
    <ul class="menu-inner">
      @foreach ($menuData[1]->menu as $menu)
        {{-- active menu method --}}
        @php
          $activeClass = $isMenuActive($menu) ? 'active' : null;
        @endphp

        {{-- main menu --}}
        <li class="menu-item {{ $activeClass }}">
          <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
            class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
            @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
            @isset($menu->icon)
              <i class="{{ $menu->icon }}"></i>
            @endisset
            <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
          </a>

          {{-- submenu --}}
          @isset($menu->submenu)
            @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu, 'isMenuActive' => $isMenuActive])
          @endisset
        </li>
      @endforeach
    </ul>
  </div>
</aside>
<!--/ Horizontal Menu -->
