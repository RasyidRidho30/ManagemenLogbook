@props([
    'projectId' => null, 
    'activeMenu' => 'beranda' // Opsi: beranda, list, jobs, logbook, edit
])

@vite(['resources/css/Sidebar.css', 'resources/js/app.css'])

<aside class="project-sidebar">
    <nav class="sidebar-nav">
        
        <a href="/projek/{{ $projectId }}/dashboard" 
           class="sidebar-item {{ $activeMenu === 'beranda' ? 'active' : '' }}">
            <div class="icon-wrapper">
                <i class="bi bi-grid-fill"></i>
            </div>
            <span class="label">Dashboard</span>
        </a>

        <a href="/projek/{{ $projectId }}/list" 
           class="sidebar-item {{ $activeMenu === 'list' ? 'active' : '' }}">
            <div class="icon-wrapper">
                <i class="bi bi-calendar-event"></i>
            </div>
            <span class="label">List</span>
        </a>

        <a href="/projek/{{ $projectId }}/jobs" 
           class="sidebar-item {{ $activeMenu === 'jobs' ? 'active' : '' }}">
            <div class="icon-wrapper">
                <i class="bi bi-clipboard-check"></i>
            </div>
            <span class="label">Jobs</span>
        </a>

        <a href="/projek/{{ $projectId }}/logbook" 
           class="sidebar-item {{ $activeMenu === 'logbook' ? 'active' : '' }}">
            <div class="icon-wrapper">
                <i class="bi bi-journal-text"></i>
            </div>
            <span class="label">Logbook</span>
        </a>

        <a href="/projek/{{ $projectId }}/edit" 
           class="sidebar-item {{ $activeMenu === 'edit' ? 'active' : '' }}">
            <div class="icon-wrapper">
                <i class="bi bi-pencil-square"></i>
            </div>
            <span class="label">Edit</span>
        </a>
    </nav>
</aside>