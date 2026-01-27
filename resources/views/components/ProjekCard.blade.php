@props([
    'id' => null,
    'nama' => 'Tanpa Nama',
    'deskripsi' => '-',
    'progress' => 0,
    'tanggalMulai' => null,
    'tanggalSelesai' => null,
    'user' => '-',
    'pic' => '-',
    'leader' => '-',
    'tasksDone' => 0,
    'tasksTotal' => 0
])

@vite(['resources/css/ProjekCard.css', 'resources/js/app.css'])

@php
    // Format tanggal
    $formatDate = function($dateStr) {
        if (!$dateStr) return '-';
        return \Carbon\Carbon::parse($dateStr)->locale('id')->isoFormat('D MMM YYYY');
    };

    $dateRange = $formatDate($tanggalMulai) . ' - ' . $formatDate($tanggalSelesai);

    $progressValue = floatval($progress);

    // Warna progress
    $textColor = 'text-danger';
    $bgColor = 'bg-danger';
    $checkIcon = '';

    if ($progressValue >= 100) {
        $textColor = 'text-success';
        $bgColor = 'bg-success';
        $checkIcon = '
            <span class="ms-2 d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                <i class="bi bi-check2-circle text-success ms-4"></i>
            </span>';
    } elseif ($progressValue >= 50) {
        $textColor = 'text-warning';
        $bgColor = 'bg-warning';
    }
@endphp

<div class="card h-100 shadow-sm border project-card"
     data-project-id="{{ $id }}"
     onclick="showProjectDetail({{ $id }})"
     style="cursor:pointer">

    <div class="card-body d-flex flex-column justify-content-between">

        <div>
            {{-- Header --}}
            <div class="mb-4">
                <h5 class="nama text-uppercase text-truncate" title="{{ $nama }}">
                    {{ $nama }}
                </h5>
                <small class="text-muted fw-medium">{{ $dateRange }}</small>
            </div>

            {{-- Progress --}}
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="{{ $textColor }} fw-bold d-flex align-items-center" style="font-size:2rem">
                        {{ number_format($progressValue, 0) }}% {!! $checkIcon !!}
                    </div>
                </div>

                <div class="progress mb-2" style="height:10px">
                    <div class="progress-bar {{ $bgColor }}"
                         role="progressbar"
                         style="width: {{ $progressValue }}%">
                    </div>
                </div>

                <small class="text-muted fw-medium">
                    Tasks {{ $tasksDone }}/{{ $tasksTotal }} done
                </small>
            </div>
        </div>

        <hr>

        {{-- Footer Info --}}
        <div class="d-grid gap-2 project-meta">
            <div class="d-flex align-items-center gap-2 text-sm text-dark">
                <i class="bi bi-person-circle meta-icon user-icon"></i>
                <span><strong>User:</strong> {{ $user }}</span>
            </div>

            <div class="d-flex align-items-center gap-2 text-sm text-dark">
                <i class="bi bi-briefcase-fill meta-icon pic-icon"></i>
                <span><strong>PIC:</strong> {{ $pic }}</span>
            </div>

            <div class="d-flex align-items-center gap-2 text-sm text-dark">
                <i class="bi bi-award-fill meta-icon leader-icon"></i>
                <span><strong>Ketua:</strong> {{ $leader }}</span>
            </div>
        </div>

        <div class="hover-detail-text">
            Klik untuk melihat detail
        </div>

    </div>
</div>