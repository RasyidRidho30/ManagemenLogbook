<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activity Plan - {{ $projek->pjk_nama ?? 'Project' }}</title>
    @vite(['resources/css/app.css', 'resources/css/NavbarSearchFilter.css', 'resources/css/Sidebar.css', 'resources/css/List.css', 'resources/js/Projek/list.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        
        .progress-fill,
        .progress-text { pointer-events: none !important; }

        
        .page-header-wrap {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.25rem;
        }
        .page-title-group h2 {
            font-size: 1.55rem;
            font-weight: 800;
            color: #0f2d45;
            margin: 0;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .page-title-group .project-subtitle {
            font-size: 0.92rem;
            color: #5c7389;
            margin-top: 3px;
            font-weight: 400;
        }
        .page-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        
        #btnToggleList {
            display: flex;
            align-items: center;
            gap: 6px;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            transition: all 0.2s ease;
            border-width: 1.5px;
        }
        #btnToggleList:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        #btnToggleList i {
            font-size: 1rem;
        }

        
        .breadcrumb {
            font-size: 0.8rem;
            margin-bottom: 0;
        }
        .breadcrumb-item a {
            color: #5c7389;
            text-decoration: none;
            font-weight: 500;
        }
        .breadcrumb-item a:hover { color: #0f2d45; }
        .breadcrumb-item.active { color: #0f2d45; font-weight: 600; }
        .breadcrumb-item + .breadcrumb-item::before { color: #b0bec9; }

        
        .activity-container {
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            border: 1px solid #dde3ea !important;
        }

        
        .module-row {
            background: linear-gradient(90deg, #0f2d45 0%, #143752 100%) !important;
            color: white !important;
            position: relative;
        }
        .module-row::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #ff7d00;
        }

        
        .kegiatan-row {
            background-color: #e8f3fc !important;
            color: #1a5276 !important;
        }

        
        @media (max-width: 480px) {
            #textToggle { display: none !important; }
            #btnToggleList { padding: 7px 10px; }
            .page-title-group h2 { font-size: 1.1rem; }
            .page-title-group .project-subtitle { font-size: 0.78rem; }
        }
    </style>
</head>
<body>

@include('components.NavbarSearchFilter', [
    'title'            => 'Logbook Management System',
    'showSearchFilter' => false,
    'userName'         => auth()->user()->name ?? 'Guest',
    'userRole'         => auth()->user()->role ?? 'No Role'
])

<x-Sidebar :projectId="$projectId ?? null" activeMenu="list" />

<main class="main-content">    <div class="page-header-wrap">
        <div class="page-title-group">
            <h2>Activity Plan</h2>
            <div class="project-subtitle">
                <i class="bi bi-folder2-open me-1"></i>{{ $projek->pjk_nama ?? '' }}
            </div>
        </div>

        <div class="page-actions">            <button id="btnToggleList"
                    class="btn btn-outline-primary fw-bold shadow-sm"
                    title="Toggle Task List">
                <i class="bi bi-layout-sidebar-reverse" id="iconToggle"></i>
                <span id="textToggle">Hide List</span>
            </button>            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/projek"><i class="bi bi-house-door me-1"></i>Project</a>
                    </li>
                    <li class="breadcrumb-item active">Activity Plan</li>
                </ol>
            </nav>
        </div>
    </div>    <div class="activity-container bg-white">
        <div class="activity-wrap" id="activityWrap">            <div class="activity-left border-end" id="activityLeft">                <div class="activity-left-head">
                    <div class="col-task ps-3">Task Description</div>
                    <div class="col-pic pe-3 text-end">PIC</div>
                </div>                <div class="activity-list">
                    @foreach($moduls ?? [] as $modIndex => $mod)                        <div class="row-item module-row bg-dark-blue text-white">
                            <div class="d-flex align-items-center h-100 ps-3 fw-bold text-truncate"
                                 style="font-size: 0.82rem; letter-spacing: 0.03em;">
                                <i class="bi bi-layers-half me-2 opacity-75"></i>
                                MODULE {{ $modIndex + 1 }}: {{ strtoupper($mod->mdl_nama) }}
                            </div>
                        </div>

                        @foreach($mod->kegiatans ?? [] as $kgt)                            <div class="row-item kegiatan-row bg-light-blue text-primary">
                                <div class="d-flex align-items-center h-100 ps-3 fw-semibold text-truncate"
                                     style="font-size: 0.82rem;">
                                    <i class="bi bi-chevron-right me-1 opacity-50" style="font-size: 0.65rem;"></i>
                                    {{ $kgt->kgt_nama }}
                                </div>
                            </div>                            @foreach($kgt->tugas as $t)
                                <div class="row-item task-row js-task-click"
                                     data-target="bar-{{ $t->tgs_id }}">
                                    <div class="col-task d-flex align-items-center h-100 ps-4 text-dark gap-1"
                                         title="{{ $t->tgs_nama }}"
                                         style="font-size: 0.82rem;">
                                        <span class="text-muted me-1" style="opacity: 0.4;">—</span>
                                        {{ $t->tgs_nama }}
                                    </div>
                                    <div class="col-pic d-flex align-items-center justify-content-end h-100 pe-3">
                                        <span class="badge rounded-pill bg-light text-dark border text-truncate"
                                              style="max-width: 100%; font-size: 0.7rem;">
                                            {{ $t->pic_name ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach

                        @endforeach
                    @endforeach
                </div>
            </div>            <div class="activity-right">
                <div class="timeline-scroll-container" id="timelineScroll">
                    <div class="timeline-content" id="timelineContent"
                         data-pstart="{{ $projek->pjk_tanggal_mulai ?? '' }}"
                         data-pend="{{ $projek->pjk_tanggal_selesai ?? '' }}">                        <div class="timeline-header">
                            <div class="date-scale" id="dateScale"></div>
                        </div>                        <div class="timeline-body" id="timelineBody">                            <div class="today-marker" id="todayMarker" style="display:none;">
                                <div class="label">Today</div>
                                <div class="line"></div>
                            </div>                            @foreach($moduls ?? [] as $modIndex => $mod)                                <div class="row-item spacer-row module-spacer position-relative row-modul-timeline">
                                    <div class="inline-title-collapse text-white fw-bold ms-3"
                                         style="position: sticky; left: 15px; z-index: 10; font-size: 0.82rem; letter-spacing: 0.03em;">
                                        <i class="bi bi-layers-half me-2 opacity-75"></i>
                                        MODULE {{ $modIndex + 1 }}: {{ strtoupper($mod->mdl_nama) }}
                                    </div>
                                </div>

                                @foreach($mod->kegiatans ?? [] as $kgt)                                    <div class="row-item spacer-row kegiatan-spacer position-relative row-kegiatan-timeline">
                                        <div class="inline-title-collapse text-primary fw-semibold ms-4"
                                             style="position: sticky; left: 15px; z-index: 10; font-size: 0.82rem;">
                                            <i class="bi bi-chevron-right me-1 opacity-50" style="font-size: 0.65rem;"></i>
                                            {{ $kgt->kgt_nama }}
                                        </div>
                                    </div>                                    @foreach($kgt->tugas as $t)
                                        <div class="row-item timeline-row position-relative"
                                             data-start="{{ $t->tgs_tanggal_mulai }}"
                                             data-end="{{ $t->tgs_tanggal_selesai }}"
                                             data-progress="{{ $t->tgs_persentasi_progress ?? 0 }}">

                                            <div class="bar-container w-100 position-relative h-100">
                                                <div class="duration-bar"
                                                     id="bar-{{ $t->tgs_id }}">

                                                    <div class="progress-fill"></div>

                                                    <div class="progress-text px-2">
                                                        {{ intval($t->tgs_persentasi_progress ?? 0) }}%
                                                    </div>

                                                    <div class="inline-task-collapse">
                                                        <span class="badge bg-secondary shadow-sm">{{ $t->pic_name ?? '-' }}</span>
                                                        <span class="fw-bold text-dark text-nowrap" style="font-size: 0.78rem;">
                                                            {{ $t->tgs_nama }}
                                                        </span>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                @endforeach
                            @endforeach

                        </div>                    </div>                </div>            </div>
        </div>    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.addEventListener('load', function () {

        
        
        
        
        
        const popup = document.createElement('div');
        popup.id = 'tapPopup';
        popup.style.cssText = [
            'display:none',
            'position:fixed',
            'z-index:999999',
            'background:#0f2d45',
            'color:#fff',
            'border-radius:10px',
            'padding:10px 14px',
            'font-size:0.82rem',
            'font-family:DM Sans,sans-serif',
            'box-shadow:0 6px 24px rgba(0,0,0,0.35)',
            'max-width:240px',
            'pointer-events:none',
            'line-height:1.5',
            'border-left:3px solid #ff7d00',
        ].join(';');
        document.body.appendChild(popup);

        let popupTimer = null;

        function showTapPopup(bar, clientX, clientY) {
            const name     = bar.dataset.taskName  || '';
            const start    = bar.dataset.taskStart || '';
            const end      = bar.dataset.taskEnd   || '';
            const progress = bar.dataset.taskProgress || '0';

            popup.innerHTML =
                '<div style="font-weight:700;margin-bottom:4px;">' + name + '</div>' +
                '<div style="font-size:0.75rem;opacity:0.85;">' +
                    '<i class="bi bi-calendar-event" style="margin-right:4px;"></i>' +
                    start + ' &rarr; ' + end +
                '</div>' +
                '<div style="font-size:0.72rem;margin-top:4px;opacity:0.7;">' +
                    'Progress: <strong>' + progress + '%</strong>' +
                '</div>';

            
            const pw = 240, ph = 80;
            let left = clientX - pw / 2;
            let top  = clientY - ph - 14;
            if (left < 8) left = 8;
            if (left + pw > window.innerWidth - 8) left = window.innerWidth - pw - 8;
            if (top < 8) top = clientY + 14;

            popup.style.left    = left + 'px';
            popup.style.top     = top  + 'px';
            popup.style.display = 'block';

            clearTimeout(popupTimer);
            popupTimer = setTimeout(function () {
                popup.style.display = 'none';
            }, 3000);
        }

        
        function attachBarTapListeners() {
            document.querySelectorAll('.duration-bar').forEach(function (bar) {
                var nameEl   = bar.querySelector('.inline-task-collapse .fw-bold');
                var taskName = nameEl ? nameEl.textContent.trim() : bar.id.replace('bar-', 'Task ');

                bar.dataset.taskName     = taskName;
                bar.dataset.taskStart    = bar.closest('.timeline-row')
                    ? bar.closest('.timeline-row').dataset.start
                        ? formatDate(bar.closest('.timeline-row').dataset.start)
                        : ''
                    : '';
                bar.dataset.taskEnd      = bar.closest('.timeline-row')
                    ? bar.closest('.timeline-row').dataset.end
                        ? formatDate(bar.closest('.timeline-row').dataset.end)
                        : ''
                    : '';
                bar.dataset.taskProgress = bar.closest('.timeline-row')
                    ? (bar.closest('.timeline-row').dataset.progress || '0')
                    : '0';

                bar.addEventListener('touchstart', function (e) {
                    e.stopPropagation();
                    var touch = e.touches[0];
                    showTapPopup(bar, touch.clientX, touch.clientY);
                }, { passive: true });

                
                bar.addEventListener('click', function (e) {
                    if ('ontouchstart' in window) return; 
                    showTapPopup(bar, e.clientX, e.clientY);
                });
            });
        }

        function formatDate(dateStr) {
            if (!dateStr) return '';
            var d = new Date(dateStr);
            if (isNaN(d)) return dateStr;
            var months = ['Jan','Feb','Mar','Apr','May','Jun',
                          'Jul','Aug','Sep','Oct','Nov','Dec'];
            return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
        }

        
        document.addEventListener('touchstart', function (e) {
            if (!e.target.closest('.duration-bar')) {
                popup.style.display = 'none';
            }
        }, { passive: true });

        
        setTimeout(attachBarTapListeners, 800);

        
        
        
        const btnToggle    = document.getElementById('btnToggleList');
        const activityWrap = document.getElementById('activityWrap');
        const iconToggle   = document.getElementById('iconToggle');
        const textToggle   = document.getElementById('textToggle');

        
        function applyMobileCollapse() {
            if (window.innerWidth <= 600) {
                if (!activityWrap.classList.contains('is-collapsed')) {
                    activityWrap.classList.add('is-collapsed');
                    if (textToggle) textToggle.innerText = 'Show List';
                    if (iconToggle) iconToggle.className = 'bi bi-layout-sidebar';
                    if (btnToggle)  btnToggle.classList.replace('btn-outline-primary', 'btn-primary');
                }
            }
        }
        applyMobileCollapse();
        window.addEventListener('resize', applyMobileCollapse);

        if (btnToggle) {
            btnToggle.addEventListener('click', function () {
                activityWrap.classList.toggle('is-collapsed');

                if (activityWrap.classList.contains('is-collapsed')) {
                    textToggle.innerText  = 'Show List';
                    iconToggle.className  = 'bi bi-layout-sidebar';
                    btnToggle.classList.replace('btn-outline-primary', 'btn-primary');
                } else {
                    textToggle.innerText  = 'Hide List';
                    iconToggle.className  = 'bi bi-layout-sidebar-reverse';
                    btnToggle.classList.replace('btn-primary', 'btn-outline-primary');
                }
            });
        }

    });
</script>
</body>
</html>