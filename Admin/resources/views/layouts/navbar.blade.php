@php
    $user = Auth::user();
@endphp

<nav id="navigation" class="app-header navbar navbar-expand bg-white border-bottom sticky-top" tabindex="-1">
    <div class="container-fluid">

        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link cyb-sidebar-toggle"
                   data-lte-toggle="sidebar"
                   href="#"
                   role="button"
                   aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown user-menu">
                <a href="#"
                   class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                   data-bs-toggle="dropdown"
                   aria-expanded="false">
                    <x-avatar :name="$user->name" size="30" />

                    <span class="d-none d-md-inline fw-medium">
                        {{ $user->name }}
                    </span>
                </a>

                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <li class="user-header text-bg-primary">
                        <x-avatar :name="$user->name" size="80" />

                        <p>
                            <strong>{{ $user->name }}</strong>
                            <small>{{ $user->role?->name ?? 'Admin' }}</small>
                            <span class="fs-7">Crusader Yearbook 2026</span>
                        </p>
                    </li>

                    <li class="user-footer">
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
                            Profile
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf

                            <button type="submit" class="btn btn-outline-danger float-end">
                                Sign out
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>

    </div>
</nav>