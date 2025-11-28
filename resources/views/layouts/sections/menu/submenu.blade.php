@php
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Str;
@endphp

<ul class="menu-sub">
  @if (isset($menu))
    @foreach ($menu as $submenu)
      {{-- active menu method --}}
      @php
        $activeClass = null;
        $active = $configData['layout'] === 'vertical' ? 'active open' : 'active';
        $currentRouteName = Route::currentRouteName();

        $matchesSlug = function ($slug) use ($currentRouteName) {
            return $currentRouteName === $slug || Str::startsWith($currentRouteName, $slug . '.');
        };

        $slugs = $submenu->slug ?? null;

        if (is_array($slugs)) {
            foreach ($slugs as $slug) {
                if ($matchesSlug($slug)) {
                    $activeClass = $active;
                }
            }
        } elseif ($matchesSlug($slugs)) {
            $activeClass = $active;
        } elseif (isset($submenu->submenu ?? null)) {
            foreach ($submenu->submenu as $nested) {
                if (isset($isMenuActive) && $isMenuActive($nested)) {
                    $activeClass = $active;
                }
            }
        }
      @endphp

      <li class="menu-item {{ $activeClass }}">
        <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0)' }}"
          class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
          @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
          @if (isset($submenu->icon))
            <i class="{{ $submenu->icon }}"></i>
          @endif
          <div>{{ isset($submenu->name) ? __($submenu->name) : '' }}</div>
          @isset($submenu->badge)
            <div class="badge bg-{{ $submenu->badge[0] }} rounded-pill ms-auto">{{ $submenu->badge[1] }}</div>
          @endisset
        </a>

        {{-- submenu --}}
        @if (isset($submenu->submenu))
          @include('layouts.sections.menu.submenu', ['menu' => $submenu->submenu])
        @endif
      </li>
    @endforeach
  @endif
</ul>
