<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Project - {{ $projectId }}</title>

    @vite(['resources/css/app.css', 'resources/css/NavbarSearchFilter.css', 'resources/css/Sidebar.css', 'resources/js/Projek/edit-projek.js', 'resources/css/EditProjek.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    @include('components.NavbarSearchFilter', [
        'title' => 'Project Settings',
        'showSearchFilter' => false,
        'userName' => auth()->user()->name ?? 'User'
    ])

    <x-Sidebar :projectId="$projectId" activeMenu="edit" />

    <main class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Edit Project Details</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/projek" class="text-decoration-none">Project</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav> 
            </div>

            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card border shadow-sm rounded-3 h-100">
                        <div class="card-header card-header-dark py-3">
                            Primary Information
                        </div>
                        <div class="card-body p-4">
                            <form id="formEditProjek">
                                <div class="mb-3">
                                    <label class="form-label">Project Name</label>
                                    <input type="text" id="pjk_nama" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea id="pjk_deskripsi" class="form-control" rows="4"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Project PIC (External)</label>
                                        <input type="text" id="pjk_pic" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Project Status</label>
                                        <select id="pjk_status" class="form-select">
                                            <option value="InProgress">In Progress</option>
                                            <option value="Completed">Completed</option>
                                            <option value="OnHold">On Hold</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" id="pjk_tgl_mulai" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" id="pjk_tgl_selesai" class="form-control" required>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" id="btnHapusProjek" class="btn btn-outline-danger px-4">
                                        <i class="bi bi-trash me-2"></i>Delete Project
                                    </button>
                                    <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card border shadow-sm rounded-3 h-100">
                        <div class="card-header card-header-dark py-3 d-flex justify-content-between align-items-center">
                            <span>Team Members</span>
                            <span class="badge bg-light text-dark" id="memberCount">0</span>
                        </div>
                        <div class="card-body p-0">
                            <div id="teamMembersList" class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                                <div class="text-center p-4 text-muted">Loading members...</div>
                            </div>
                        </div>
                        <div class="card-footer bg-white p-3 border-top">
                            <button type="button" class="btn btn-outline-primary w-100 dashed-border" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                                <i class="bi bi-plus-lg me-2"></i>Add Team Member
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 overflow-hidden">
                <div class="modal-header modal-header-dark p-3">
                    <h5 class="modal-title fw-bold text-white">Select New Member</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" id="searchUser" class="form-control border-start-0 ps-2" placeholder="Search by name...">
                        <button class="btn btn-primary" type="button" id="btnSearchTrigger">Search</button>
                    </div>

                    <div class="d-flex justify-content-between text-muted small mb-2 px-2">
                        <span>Nama</span>
                    </div>

                    <div id="userSelectionList" class="user-list-container mb-4">
                        <div class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
                            <div>Loading users...</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark-blue">Role in Project</label>
                        <input type="text" id="inputRole" class="form-control py-2" placeholder="e.g. Frontend Dev, QA, UI Designer">
                    </div>

                    <div class="d-grid">
                        <button type="button" id="btnSubmitAddMember" class="btn btn-primary py-2 fw-bold" disabled>
                            Add Member
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editMemberModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header modal-header-dark p-3">
                    <h5 class="modal-title fw-bold text-white">Edit Member Role</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formEditMember">
                        <input type="hidden" id="editMemberId">

                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Member Name</label>
                            <input type="text" id="editMemberName" class="form-control bg-light" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark-blue">Role in Project</label>
                            <input type="text" id="editMemberRole" class="form-control" required placeholder="e.g. Backend Dev">
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary fw-bold">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    @vite(['resources/js/Projek/edit-projek.js']) 
</body>
</html>