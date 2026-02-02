@props([
    'title' => 'Manajemen Projek',
    'showSearchFilter' => true,
    'userName' => null,
    'userRole' => null,
    'userAvatar' => null,
    'showNotificationBadge' => true,
    'notificationCount' => 3,
    'searchPlaceholder' => 'Cari Projek...'
])

@vite(['resources/js/Components/navbar.js'])

<nav class="navbar-search-filter">
    <div class="navbar-top">
        <div class="navbar-left">
            <div class="navbar-logo">
                <img class="navbar-logo-img" src="{{ asset('images/LogoPutihTr.png') }}" alt="Logo">
            </div>
            <h1 class="navbar-title">{{ $title }}</h1>
        </div>

        <div class="navbar-right">
            <div class="user-info">
                <div class="user-details">
                    <span class="user-name" id="nav-user-name">{{ $userName ?? 'Loading...' }}</span> 
                    <span class="user-role" id="nav-user-role">{{ $userRole ?? '...' }}</span>
                </div>
                
                <div class="avatar-notification">
                    <div class="user-avatar" id="avatarBtn">
                        @if($userAvatar)
                            <img src="{{ $userAvatar }}" alt="User Avatar">
                        @else
                            <div class="avatar-placeholder">
                                {{ strtoupper(substr($userAvatar ?? 'P', 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div id="avatarDropdown" class="custom-dropdown-menu avatar-dropdown d-none">
                        <a href="/profile/edit" class="dropdown-item">
                            <i class="bi bi-person-gear"></i> Edit Profile
                        </a>
                        <hr class="dropdown-divider">
                        <div class="dropdown-item text-danger" id="logoutBtn">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @if($showSearchFilter)
    <div class="navbar-bottom">
        <div class="search-container" style="min-width: 400px;">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input 
                    type="text" 
                    class="form-control border-start-0 border-end-0 ps-2" 
                    placeholder="{{ $searchPlaceholder }}"
                    id="navbarSearchInput"
                >
                <button class="btn btn-primary" type="button" id="navbarSearchBtn"><span class="bi bi-search"></span></button>
            </div>
        </div>

        <div class="action-buttons">
            <div class="dropdown-wrapper">
                <button class="btn-filter" id="filterBtn">
                    <i class="bi bi-funnel"></i>
                    <span id="filterBtnText">Filter</span> 
                </button>
                
                <div id="filterDropdown" class="custom-dropdown-menu d-none">
                    <div class="dropdown-item active" data-value="">Semua Status</div>
                    <div class="dropdown-item" data-value="InProgress">In Progress</div>
                    <div class="dropdown-item" data-value="Completed">Completed</div>
                    <div class="dropdown-item" data-value="OnHold">On Hold</div>
                </div>
            </div>

            <button class="btn-sort" id="sortBtn">
                <i class="bi bi-arrow-down-up"></i>
                <span>Sort By</span>
            </button>
        </div>
    </div>
    @endif
</nav>

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @vite(['resources/css/NavbarSearchFilter.css'])
@endpush