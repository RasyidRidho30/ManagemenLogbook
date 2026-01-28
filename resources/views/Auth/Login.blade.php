<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Logbook Management</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/login.css', 'resources/js/Auth/login.js', 'resources/js/app.js'])
</head>
<body>

    <div class="main-card">
        <div class="left-panel">
            
            <div class="header-section">
                <div class="brand-logo">
                    <img class="logo-placeholder" src="{{ asset('images/LogoBiruOnly.png') }}" alt="Logo">
                   
                    <div>
                        <h1 class="brand-title">LOGBOOK MANAGEMENT</h1>
                        <p class="brand-subtitle">Project Monitoring and Management</p>
                    </div>
                </div>
                <div class="divider"></div>
            </div>

            <form id="loginForm">
                <div id="errorMsg" class="alert-error" style="display:none"></div>

                <div class="form-group">
                    <div class="input-wrapper">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <input type="text" id="usr_email" class="form-input" placeholder="Username or Email" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-wrapper">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input type="password" id="password" class="form-input" placeholder="Password" required>
                    </div>
                </div>
                
                <a href="#" class="forgot-password">Forgot password?</a>

                <button type="button" id="loginBtn" class="btn-login">Log In</button>
                <a href="/signup" class="btn-signup">Sign Up</a>
            </form>

        </div>

        <div class="right-panel">
            <div class="illustration-container">
                <img class="illustration-img" src="{{ asset('images/IlustrationLogin.png') }}" alt="">
            </div>
        </div>
    </div>

</body>
</html>