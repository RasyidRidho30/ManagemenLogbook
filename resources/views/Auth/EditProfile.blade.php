<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Profile</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    @vite([
        'resources/css/app.css',
        'resources/css/NavbarSearchFilter.css',
        'resources/css/Sidebar.css',
        'resources/css/EditProfile.css',
        'resources/js/Auth/edit-profile.js'
    ])
</head>
<body>

@include('components.NavbarSearchFilter', [
    'title'            => 'Account Settings',
    'showSearchFilter' => false,
    'userName'         => 'User',
    'userRole'         => 'Loading...'
])

<main class="main-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-6">

                <div class="card profile-card">
                    <div class="card-body p-4 p-md-5">

                        {{-- ── Back + Title ── --}}
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                            <a href="/projek" class="back-link">
                                <i class="bi bi-arrow-left-circle"></i> Back to Dashboard
                            </a>
                        </div>

                        <hr class="mt-2 mb-4">

                        <h4 class="profile-title mb-4">
                            <i class="bi bi-person-gear me-2" style="color: var(--color-accent); opacity: 0.85;"></i>
                            Edit My Profile
                        </h4>

                        <form id="formEditProfile" enctype="multipart/form-data">

                            {{-- ── Avatar ── --}}
                            <div class="avatar-upload text-center">
                                <img src="" id="avatarPreview" class="avatar-preview">
                                <label for="avatarInput" class="btn-change-photo" title="Change Photo">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                                <input type="file" id="avatarInput" name="avatar"
                                       class="d-none" accept="image/*">
                            </div>

                            {{-- ── Personal Info ── --}}
                            <div class="section-heading">
                                <i class="bi bi-person opacity-60"></i> Personal Information
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" id="first_name"
                                           class="form-control" required
                                           placeholder="First name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" id="last_name"
                                           class="form-control"
                                           placeholder="Last name">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" id="email"
                                           class="form-control" required
                                           placeholder="your@email.com">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Username</label>
                                    <input type="text" id="username"
                                           class="form-control bg-light" readonly>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-lock me-1"></i>Username cannot be changed.
                                    </small>
                                </div>
                            </div>

                            {{-- ── Change Password ── --}}
                            <div class="section-heading">
                                <i class="bi bi-shield-lock opacity-60"></i> Change Password
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label class="form-label text-danger">Current Password</label>
                                    <input type="password" name="current_password"
                                           class="form-control"
                                           placeholder="Required to change password">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="password"
                                           class="form-control"
                                           placeholder="Leave blank if not changing">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="password_confirmation"
                                           class="form-control"
                                           placeholder="Repeat new password">
                                </div>
                            </div>

                            {{-- ── Submit ── --}}
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check2-circle me-2"></i>Save Changes
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
</body>
</html>