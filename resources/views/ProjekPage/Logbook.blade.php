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
    'title'             => 'Logbook Management',
    'showSearchFilter'  => false,
    'searchPlaceholder' => 'Cari Logbook...',
    'userName'          => auth()->user()->name ?? 'Guest',
    'userRole'          => auth()->user()->role ?? 'No Role'
])

<x-Sidebar :projectId="$projectId" activeMenu="logbook" />

<main class="main-content">

    {{-- ── Page Header ── --}}
    <div class="page-header-wrap">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            @if(count($logbooks) === 0 && count($tugas) > 0)
                <button class="btn-add-modul shadow-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#modalAddLogbook">
                    <i class="bi bi-plus-lg"></i> Add Logbook Entry
                </button>
            @endif
            <h3>
                <i class="bi bi-journal-text me-2 opacity-75" style="color: #ff7d00;"></i>
                Logbook — {{ $projek->pjk_nama ?? '' }}
            </h3>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="/projek"><i class="bi bi-house-door me-1"></i>Project</a>
                </li>
                <li class="breadcrumb-item active">Logbook</li>
            </ol>
        </nav>
    </div>

    {{-- ════════════════════════════════════════════
         EMPTY STATE
    ════════════════════════════════════════════ --}}
    @if(count($logbooks) === 0)
        <div class="card border-0 shadow-sm rounded-3 py-5">
            <div class="card-body text-center py-4">
                <i class="bi bi-book d-block mb-3" style="font-size: 3.5rem; color: #c8d8e8;"></i>
                <h4 class="fw-bold" style="color: #0f2d45;">No Logbook Entries Yet</h4>
                <p class="text-muted mb-4 mx-auto" style="max-width: 380px; font-size: 0.88rem;">
                    Start documenting your work by adding your first logbook entry.
                </p>
                <button class="btn btn-primary px-4 shadow-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#modalAddLogbook">
                    <i class="bi bi-plus-lg me-2"></i>Add Logbook Entry Now
                </button>
            </div>
        </div>

    @else
    {{-- ════════════════════════════════════════════
         LOGBOOK TABLE
    ════════════════════════════════════════════ --}}
    <div class="card">
        <div class="card-header card-header-dark py-3 px-4 d-flex align-items-center gap-2 text-light">
            <i class="bi bi-journal-richtext opacity-75"></i>
            <span>Logbook Entries</span>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="44">No</th>
                            <th>Date</th>
                            <th>Task</th>
                            <th>Start</th>
                            <th>End</th>
                            <th class="text-center">Progress</th>
                            <th>Code</th>
                            <th>PIC</th>
                            <th>Description</th>
                            <th width="50">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logbooks as $index => $log)
                            <tr>
                                <td class="text-muted" style="font-size: 0.76rem;">{{ $index + 1 }}</td>
                                <td style="white-space: nowrap; font-size: 0.8rem;">
                                    {{ \Carbon\Carbon::parse($log->lbk_tanggal)->format('d/m/Y') }}
                                </td>
                                <td class="fw-medium">{{ $log->tgs_nama }}</td>
                                <td style="white-space: nowrap; font-size: 0.8rem;">
                                    {{ \Carbon\Carbon::parse($log->tgs_tanggal_mulai)->format('d/m/Y') }}
                                </td>
                                <td style="white-space: nowrap; font-size: 0.8rem;">
                                    {{ \Carbon\Carbon::parse($log->tgs_tanggal_selesai)->format('d/m/Y') }}
                                </td>
                                <td class="text-center">
                                    <span class="{{ ($log->lbk_progress ?? 0) >= 100 ? 'badge bg-success' : 'badge bg-secondary' }}">
                                        {{ number_format($log->lbk_progress ?? 0, 0) }}%
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $log->tgs_kode_prefix }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $log->pic_name }}</span>
                                </td>
                                <td>
                                    <small class="text-muted" title="{{ $log->lbk_deskripsi }}">
                                        {{ Str::limit($log->lbk_deskripsi, 50) }}
                                    </small>
                                </td>
                                <td>
                                    {{-- Tombol View Detail --}}
                                    <button type="button"
                                            class="btn btn-sm btn-dark"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDetailLogbook"
                                            data-lbk-id="{{ $log->lbk_id }}"
                                            data-tgs-id="{{ $log->tgs_id }}"
                                            data-tanggal="{{ $log->lbk_tanggal }}"
                                            data-task="{{ $log->tgs_nama }}"
                                            data-kode="{{ $log->tgs_kode_prefix }}"
                                            data-deskripsi="{{ $log->lbk_deskripsi }}"
                                            data-progress="{{ $log->lbk_progress ?? 0 }}"
                                            data-komentar="{{ $log->lbk_komentar ?? '' }}"
                                            data-pic="{{ $log->pic_name }}"
                                            data-start="{{ $log->tgs_tanggal_mulai }}"
                                            data-end="{{ $log->tgs_tanggal_selesai }}"
                                            data-evidence-link="{{ $log->lbk_evidence_link ?? '' }}"
                                            data-is-today="{{ \Carbon\Carbon::parse($log->lbk_tanggal)->setTimezone(config('app.timezone'))->isToday() ? '1' : '0' }}"
                                            title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <span class="text-muted" style="font-size: 0.86rem;">No logbook entries available.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <button class="btn-add-kegiatan mt-3"
                    data-bs-toggle="modal"
                    data-bs-target="#modalAddLogbook">
                <i class="bi bi-plus-lg"></i> Add New Logbook Entry
            </button>
        </div>
    </div>
    @endif

