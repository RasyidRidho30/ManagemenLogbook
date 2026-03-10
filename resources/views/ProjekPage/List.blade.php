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
    /* 1. Mengamankan klik kursor menembus bar biru */
    .progress-fill, 
    .progress-text {
        pointer-events: none !important;
    }

    /* 2. Pengaturan Dasar Tooltip & Anti-Flicker */
    .tooltip {
        z-index: 99999999 !important;
        pointer-events: none !important;
        transition: opacity 0.15s linear !important;
    }

    /* 3. EFEK ANIMASI ZOOM/POP (Saat Masuk) */
    .tooltip.fade .tooltip-inner {
        transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
    }

    .tooltip.show .tooltip-inner {
        transform: scale(1) !important;
    }

    .tooltip.show {
        opacity: 0.9 !important;
        visibility: visible !important;
    }

    .tooltip-inner {
        max-width: 250px !important;
        padding: 0.4rem 0.8rem !important;
        color: #fff !important;
        text-align: center !important;
        background-color: #000 !important;
        border-radius: 0.375rem !important;
        font-size: 0.85rem !important;
        /* box-shadow: 0px 4px 10px rgba(0,0,0,0.2) !important; */
    }

    .bs-tooltip-top .tooltip-arrow::before,
    .bs-tooltip-auto[data-popper-placement^="top"] .tooltip-arrow::before {
        border-top-color: #000 !important;
    }
</style>
</head>
<body>

@include('components.NavbarSearchFilter', [
    'title' => 'Logbook Management System',
    'showSearchFilter' => false,
    'userName' => auth()->user()->name ?? 'Guest',
    'userRole' => auth()->user()->role ?? 'No Role'
])

<x-Sidebar :projectId="$projectId ?? null" activeMenu="list" />

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="activity-header d-flex align-items-baseline mb-3">
            <h2 class="fw-bold me-3">Activity Plan</h2>
            <h4 class="text-secondary">{{ $projek->pjk_nama ?? '' }}</h4>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/projek" class="text-decoration-none">Project</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav> 
    </div>
    
    <div class="activity-container shadow-sm border rounded-3 bg-white">
        <div class="activity-wrap">
            
            <div class="activity-left border-end">
                <div class="activity-left-head">
                    <div class="col-task ps-3 fw-bold">Task Description</div>
                    <div class="col-pic pe-3 text-end fw-bold">PIC</div>
                </div>

                <div class="activity-list">
                    @foreach($moduls ?? [] as $modIndex => $mod)
                        <div class="row-item module-row bg-dark-blue text-white">
                            <div class="d-flex align-items-center h-100 ps-3 fw-bold text-truncate">
                                MODULE {{ $modIndex + 1 }}: {{ strtoupper($mod->mdl_nama) }}
                            </div>
                        </div>

                        @foreach($mod->kegiatans ?? [] as $kgt)
                            <div class="row-item kegiatan-row bg-light-blue text-primary">
                                <div class="d-flex align-items-center h-100 ps-3 fw-semibold text-truncate">
                                    | {{ $kgt->kgt_nama }}
                                </div>
                            </div>
                            @foreach($kgt->tugas as $t)
                                <div class="row-item task-row js-task-click" data-target="bar-{{ $t->tgs_id }}">
                                    <div class="col-task d-flex align-items-center h-100 ps-4 text-dark" title="{{ $t->tgs_nama }}">
                                        | {{ $t->tgs_nama }}
                                    </div>
                                    <div class="col-pic d-flex align-items-center justify-content-end h-100 pe-3">
                                        <span class="badge rounded-pill bg-light text-dark border px-3 text-truncate" style="max-width: 100%;">
                                            {{ $t->pic_name ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    @endforeach
                </div>
            </div>

            <div class="activity-right">
                <div class="timeline-scroll-container" id="timelineScroll">
                    <div class="timeline-content" id="timelineContent"
                         data-pstart="{{ $projek->pjk_tanggal_mulai ?? '' }}"
                         data-pend="{{ $projek->pjk_tanggal_selesai ?? '' }}">
                        
                        <div class="timeline-header">
                            <div class="date-scale" id="dateScale"></div>
                        </div>

                        <div class="timeline-body" id="timelineBody">
                            <div class="today-marker" id="todayMarker" style="display:none;">
                                <div class="label">Today</div>
                                <div class="line"></div>
                            </div>

                            @foreach($moduls ?? [] as $mod)
                                <div class="row-item spacer-row module-spacer"></div>
                                @foreach($mod->kegiatans ?? [] as $kgt)
                                    <div class="row-item spacer-row kegiatan-spacer"></div>
                                    @foreach($kgt->tugas as $t)
                                        <div class="row-item timeline-row" 
                                             data-start="{{ $t->tgs_tanggal_mulai }}" 
                                             data-end="{{ $t->tgs_tanggal_selesai }}" 
                                             data-progress="{{ $t->tgs_persentasi_progress ?? 0 }}">
                                            
                                            <div class="bar-container w-100 position-relative h-100">
                                                <div class="duration-bar"
                                                     id="bar-{{ $t->tgs_id }}"
                                                     data-bs-toggle="tooltip"
                                                     data-bs-placement="top"
                                                     data-bs-html="true"
                                                     data-bs-container="body"
                                                     title="<b>{{ $t->tgs_nama }}</b><br>
                                                            {{ \Carbon\Carbon::parse($t->tgs_tanggal_mulai)->format('d M Y') }} - 
                                                            {{ \Carbon\Carbon::parse($t->tgs_tanggal_selesai)->format('d M Y') }}">
                                                    
                                                    <div class="progress-fill"></div>
                                                    
                                                    <div class="progress-text px-2">
                                                        {{ intval($t->tgs_persentasi_progress ?? 0) }}%
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.addEventListener('load', function() {
        setTimeout(function() {
            var tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl, {
                    container: 'body',
                    offset: [0, 8],
                });
            });
            console.log("SUKSES: " + tooltipTriggerList.length + " Tooltip berhasil dipasang tanpa kedip!");
        }, 500); 
    });
</script>
</body>
</html>