<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logbook - {{ $projek->pjk_nama }}</title>

    @vite(['resources/css/app.css', 'resources/css/NavbarSearchFilter.css', 'resources/css/Sidebar.css', 'resources/css/Logbook.css', 'resources/js/Projek/logbook.js'])
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    
    @include('components.NavbarSearchFilter', [
        'title' => 'Manajemen Projek',
        'showSearchFilter' => false, 
        'searchPlaceholder' => 'Cari Logbook...',
        'userName' => auth()->user()->name ?? 'Guest',
        'userRole' => auth()->user()->role ?? 'No Role'
    ])

    <x-Sidebar :projectId="$projectId" activeMenu="logbook" />

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            @if(count($logbooks) === 0 && count($tugas) > 0)
            <button class="btn-add-modul shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddLogbook">
                <i class="bi bi-plus-lg"></i> Add Logbook Entry
            </button>
            @endif
            <h3 class="fw-bold mb-0">Logbook - {{ $projek->pjk_nama ?? '' }}</h3>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/projek" class="text-decoration-none">Project</a></li>
                    <li class="breadcrumb-item active">Logbook</li>
                </ol>
            </nav> 
        </div>

        @if(count($logbooks) === 0)
            <div class="card border-0 shadow-sm rounded-3 py-5">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-book text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                    </div>
                    <h4 class="fw-bold text-dark">No Logbook Entries Yet</h4>
                    <p class="text-muted mb-4">Start documenting your work by adding your first logbook entry.</p>
                    <button class="btn btn-primary px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddLogbook">
                        <i class="bi bi-plus-lg me-2"></i> Add Logbook Entry Now
                    </button>
                </div>
            </div>
        @else
            <div class="card border shadow-sm rounded-3">
                <div class="card-header card-header-dark py-3 px-4">
                    <h6 class="mb-0 fw-bold">Logbook Entries</h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">NO</th>
                                    <th>DATE</th>
                                    <th>TASK</th>
                                    <th>START</th>
                                    <th>END</th>
                                    <th class="text-center">PROGRESS</th>
                                    <th>CODE</th>
                                    <th>PIC</th>
                                    <th>DESCRIPTION</th>
                                    <th width="50">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logbooks as $index => $log)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($log->lbk_tanggal)->format('d/m/Y') }}</td>
                                        <td class="fw-medium">{{ $log->tgs_nama }}</td>
                                        <td>{{ \Carbon\Carbon::parse($log->tgs_tanggal_mulai)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($log->tgs_tanggal_selesai)->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            <span class="{{ ($log->lbk_progress ?? 0) >= 100 ? 'badge bg-success' : 'badge bg-secondary' }}">
                                                {{ number_format($log->lbk_progress ?? 0, 0) }}%
                                            </span>
                                        </td>
                                        <td class="text-muted small">{{ $log->tgs_kode_prefix }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $log->pic_name }}</span></td>
                                        <td>
                                            <small class="text-muted" title="{{ $log->lbk_deskripsi }}">
                                                {{ Str::limit($log->lbk_deskripsi, 50) }}
                                            </small>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#modalDetailLogbook"
                                                data-lbk-id="{{ $log->lbk_id }}"
                                                data-tanggal="{{ $log->lbk_tanggal }}"
                                                data-task="{{ $log->tgs_nama }}"
                                                data-kode="{{ $log->tgs_kode_prefix }}"
                                                data-deskripsi="{{ $log->lbk_deskripsi }}"
                                                data-progress="{{ $log->lbk_progress ?? 0 }}"
                                                data-komentar="{{ $log->lbk_komentar ?? '' }}"
                                                data-pic="{{ $log->pic_name }}"
                                                data-start="{{ $log->tgs_tanggal_mulai }}"
                                                data-end="{{ $log->tgs_tanggal_selesai }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            No logbook entries available.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <button class="btn-add-kegiatan w-100 mt-3" data-bs-toggle="modal" data-bs-target="#modalAddLogbook">
                        <i class="bi bi-plus-lg"></i> Add New Logbook Entry
                    </button>
                </div>
            </div>
        @endif
    </main>


    <div class="modal fade" id="modalAddLogbook" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formAddLogbook" class="modal-content border-white">
                <div class="modal-header card-header-dark">
                    <h5 class="modal-title">Add Logbook Entry</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="pjk_id" value="{{ $projectId }}">

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="lbk_tanggal" id="lbk_tanggal" class="form-control" value="{{ date('Y-m-d') }}" required readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Task</label>
                        <select name="tgs_id" id="tgs_id" class="form-select" required>
                            <option value="">-- Select Task --</option>
                            @foreach($tugas as $t)
                                <option value="{{ $t->tgs_id }}" data-progress="{{ $t->tgs_persentasi_progress ?? 0 }}">
                                    [{{ $t->tgs_kode_prefix }}] {{ $t->tgs_nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="lbk_deskripsi" id="lbk_deskripsi" class="form-control" rows="3" placeholder="What did you work on?" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Progress (%)</label>
                        <div class="input-group">
                            <input type="number" name="lbk_progress" id="lbk_progress" class="form-control" min="0" max="100" value="0">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Comment (Optional)</label>
                        <textarea name="lbk_komentar" id="lbk_komentar" class="form-control" rows="2" placeholder="Any additional comments?"></textarea>
                    </div>

                    <input type="hidden" name="urut" value="{{ count($logbooks) + 1 }}">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Save Entry</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalDetailLogbook" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-white">
                <div class="modal-header card-header-dark">
                    <h5 class="modal-title">Logbook Entry Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Date</label>
                            <p id="detail-tanggal" class="fw-medium">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Progress</label>
                            <p><span id="detail-progress" class="badge bg-secondary">0%</span></p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Task Code</label>
                        <p id="detail-kode" class="fw-medium">-</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Task Name</label>
                        <p id="detail-task" class="fw-medium">-</p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Start Date</label>
                            <p id="detail-start" class="fw-medium">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">End Date</label>
                            <p id="detail-end" class="fw-medium">-</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">PIC (Person In Charge)</label>
                        <p id="detail-pic" class="fw-medium"><span class="badge bg-light text-dark border">-</span></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Description</label>
                        <p id="detail-deskripsi" class="text-dark">-</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Comment</label>
                        <p id="detail-komentar" class="text-dark">
                            <em class="text-muted">-</em>
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" id="btnEditComment" data-bs-toggle="modal" data-bs-target="#modalEditComment">
                        <i class="bi bi-pencil"></i> Add/Edit Comment
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- EDIT COMMENT MODAL --}}
    <div class="modal fade" id="modalEditComment" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formEditComment" class="modal-content border-white">
                <div class="modal-header card-header-dark">
                    <h5 class="modal-title">Add/Edit Comment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="lbk_id_edit" name="lbk_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Comment</label>
                        <textarea id="komentarEdit" name="lbk_komentar" class="form-control" rows="4" placeholder="Add or edit your comment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Comment</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Script Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>