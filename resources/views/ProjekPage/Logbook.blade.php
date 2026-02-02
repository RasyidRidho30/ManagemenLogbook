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
                                            <span class="{{ $log->tgs_status == 'Selesai' ? 'badge bg-success' : 'badge bg-secondary' }}">
                                                {{ number_format($log->tgs_persentasi_progress, 0) }}%
                                            </span>
                                        </td>
                                        <td class="text-muted small">{{ $log->tgs_kode_prefix }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $log->pic_name }}</span></td>
                                        <td>
                                            <small class="text-muted" title="{{ $log->lbk_deskripsi }}">
                                                {{ Str::limit($log->lbk_deskripsi, 50) }}
                                            </small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
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


    {{-- ADD LOGBOOK MODAL --}}
    <div class="modal fade" id="modalAddLogbook" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formAddLogbook" class="modal-content border-white">
                <div class="modal-header card-header-dark">
                    <h5 class="modal-title">Add Logbook Entry</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="pjk_id" value="{{ $projectId }}">

                    {{-- 1. INPUT TANGGAL --}}
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="lbk_tanggal" id="lbk_tanggal" class="form-control" value="{{ date('Y-m-d') }}" required readonly>
                    </div>

                    {{-- 2. DROPDOWN KEGIATAN (TUGAS) --}}
                    <div class="mb-3">
                        <label class="form-label">Task</label>
                        <select name="tgs_id" id="tgs_id" class="form-select" required>
                            <option value="">-- Select Task --</option>
                            {{-- Variabel $tugas berisi hasil query daftarTugas dari controller --}}
                            @foreach($tugas as $t)
                                <option value="{{ $t->tgs_id }}">
                                    [{{ $t->tgs_kode_prefix }}] {{ $t->tgs_nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. INPUT DESKRIPSI (KETERANGAN) --}}
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="lbk_deskripsi" id="lbk_deskripsi" class="form-control" rows="3" placeholder="What did you work on?" required></textarea>
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

    {{-- Script Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>