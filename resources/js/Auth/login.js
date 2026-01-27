// resources/js/login.js

document.addEventListener("DOMContentLoaded", function () {
    const loginBtn = document.getElementById("loginBtn");
    const emailInput = document.getElementById("usr_email");
    const passwordInput = document.getElementById("password");
    const errorMsg = document.getElementById("errorMsg");
    const loginForm = document.getElementById("loginForm");

    async function doLogin() {
        if (errorMsg) {
            errorMsg.style.display = "none";
            errorMsg.textContent = "";
        }

        if (!emailInput.value.trim()) {
            showError("Please enter your username or email");
            return;
        }

        if (!passwordInput.value) {
            showError("Please enter your password");
            return;
        }

        loginBtn.disabled = true;
        loginBtn.textContent = "Authenticating...";

        try {
            const response = await fetch("/api/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    login_identity: emailInput.value.trim(),
                    password: passwordInput.value,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message ||
                        "Login failed. Please check your credentials."
                );
            }

            if (data.access_token) {
                localStorage.setItem("api_token", data.access_token);

                if (data.user) {
                    localStorage.setItem(
                        "user_data",
                        JSON.stringify(data.user)
                    );
                }

                loginBtn.textContent = "Success! Redirecting...";

                setTimeout(() => {
                    window.location.href = "/projek";
                }, 500);
            } else {
                throw new Error("Token not received from server");
            }
        } catch (error) {
            showError(error.message);

            loginBtn.disabled = false;
            loginBtn.textContent = "Log In";
        }
    }

 
    function showError(message) {
        if (errorMsg) {
            errorMsg.textContent = message;
            errorMsg.style.display = "block";
        } else {
            alert(message);
        }
    }

    if (loginBtn) {
        loginBtn.addEventListener("click", function (e) {
            e.preventDefault();
            doLogin();
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                doLogin();
            }
        });
    }

    if (emailInput) {
        emailInput.addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                passwordInput.focus();
            }
        });
    }

    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            e.preventDefault();
            doLogin();
        });
    }

    [emailInput, passwordInput].forEach((input) => {
        if (input) {
            input.addEventListener("input", function () {
                if (errorMsg && errorMsg.style.display === "block") {
                    errorMsg.style.display = "none";
                }
            });
        }
    });
});
