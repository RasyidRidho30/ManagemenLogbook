<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $projek->pjk_nama }} - Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/Sidebar.css') }}">
    @vite(['resources/js/app.js', 'resources/css/app.css', 'resources/css/NavbarSearchFilter.css', 'resources/css/ProjekRead.css', 'resources/css/Dashboard.css'])

</head>
<body>
    <x-Sidebar :projectId="$projek->pjk_id" activeMenu="beranda" />

    {{-- Include Navbar --}}
    @include('components.NavbarSearchFilter', [
        'title' => 'Sistem Manajemen Logbook',
        'showSearchFilter' => false,
        'userName' => auth()->user()->name ?? 'Guest',
        'userRole' => auth()->user()->role ?? 'No Role',
        'userAvatar' => auth()->user()->avatar ?? null,
    ])

    {{-- 2. Konten Utma --}}
    <main class="main-content">
        
        {{-- Top Navigation --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="/projek" class="btn-back-custom shadow-sm">
                <i class="bi bi-chevron-left"></i>
                <span>Daftar Projek</span>
            </a>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/projek" class="text-decoration-none">Projek</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav> 
        </div>

        {{-- Page Content Card --}}
        <div class="card border shadow-sm" style="border-radius: 12px; ">
            <div class="card-body p-4">
                
                {{-- Header Title --}}
                <div class="page-header mb-4">
                    <h2 class="mb-1">{{ $projek->pjk_nama }}</h2>
                    <p class="text-muted mb-0">Beranda</p>
                </div>
                <hr class="mb-4">

                <div class="row g-4">
                    {{-- Kolom Kiri: Tabel Progress Modul & Kegiatan --}}
                    <div class="col-lg-9">
                        <div class="table-responsive rounded border">
                            <table class="table custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 5%">No</th>
                                        <th style="width: 35%">Uraian Kegiatan / Modul</th>
                                        <th class="text-center" style="width: 15%">% Selesai</th> {{-- Sebelumnya % Complete --}}
                                        <th class="text-center" style="width: 10%">Bobot</th>
                                        <th class="text-center" style="width: 15%">Porsi Bobot (%)</th> {{-- Sebelumnya Prosentase --}}
                                        <th class="text-center" style="width: 20%">Kontribusi Projek</th> {{-- Sebelumnya Total --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $counter = 1; @endphp
                                    @foreach($breakdown as $item)
                                        @if($item->tipe_item === 'Modul')
                                            <tr class="modul-row">
                                                <td class="text-center"><i class="bi bi-folder2-open"></i></td>
                                                <td colspan="2">MODUL : {{ $item->nama_item }}</td>
                                                <td class="text-center">{{ $item->bobot_angka }}</td>
                                                <td class="text-center">{{ number_format($item->prosentase_item, 2) }}%</td>
                                                <td></td> 
                                            </tr>
                                            @php $counter = 1; @endphp
                                        @else
                                            <tr>
                                                <td class="text-center text-muted">{{ chr(64 + $counter++) }}</td>
                                                <td class="ps-4">{{ $item->nama_item }}</td>
                                                <td class="text-center">
                                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                                        <span class="small">{{ number_format($item->progress_persen, 2) }}%</span>
                                                        <div class="progress d-none d-md-flex" style="height: 6px; min-width: 50px;">
                                                            <div class="progress-bar bg-success" style="width: {{ $item->progress_persen }}%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">{{ $item->bobot_angka }}</td>
                                                <td class="text-center text-muted small">{{ number_format($item->prosentase_item, 2) }}%</td>
                                                <td class="text-center fw-bold text-success">
                                                    {{ number_format($item->kontribusi_total, 2) }}%
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Total Progress Card (Bawah Tabel) --}}
                        <div class="mt-3 p-3 rounded bg-light border d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">Progres Total Projek:</span>
                            <span class="fw-bold text-primary fs-5">
                                @php
                                    $totalRealProgress = collect($breakdown)->where('tipe_item', 'Kegiatan')->sum('kontribusi_total');
                                @endphp
                                {{ number_format($totalRealProgress, 2) }} %
                            </span>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Tim & Search --}}
                    <div class="col-lg-3 ">
                        
                        {{-- Search Box --}}
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Cari...">
                        </div>

                       {{-- Team List --}}
                        <div class="team-card border">
                            <div class="team-header">Tim</div>
                            <div class="team-body">
                                @foreach($team as $member)
                                    <div class="member-item">
                                        {{-- Avatar Logic --}}
                                        <div class="avatar-circle" style="width: 30px; height: 30px; font-size: 0.75rem; overflow: hidden;">
                                            @if($member->usr_avatar_url)
                                                {{-- Tampilkan Gambar jika tidak null --}}
                                                <img src="{{ $member->usr_avatar_url }}" 
                                                    alt="{{ $member->usr_first_name }}" 
                                                    style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                {{-- Tampilkan Huruf Awal jika null --}}
                                                {{ strtoupper(substr($member->usr_first_name, 0, 1)) }}
                                            @endif
                                        </div>

                                        <div class="lh-1">
                                            <div class="small fw-bold text-dark">
                                                {{ $member->usr_first_name }} {{ $member->usr_last_name }}
                                            </div>
                                            <small class="text-muted" style="font-size: 0.7rem">
                                                {{ $member->mpk_role_projek }}
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>