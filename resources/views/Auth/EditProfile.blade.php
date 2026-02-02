<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Profile</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/css/NavbarSearchFilter.css', 'resources/css/Sidebar.css', 'resources/css/EditProfile.css', 'resources/js/Auth/edit-profile.js'])
    
</head>
<body>
    @include('components.NavbarSearchFilter', [
        'title' => 'Account Settings',
        'showSearchFilter' => false,
        'userName' => 'User',
        'userRole' => 'Loading...'
    ])

    <main class="main-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card profile-card">
                        <div class="card-body p-5">
                            <div>
                                <a href="/projek" class="text-decoration-none text-secondary">
                                    <i class="bi bi-arrow-left-circle me-1"></i> Back to Dashboard
                                </a>
                            </div>
                            <hr class="my-4">

                            <h4 class="fw-bold mb-4" style="color: #143752;">Edit My Profile</h4>
                            
                            <form id="formEditProfile" enctype="multipart/form-data">
                                <div class="avatar-upload text-center">
                                    <img src="" id="avatarPreview" class="avatar-preview">
                                    <label for="avatarInput" class="btn-change-photo">
                                        <i class="bi bi-camera-fill"></i>
                                    </label>
                                    <input type="file" id="avatarInput" name="avatar" class="d-none" accept="image/*">
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" id="first_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" id="last_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" id="email" class="form-control" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Username</label>
                                        <input type="text" id="username" class="form-control bg-light" readonly>
                                        <small class="text-muted">Username cannot be changed.</small>
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h6 class="fw-bold mb-3">Change Password</h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label text-danger">Current Password</label>
                                        <input type="password" name="current_password" class="form-control" placeholder="Required to change password">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="password" class="form-control" placeholder="Leave blank if not changing">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="password_confirmation" class="form-control">
                                    </div>
                                </div>
                                <div class="mt-4 text-end">
                                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
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
</body>
</html>