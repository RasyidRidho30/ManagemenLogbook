<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Projek - {{ $projectId }}</title>

    @vite(['resources/css/app.css', 'resources/css/NavbarSearchFilter.css', 'resources/css/Sidebar.css', 'resources/js/Projek/edit-projek.js', 'resources/css/EditProjek.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
    @include('components.NavbarSearchFilter', [
        'title' => 'Pengaturan Projek',
        'showSearchFilter' => false,
        'userName' => auth()->user()->name ?? 'User'
    ])

    <x-Sidebar :projectId="$projectId" activeMenu="edit" />

    <main class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Edit Detail Projek</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/projek" class="text-decoration-none">Projek</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav> 
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card border shadow-sm rounded-3">
                        <div class="card-header card-header-dark py-3">
                            Informasi Utama
                        </div>
                        <div class="card-body p-4">
                            <form id="formEditProjek">
                                <div class="mb-3">
                                    <label class="form-label">Nama Projek</label>
                                    <input type="text" id="pjk_nama" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea id="pjk_deskripsi" class="form-control" rows="4"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">PIC Projek (Eksternal/Client)</label>
                                        <input type="text" id="pjk_pic" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status Projek</label>
                                        <select id="pjk_status" class="form-select">
                                            <option value="InProgress">In Progress</option>
                                            <option value="Completed">Completed</option>
                                            <option value="OnHold">On Hold</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tanggal Mulai</label>
                                        <input type="date" id="pjk_tgl_mulai" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tanggal Selesai</label>
                                        <input type="date" id="pjk_tgl_selesai" class="form-control" required>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" id="btnHapusProjek" class="btn btn-outline-danger px-4">
                                        <i class="bi bi-trash me-2"></i>Hapus Projek
                                    </button>
                                    <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @vite(['resources/js/Projek/edit-handler.js'])
</body>
</html>