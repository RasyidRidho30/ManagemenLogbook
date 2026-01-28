<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Profile</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/css/NavbarSearchFilter.css', 'resources/css/Sidebar.css'])
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f9; }
        .main-content { margin-left: 125px; padding: 2rem; }
        .profile-card { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .avatar-upload { position: relative; max-width: 120px; margin: 0 auto 20px; }
        .avatar-preview { width: 120px; height: 120px; border-radius: 50%; border: 4px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); object-fit: cover; background: #ddd; }
        .btn-change-photo { position: absolute; bottom: 0; right: 0; background: #143752; color: white; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid #fff; }
        .form-label { font-weight: 600; color: #143752; font-size: 0.9rem; }
    </style>
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
    
    <script>
        const token = localStorage.getItem("api_token");
        if (!token) window.location.href = "/login"; 

        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

        function populateProfile(user) {
            document.getElementById('first_name').value = user.first_name;
            document.getElementById('last_name').value = user.last_name;
            document.getElementById('email').value = user.email;
            document.getElementById('username').value = user.username;
            document.getElementById('avatarPreview').src = user.avatar ;
        }

        axios.get('/api/user')
            .then(res => {
                const payload = res.data;
                const user = payload.data ?? payload;
                populateProfile(user);
            })
            .catch(err => {
                const status = err.response?.status;
                if (status === 404) {
                    axios.get('/api/me')
                        .then(r => {
                            const user = r.data.data ?? r.data;
                            populateProfile(user);
                        })
                        .catch(e => {
                            const st = e.response?.status;
                            if (st === 401 || st === 403) {
                                localStorage.removeItem("api_token");
                                window.location.href = "/login";
                            } else {
                                console.error('Fallback /api/me error', e);
                                Swal.fire('Error', e.response?.data?.message || 'Failed to fetch profile data (fallback)', 'error');
                            }
                        });
                    return;
                }

                if (status === 401 || status === 403) {
                    localStorage.removeItem("api_token");
                    window.location.href = "/login";
                } else {
                    console.error('Error fetching /api/user', err);
                    Swal.fire('Error', err.response?.data?.message || `Failed to fetch profile data (status: ${status || 'unknown'})`, 'error');
                }
            });

        document.getElementById('avatarInput').onchange = function () {
            const [file] = this.files;
            if (file) document.getElementById('avatarPreview').src = URL.createObjectURL(file);
        };

        document.getElementById('formEditProfile').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            axios.post('/api/profile/update', formData)
                .then(res => {
                    Swal.fire('Success', 'Profile has been updated', 'success').then(() => location.reload());
                })
                .catch(err => {
                    Swal.fire('Failed', err.response?.data?.message || 'An error occurred', 'error');
                });
        };
    </script>
</body>
</html>