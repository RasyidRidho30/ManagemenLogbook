document.addEventListener("DOMContentLoaded", function () {
    const signupBtn = document.getElementById("signupBtn");
    const username = document.getElementById("username");
    const firstName = document.getElementById("first_name");
    const lastName = document.getElementById("last_name");
    const email = document.getElementById("email");
    const password = document.getElementById("password");
    // role is fixed to 'user' by the server; we still send it for clarity
    const role = "user";
    const errorEl = document.getElementById("signupError");

    function showError(msg) {
        if (errorEl) {
            errorEl.textContent = msg;
            errorEl.style.display = "block";
        } else {
            alert(msg);
        }
    }

    async function doSignUp() {
        if (errorEl) {
            errorEl.style.display = "none";
        }

        if (!username.value.trim()) {
            showError("Username is required");
            return;
        }
        if (!firstName.value.trim()) {
            showError("First name is required");
            return;
        }
        if (!email.value.trim()) {
            showError("Email is required");
            return;
        }
        if (!password.value) {
            showError("Password is required");
            return;
        }
        if (password.value.length < 8) {
            showError("Password must be at least 8 characters long");
            return;
        }

        signupBtn.disabled = true;
        signupBtn.textContent = "Creating...";

        try {
            const res = await fetch("/api/register", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    username: username.value.trim(),
                    email: email.value.trim(),
                    password: password.value,
                    first_name: firstName.value.trim(),
                    last_name: lastName.value.trim(),
                    role: role,
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                // Laravel validation returns 422 with errors object
                if (data.errors) {
                    const firstKey = Object.keys(data.errors)[0];
                    throw new Error(data.errors[firstKey][0]);
                }
                throw new Error(data.message || "Registration failed");
            }

            window.location.href = "/";
        } catch (err) {
            showError(err.message);
            signupBtn.disabled = false;
            signupBtn.textContent = "Sign Up";
        }
    }

    if (signupBtn) {
        signupBtn.addEventListener("click", function (e) {
            e.preventDefault();
            doSignUp();
        });
    }
});
