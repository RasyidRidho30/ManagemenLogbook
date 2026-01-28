<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jobs - {{ $projek->pjk_nama }}</title>

    {{-- HUBUNGKAN CSS --}}
    @vite(['resources/css/app.css', 'resources/css/NavbarSearchFilter.css', 'resources/css/Sidebar.css', 'resources/css/Jobs.css', 'resources/js/Projek/jobs.js'])
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
    {{-- Navbar --}}
    @include('components.NavbarSearchFilter', [
        'title' => 'Logbook Management System',
        'showSearchFilter' => false,
        'userName' => auth()->user()->name ?? 'Guest',
        'userRole' => auth()->user()->role ?? 'No Role'
    ])

    {{-- Sidebar --}}
    <x-Sidebar :projectId="$projectId" activeMenu="jobs" />

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            {{-- Main Add Module Button --}}
            @if(count($moduls) > 0)
            <button class="btn-add-modul shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddModul">
                <i class="bi bi-plus-lg"></i> Add Module
            </button>
            @endif

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/projek" class="text-decoration-none">Project</a></li>
                    <li class="breadcrumb-item active">Jobs</li>
                </ol>
            </nav> 
        </div>

        {{-- CONDITION IF MODULE IS EMPTY --}}
        @if(count($moduls) === 0)
            <div class="card border-0 shadow-sm rounded-3 py-5">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-folder-plus text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                    </div>
                    <h4 class="fw-bold text-dark">No Modules Yet</h4>
                    <p class="text-muted mb-4">This project does not have a module structure yet. Please add your first module to start managing tasks.</p>
                    <button class="btn btn-primary px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddModul">
                        <i class="bi bi-plus-lg me-2"></i> Add Module Now
                    </button>
                </div>
            </div>
        @else

            {{-- Module & Jobs Section --}}
            @foreach($moduls as $index => $modul)
            <div class="card mb-4 border shadow-sm rounded-3">
                <div class="card-header card-header-dark py-3 d-flex justify-content-between align-items-center">
                    <span>MODULE {{ $index + 1 }}: {{ strtoupper($modul->mdl_nama) }}</span>
                </div>
                <div class="card-body p-4">
                    @foreach($modul->kegiatans as $kegiatan)
                    <div class="kegiatan-wrapper mb-4">
                        <div class="bg-kegiatan d-flex justify-content-between align-items-center shadow-sm">
                            <span class="fw-bold">{{ $kegiatan->kgt_nama }}</span>
                            <i class="bi bi-caret-up-fill"></i>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mt-2">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">NO</th>
                                        <th>TASK</th>
                                        <th>START</th>
                                        <th>END</th>
                                        <th class="text-center">WEIGHT</th>
                                        <th class="text-center">PROGRESS</th>
                                        <th>CODE</th>
                                        <th>PIC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kegiatan->tugas as $tidx => $tgs)
                                        <tr onclick="window.openModalEditTugas({{ $tgs->tgs_id }})" style="cursor: pointer;">
                                            <td>{{ $tidx + 1 }}</td>
                                            <td class="fw-medium">{{ $tgs->tgs_nama }}</td>
                                            <td>{{ date('d/m/Y', strtotime($tgs->tgs_tanggal_mulai)) }}</td>
                                            <td>{{ date('d/m/Y', strtotime($tgs->tgs_tanggal_selesai)) }}</td>
                                            <td class="text-center">{{ $tgs->tgs_bobot }}</td>
                                            <td class="text-center">
                                                <span class="{{ $tgs->tgs_status == 'Selesai' ? 'badge bg-success' : 'badge bg-secondary' }}">
                                                    {{ number_format($tgs->tgs_persentasi_progress, 0) }}% {{ $tgs->tgs_status }}
                                                </span>
                                            </td>
                                            <td class="text-muted small">{{ $tgs->tgs_kode_prefix }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $tgs->pic_name }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center py-4 text-muted">No tasks available.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{-- Add Task Button --}}
                        <button class="btn btn-link btn-sm text-decoration-none p-0 mt-2 text-muted" 
                                onclick="openModalTugas({{ $kegiatan->kgt_id }}, '{{ $kegiatan->kgt_nama }}')">
                            <i class="bi bi-plus-circle"></i> Add New Task
                        </button>
                    </div>
                    @endforeach

                    {{-- Add Activity Button --}}
                    <button class="btn-add-kegiatan w-100 mt-2" 
                            onclick="openModalKegiatan({{ $modul->mdl_id }}, '{{ $modul->mdl_nama }}')">
                        <i class="bi bi-plus-lg"></i> Add New Activity
                    </button>
                </div>
            </div>
            @endforeach
        @endif
    </main>

    {{-- ADD MODULE MODAL --}}
    <div class="modal fade" id="modalAddModul" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formAddModul" class="modal-content border-white">
                <div class="modal-header card-header-dark">
                    <h5 class="modal-title">Add New Module</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="pjk_id" value="{{ $projectId }}">
                    <div class="mb-3">
                        <label class="form-label">Module Name</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sequence (No)</label>
                        <input type="number" name="urut" class="form-control" value="{{ count($moduls) + 1 }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Save Module</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ADD ACTIVITY MODAL --}}
    <div class="modal fade" id="modalAddKegiatan" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formAddKegiatan" class="modal-content border-white">
                <div class="modal-header text-white card-header-dark">
                    <h5 class="modal-title">Add Activity (<span id="title_mdl"></span>)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="mdl_id" id="input_mdl_id">
                    <div class="mb-3">
                        <label class="form-label">Activity Name</label>
                        <input type="text" name="nama" class="form-control" placeholder="Example: Requirement Analysis" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Save Activity</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ADD TASK MODAL --}}
    <div class="modal fade" id="modalAddTugas" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="formAddTugas" class="modal-content border-white">
                <div class="modal-header card-header-dark">
                    <h5 class="modal-title">Add Task (<span id="title_kgt"></span>)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="kgt_id" id="input_kgt_id">
                    <div class="row">
                        <div class="col-md-12 mb-3"> 
                            <label class="form-label">Task Name</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="tgl_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="tgl_selesai" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Weight</label>
                            <input type="number" name="bobot" class="form-control" min="1" max="100" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">PIC (User)</label>
                            <select name="usr_id" class="form-select" required id="select_pic">
                                <option value="">Select PIC...</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Save Task</button>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT TASK MODAL --}}
    <div class="modal fade" id="modalEditTugas" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="formEditTugas" class="modal-content border-white">
                <div class="modal-header card-header-dark">
                    <h5 class="modal-title fw-bold">Edit Task: <span id="display_edit_tgs_nama"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_tgs_id">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Task Name</label>
                            <input type="text" id="edit_nama" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Code</label>
                            <input type="text" id="edit_kode" class="form-control" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">PIC (Project Member)</label>
                            <select id="edit_usr_id" class="form-select" required></select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">Weight</label>
                            <input type="number" id="edit_bobot" class="form-control" min="1" max="100" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">Progress (%)</label>
                            <input type="number" id="edit_progress" class="form-control" min="0" max="100" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" id="edit_tgl_selesai" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger" id="btnHapusTugas">
                        <i class="bi bi-trash"></i> Delete Task
                    </button>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
        
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>