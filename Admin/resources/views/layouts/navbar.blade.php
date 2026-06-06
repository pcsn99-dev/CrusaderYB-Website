@php
    $user = Auth::user();
@endphp



<nav id="navigation" class="app-header navbar navbar-expand bg-body sticky-top" tabIndex="-1">

    <div class="container-fluid">

        <ul class="navbar-nav">
            <li class="nav-item">
            <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                <i class="bi bi-list"></i>
            </a>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a href="#" class="nav-link" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                    <i class="bi bi-bell-fill"></i>
                    <span class="navbar-badge badge text-bg-warning">3</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end show" data-bs-popper="static">
                    <span class="dropdown-item dropdown-header">3 Notifications</span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">1 Unsure
                        <span class="float-end text-secondary fs-7">3 mins</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer"> See All Notifications </a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link show" href="#" id="bd-theme" aria-label="Toggle color scheme" data-bs-toggle="dropdown" aria-expanded="true">
                <i class="bi bi-sun-fill" data-lte-theme-icon="light"></i>
                <i class="bi bi-moon-fill d-none" data-lte-theme-icon="dark"></i>
                <i class="bi bi-circle-half d-none" data-lte-theme-icon="auto"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end show" aria-labelledby="bd-theme" style="--bs-dropdown-min-width: 8rem" data-bs-popper="static">
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="light" aria-pressed="true">
                    <i class="bi bi-sun-fill me-2"></i>
                    Light
                    <i class="bi bi-check-lg ms-auto"></i>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                    <i class="bi bi-moon-fill me-2"></i>
                    Dark
                    <i class="bi bi-check-lg ms-auto d-none"></i>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="auto" aria-pressed="false">
                    <i class="bi bi-circle-half me-2"></i>
                    Auto
                    <i class="bi bi-check-lg ms-auto d-none"></i>
                    </button>
                </li>
                </ul>
            </li>
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <x-avatar :name="$user->name" size="30" />
                    <span class="d-none d-md-inline">{{ $user->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <!--begin::User Image-->
                <li class="user-header text-bg-primary">
                    <x-avatar :name="$user->name" size="80" />
                    <p>
                    <strong>{{ $user->name }}</strong>
                    <small>{{ $user->role->name }}</small>
                    <span class="fs-7">Crudaser Yearbook 2026</span>
                    </p>
                </li>
                <!--end::User Image-->

                <!--begin::Menu Footer-->

    
                <li class="user-footer">
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">Profile</a>
                   <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger float-end">
                            Sign out
                        </button>
                    </form>
                </li>
                <!--end::Menu Footer-->
                </ul>
            </li>
        </ul>

    </div>
    
</nav>