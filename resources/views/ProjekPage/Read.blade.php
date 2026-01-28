<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Project List - LogbookManagement</title>

 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @vite([ 'resources/js/app.js',
            'resources/css/app.css', 
            'resources/css/NavbarSearchFilter.css', 
            'resources/css/ProjekRead.css', 
            'resources/js/Projek/read-projek.js', 
            'resources/js/Components/navbar.js'
            ])
</head>
<body>

    @include('components.NavbarSearchFilter', [
        'logo' => 'Logo',
        'title' => 'Project Management',
        'userName' => auth()->check() ? auth()->user()->name : null,
        'userRole' => auth()->check() ? auth()->user()->role : null,
        'userAvatar' => auth()->check() ? auth()->user()->avatar : null,
        'searchPlaceholder' => 'Search Project...',
        'showNotificationBadge' => true,
        'notificationCount' => 3
    ])

    <div class="main-content py-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="h4 mb-0">Project List</h1>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary btn-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addProjekModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Project
                </button>
            </div>
        </div>

        <div class="card mb-4 border-0 shadow-sm d-none" id="filterCard">
            <div class="card-body">
                <div class="row g-2 align-items-center">
                    <div class="col-sm-5">
                        <input id="search" class="form-control" type="text" placeholder="Search project name">
                    </div>
                    <div class="col-sm-3">
                        <select id="status" class="form-select">
                            <option value="">All statuses</option>
                            <option value="InProgress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="OnHold">On Hold</option>
                        </select>
                    </div>
                    <div class="col-sm-4 d-flex gap-2">
                        <button id="searchBtn" type="button" class="btn btn-primary w-100">Search</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="projekContainer" class="row g-4">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        <div id="errorMsg" class="text-danger mt-2 text-center"></div>

    </div>

    <div class="modal fade" id="addProjekModal" tabindex="-1" aria-labelledby="addProjekModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="addProjekModalLabel">Create New Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4">
                    <form id="formAddProjek">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Project Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="nama" required placeholder="Project Name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Project PIC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="pic" required placeholder="PIC Name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Description</label>
                            <textarea class="form-control bg-light" id="deskripsi" rows="2" placeholder="Short details..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control bg-light" id="tgl_mulai" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control bg-light" id="tgl_selesai" required>
                            </div>
                        </div>
                        <div id="modalAlert"></div>
                    </form>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="formAddProjek" id="btnSubmit" class="btn btn-primary px-4">
                        <span id="btnText">Save</span>
                        <span id="btnLoader" class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @endpush
</body>
</html>