<header class="sticky top-0 z-40 w-full border-b border-base-300 bg-base-100">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex h-14 items-center justify-between">

      <div class="flex items-center gap-3">
        <span class="text-lg font-semibold">{{ config('app.name','KaafiyeWiFi') }}</span>
      </div>

      <div class="flex items-center gap-2">

        {{-- Dark/Light toggle (uses data-theme) --}}
        <button id="themeToggle" type="button" class="btn btn-sm btn-text">
          <span class="icon-[tabler--moon] size-5"></span>
          <span class="hidden sm:inline">Theme</span>
        </button>

        {{-- User dropdown (FlyonUI dropdown) --}}
        <div class="dropdown relative">
          <button class="btn btn-sm btn-text" aria-haspopup="menu" aria-expanded="false" data-dropdown-toggle="#userMenu">
            {{ auth()->user()->name ?? 'User' }}
            <span class="icon-[tabler--chevron-down] size-4"></span>
          </button>

          <div id="userMenu" class="dropdown-menu hidden min-w-44 rounded-box border border-base-300 bg-base-100 p-2 shadow">
            <a class="dropdown-item rounded-btn px-3 py-2 hover:bg-base-200" href="#">
              Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button class="dropdown-item w-full rounded-btn px-3 py-2 text-left hover:bg-base-200" type="submit">
                Logout
              </button>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</header>