</main>

{{-- ══════════════════════════════════════════════════
     MODAL: Add Logbook Entry
══════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalAddLogbook" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formAddLogbook" class="modal-content">
            <div class="modal-header card-header-dark">
                <h5 class="modal-title">
                    <i class="bi bi-journal-plus me-2 opacity-75"></i>Add Logbook Entry
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="pjk_id" value="{{ $projectId }}">

                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="lbk_tanggal" id="lbk_tanggal"
                           class="form-control" value="{{ date('Y-m-d') }}" required readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Task</label>
                    <select name="tgs_id" id="tgs_id" class="form-select" required>
                        <option value="">— Select Task —</option>
                        @foreach($tugas as $t)
                            <option value="{{ $t->tgs_id }}"
                                    data-progress="{{ $t->tgs_persentasi_progress ?? 0 }}">
                                [{{ $t->tgs_kode_prefix }}] {{ $t->tgs_nama }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1">
                        <i class="bi bi-info-circle me-1"></i>Hanya boleh 1 entry per task per hari. Jika task sudah di-log hari ini, edit entry tersebut; untuk task sama dapat ditambah lagi besok.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="lbk_deskripsi" id="lbk_deskripsi" class="form-control"
                              rows="3" placeholder="What did you work on?" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Progress (%)</label>
                    <div class="input-group">
                        <input type="number" name="lbk_progress" id="lbk_progress"
                               class="form-control" min="0" max="100" value="0">
                        <span class="input-group-text">%</span>
                    </div>
                    <div id="evidenceLinkGroup" class="mt-2 d-none">
                        <label class="form-label">Evidence Link (Drive)</label>
                        <input type="url" name="evidence_link" id="evidence_link" class="form-control"
                               placeholder="https://drive.google.com/..." />
                        <small class="text-muted">Optional, but recommended when progress reaches 100%.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Comment
                        <span class="text-muted fw-normal ms-1" style="text-transform: none; font-size: 0.72rem;">(Optional)</span>
                    </label>
                    <textarea name="lbk_komentar" id="lbk_komentar" class="form-control"
                              rows="2" placeholder="Any additional comments?"></textarea>
                </div>

                <input type="hidden" name="urut" value="{{ count($logbooks) + 1 }}">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check2-circle me-2"></i>Save Entry
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODAL: Detail Logbook
══════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDetailLogbook" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header card-header-dark">
                <h5 class="modal-title">
                    <i class="bi bi-journal-text me-2 opacity-75"></i>Logbook Entry Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Row: Date + Progress --}}
                <div class="row mb-3">
                    <div class="col-6">
                        <p class="detail-label">Date</p>
                        <p id="detail-tanggal" class="detail-value">—</p>
                    </div>
                    <div class="col-6">
                        <p class="detail-label">Progress</p>
                        <p><span id="detail-progress" class="badge bg-secondary">0%</span></p>
                    </div>
                </div>

                <hr class="detail-divider">

                {{-- Task Code --}}
                <div class="mb-3">
                    <p class="detail-label">Task Code</p>
                    <p id="detail-kode" class="detail-value code">—</p>
                </div>

                {{-- Task Name --}}
                <div class="mb-3">
                    <p class="detail-label">Task Name</p>
                    <p id="detail-task" class="detail-value">—</p>
                </div>

                {{-- Row: Start + End --}}
                <div class="row mb-3">
                    <div class="col-6">
                        <p class="detail-label">Start Date</p>
                        <p id="detail-start" class="detail-value">—</p>
                    </div>
                    <div class="col-6">
                        <p class="detail-label">End Date</p>
                        <p id="detail-end" class="detail-value">—</p>
                    </div>
                </div>

                <hr class="detail-divider">

                {{-- PIC --}}
                <div class="mb-3">
                    <p class="detail-label">PIC (Person In Charge)</p>
                    <p id="detail-pic" class="detail-value">
                        <span class="badge bg-light text-dark border">—</span>
                    </p>
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <p class="detail-label">Description</p>
                    <p id="detail-deskripsi" class="detail-value body-text">—</p>
                </div>

                {{-- Comment --}}
                <div class="mb-1">
                    <p class="detail-label">Comment</p>
                    <p id="detail-komentar" class="detail-value body-text">
                        <em class="text-muted">No comment yet.</em>
                    </p>
                </div>

                <div class="mb-1">
                    <p class="detail-label">Evidence Link</p>
                    <p id="detail-evidence" class="detail-value body-text">
                        <em class="text-muted">No evidence link provided.</em>
                    </p>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning"
                        id="btnEditComment"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditComment">
                    <i class="bi bi-pencil me-2"></i>Add / Edit Comment
                </button>

                {{-- Hanya muncul jika entry adalah hari ini --}}
                <button type="button" class="btn btn-primary d-none"
                        id="btnEditProgress"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditProgress">
                    <i class="bi bi-bar-chart-line me-2"></i>Edit Progress
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODAL: Edit Comment
══════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEditComment" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditComment" class="modal-content">
            <div class="modal-header card-header-dark">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2 opacity-75"></i>Add / Edit Comment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="lbk_id_edit" name="lbk_id">
                <div class="mb-3">
                    <label class="form-label">Comment</label>
                    <textarea id="komentarEdit" name="lbk_komentar" class="form-control"
                              rows="4" placeholder="Add or edit your comment..."></textarea>
                </div>
            </div>
            <div class="modal-footer d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle me-2"></i>Save Comment
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ══════════════════════════════════════════════════
     MODAL: Edit Progress
══════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEditProgress" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditProgress" class="modal-content">
            <div class="modal-header card-header-dark">
                <h5 class="modal-title">
                    <i class="bi bi-bar-chart-line me-2 opacity-75"></i>Edit Progress
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="lbk_id_progress" name="lbk_id">

                <div class="mb-2">
                    <label class="form-label d-flex justify-content-between">
                        <span>Progress</span>
                        <span class="fw-bold text-primary" id="progressDisplayLabel">0%</span>
                    </label>

                    {{-- Slider --}}
                    <input type="range"
                           id="progressSlider"
                           class="form-range mb-2"
                           min="0" max="100" step="1" value="0">

                    {{-- Input number --}}
                    <div class="input-group">
                        <input type="number"
                               id="progressInput"
                               class="form-control"
                               min="0" max="100" value="0">
                        <span class="input-group-text">%</span>
                    </div>

                    <div class="mt-3 d-none" id="editEvidenceGroup">
                        <label class="form-label">Evidence Link (Drive)</label>
                        <input type="url" id="editEvidenceLink" name="evidence_link" class="form-control"
                               placeholder="https://drive.google.com/..." />
                        <small class="text-muted">Optional, shown when progress is 100%.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle me-2"></i>Save Progress
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>