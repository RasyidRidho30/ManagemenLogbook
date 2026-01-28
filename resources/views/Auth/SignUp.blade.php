<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up - Logbook Management</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/login.css', 'resources/js/Auth/signup.js'])
</head>
<body>

    <div class="main-card">
        <div class="left-panel">
            <div class="header-section">
                <div class="brand-logo">
                    <img class="logo-placeholder" src="{{ asset('images/LogoBiruOnly.png') }}" alt="Logo">
                    <div>
                        <h1 class="brand-title">Create an account</h1>
                        <p class="brand-subtitle">Create an account to start using the application</p>
                    </div>
                </div>
                <div class="divider"></div>
            </div>

            <form id="signupForm">
                <div id="signupError" class="alert-error" style="display:none"></div>

                <div class="form-group">
                    <div class="input-wrapper">
                        <input type="text" id="username" class="form-input" placeholder="Username" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-wrapper">
                        <input type="text" id="first_name" class="form-input" placeholder="First name" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-wrapper">
                        <input type="text" id="last_name" class="form-input" placeholder="Last name">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-wrapper">
                        <input type="email" id="email" class="form-input" placeholder="Email" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-wrapper">
                        <input type="password" id="password" class="form-input" placeholder="Password" required>
                    </div>
                </div>

                <button type="button" id="signupBtn" class="btn-login">Sign Up</button>
                <a href="/" class="btn-signup">Back to Login</a>
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