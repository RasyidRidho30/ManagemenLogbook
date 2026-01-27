@props([
    'projectId' => null, 
    'activeMenu' => 'beranda' // Opsi: beranda, list, jobs, logbook, edit
])

@vite(['resources/css/Sidebar.css', 'resources/js/app.css'])

<aside class="project-sidebar">
    <nav class="sidebar-nav">
        
        {{-- 1. Beranda --}}
        <a href="/projek/{{ $projectId }}/dashboard" 
           class="sidebar-item {{ $activeMenu === 'beranda' ? 'active' : '' }}">
            <div class="icon-wrapper">
                <i class="bi bi-grid-fill"></i>
            </div>
            <span class="label">Beranda</span>
        </a>

        {{-- 2. List --}}
        <a href="/projek/{{ $projectId }}/list" 
           class="sidebar-item {{ $activeMenu === 'list' ? 'active' : '' }}">
            <div class="icon-wrapper">
                <i class="bi bi-calendar-event"></i>
            </div>
            <span class="label">List</span>
        </a>

        {{-- 3. Jobs --}}
        <a href="/projek/{{ $projectId }}/jobs" 
           class="sidebar-item {{ $activeMenu === 'jobs' ? 'active' : '' }}">
            <div class="icon-wrapper">
                <i class="bi bi-clipboard-check"></i>
            </div>
            <span class="label">Jobs</span>
        </a>

        {{-- 4. Logbook --}}
        <a href="/projek/{{ $projectId }}/logbook" 
           class="sidebar-item {{ $activeMenu === 'logbook' ? 'active' : '' }}">
            <div class="icon-wrapper">
                <i class="bi bi-journal-text"></i>
            </div>
            <span class="label">Logbook</span>
        </a>

        {{-- 5. Edit Projek --}}
        <a href="/projek/{{ $projectId }}/edit" 
           class="sidebar-item {{ $activeMenu === 'edit' ? 'active' : '' }}">
            <div class="icon-wrapper">
                <i class="bi bi-pencil-square"></i>
            </div>
            <span class="label">Edit</span>
        </a>

    

        {{-- 6. Invite Team --}}
        <a href="/projek/{{ $projectId }}/edit" 
           class="sidebar-item {{ $activeMenu === 'edit' ? 'active' : '' }}">
            <div class="icon-wrapper">
                <i class="bi bi-people"></i>
            </div>
            <span class="label text-center">Kelola<br>Team</span>
        </a>


    </nav>
</aside>