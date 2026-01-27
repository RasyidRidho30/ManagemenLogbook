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
    @include('components.NavbarSearchFilter', [
        'title' => 'Sistem Manajemen Logbook',
        'showSearchFilter' => false,
        'userName' => auth()->user()->name ?? 'Guest',
        'userRole' => auth()->user()->role ?? 'No Role'
    ])

    <x-Sidebar :projectId="$projectId" activeMenu="jobs" />

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            {{-- Tombol Tambah Modul Utama --}}
            @if(count($moduls) > 0)
            <button class="btn-add-modul shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddModul">
                <i class="bi bi-plus-lg"></i> Tambah Modul
            </button>
            @endif

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/projek" class="text-decoration-none">Projek</a></li>
                    <li class="breadcrumb-item active">Jobs</li>
                </ol>
            </nav> 
        </div>

        {{-- KONDISI JIKA MODUL KOSONG --}}
        @if(count($moduls) === 0)
            <div class="card border-0 shadow-sm rounded-3 py-5">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-folder-plus text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                    </div>
                    <h4 class="fw-bold text-dark">Belum ada Modul</h4>
                    <p class="text-muted mb-4">Projek ini belum memiliki struktur modul. Silakan tambahkan modul pertama Anda untuk mulai mengelola tugas.</p>
                    <button class="btn btn-primary px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddModul">
                        <i class="bi bi-plus-lg me-2"></i> Tambahkan Modul Sekarang
                    </button>
                </div>
            </div>
        @else

            {{-- Section Modul & Jobs --}}
            @foreach($moduls as $index => $modul)
            <div class="card mb-4 border shadow-sm rounded-3">
                <div class="card-header card-header-dark py-3 d-flex justify-content-between align-items-center">
                    <span>MODUL {{ $index + 1 }}: {{ strtoupper($modul->mdl_nama) }}</span>
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
                                        <th>TUGAS</th>
                                        <th>MULAI</th>
                                        <th>SELESAI</th>
                                        <th class="text-center">BOBOT</th> {{-- Tambah ini --}}
                                        <th class="text-center">PROGRESS</th>
                                        <th>KODE</th>
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
                                            {{-- Tampilkan Bobot --}}
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
                                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada tugas.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{-- Tombol Tambah Tugas --}}
                        <button class="btn btn-link btn-sm text-decoration-none p-0 mt-2 text-muted" 
                                onclick="openModalTugas({{ $kegiatan->kgt_id }}, '{{ $kegiatan->kgt_nama }}')">
                            <i class="bi bi-plus-circle"></i> Tambah Tugas Baru
                        </button>
                    </div>
                    @endforeach

                    {{-- Tombol Tambah Kegiatan --}}
                    <button class="btn-add-kegiatan w-100 mt-2" 
                            onclick="openModalKegiatan({{ $modul->mdl_id }}, '{{ $modul->mdl_nama }}')">
                        <i class="bi bi-plus-lg"></i> Tambah Kegiatan Baru
                    </button>
                </div>
            </div>
            @endforeach
        @endif
    </main>

    {{-- MODAL TAMBAH MODUL --}}
    <div class="modal fade" id="modalAddModul" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formAddModul" class="modal-content border-white">
                <div class="modal-header card-header-dark">
                    <h5 class="modal-title">Tambah Modul Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="pjk_id" value="{{ $projectId }}">
                    <div class="mb-3">
                        <label class="form-label">Nama Modul</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Urutan (No)</label>
                        <input type="number" name="urut" class="form-control" value="{{ count($moduls) + 1 }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Simpan Modul</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TAMBAH KEGIATAN --}}
    <div class="modal fade" id="modalAddKegiatan" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formAddKegiatan" class="modal-content border-white">
                <div class="modal-header text-white card-header-dark">
                    <h5 class="modal-title">Tambah Kegiatan (<span id="title_mdl"></span>)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="mdl_id" id="input_mdl_id">
                    <div class="mb-3">
                        <label class="form-label">Nama Kegiatan</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Analisis Kebutuhan" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Simpan Kegiatan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TAMBAH TUGAS --}}
    <div class="modal fade" id="modalAddTugas" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="formAddTugas" class="modal-content border-white">
                <div class="modal-header card-header-dark">
                    <h5 class="modal-title">Tambah Tugas (<span id="title_kgt"></span>)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="kgt_id" id="input_kgt_id">
                    <div class="row">
                        <div class="col-md-12 mb-3"> 
                            <label class="form-label">Nama Tugas</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        {{-- INPUT KODE DIHAPUS --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bobot</label>
                            <input type="number" name="bobot" class="form-control" min="1" max="100" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">PIC (User)</label>
                            <select name="usr_id" class="form-select" required id="select_pic">
                                <option value="">Pilih PIC...</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Simpan Tugas</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT TUGAS --}}
    <div class="modal fade" id="modalEditTugas" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="formEditTugas" class="modal-content border-white">
                <div class="modal-header card-header-dark">
                    <h5 class="modal-title fw-bold">Edit Tugas: <span id="display_edit_tgs_nama"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_tgs_id">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Nama Tugas</label>
                            <input type="text" id="edit_nama" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Kode</label>
                            <input type="text" id="edit_kode" class="form-control" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">PIC (Anggota Projek)</label>
                            <select id="edit_usr_id" class="form-select" required></select>
                        </div>
                        <div class="col-md-2 mb-3"> {{-- Sesuaikan gridnya --}}
                            <label class="form-label fw-bold">Bobot</label>
                            <input type="number" id="edit_bobot" class="form-control" min="1" max="100" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">Progress (%)</label>
                            <input type="number" id="edit_progress" class="form-control" min="0" max="100" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">Selesai</label>
                            <input type="date" id="edit_tgl_selesai" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger" id="btnHapusTugas">
                        <i class="bi bi-trash"></i> Hapus Tugas
                    </button>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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