const token = localStorage.getItem("api_token");
if (!token) window.location.href = "/login";

axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

function populateProfile(user) {
    document.getElementById("first_name").value = user.first_name;
    document.getElementById("last_name").value = user.last_name;
    document.getElementById("email").value = user.email;
    document.getElementById("username").value = user.username;
    document.getElementById("avatarPreview").src = user.avatar;
}

axios
    .get("/api/user")
    .then((res) => {
        const payload = res.data;
        const user = payload.data ?? payload;
        populateProfile(user);
    })
    .catch((err) => {
        const status = err.response?.status;
        if (status === 404) {
            axios
                .get("/api/me")
                .then((r) => {
                    const user = r.data.data ?? r.data;
                    populateProfile(user);
                })
                .catch((e) => {
                    const st = e.response?.status;
                    if (st === 401 || st === 403) {
                        localStorage.removeItem("api_token");
                        window.location.href = "/login";
                    } else {
                        console.error("Fallback /api/me error", e);
                        Swal.fire(
                            "Error",
                            e.response?.data?.message ||
                                "Failed to fetch profile data (fallback)",
                            "error",
                        );
                    }
                });
            return;
        }

        if (status === 401 || status === 403) {
            localStorage.removeItem("api_token");
            window.location.href = "/login";
        } else {
            console.error("Error fetching /api/user", err);
            Swal.fire(
                "Error",
                err.response?.data?.message ||
                    `Failed to fetch profile data (status: ${status || "unknown"})`,
                "error",
            );
        }
    });

document.getElementById("avatarInput").onchange = function () {
    const [file] = this.files;
    if (file)
        document.getElementById("avatarPreview").src =
            URL.createObjectURL(file);
};

document.getElementById("formEditProfile").onsubmit = function (e) {
    e.preventDefault();

    const newPassword = document.querySelector('input[name="password"]').value;
    const currentPassword = document.querySelector(
        'input[name="current_password"]',
    ).value;

    if (newPassword && !currentPassword) {
        Swal.fire(
            "Validation Error",
            "Current password is required to change password",
            "error",
        );
        return;
    }

    const formData = new FormData(this);

    axios
        .post("/api/profile/update", formData)
        .then((res) => {
            Swal.fire("Success", "Profile has been updated", "success").then(
                () => location.reload(),
            );
        })
        .catch((err) => {
            const message = err.response?.data?.message || "An error occurred";
            Swal.fire("Failed", message, "error");
        });
};
