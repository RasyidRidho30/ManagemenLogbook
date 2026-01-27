document.addEventListener("DOMContentLoaded", async function () {
    // =========================================================
    // 1. FETCH USER DATA (Untuk Nama & Role)
    // =========================================================
    const token = localStorage.getItem("api_token");
    if (token) {
        try {
            const response = await fetch("/api/me", {
                headers: {
                    Authorization: "Bearer " + token,
                    Accept: "application/json",
                },
            });

            if (response.ok) {
                const data = await response.json();
                const user = data.data || data;

                const nameEl = document.getElementById("nav-user-name");
                const roleEl = document.getElementById("nav-user-role");
                const avatarContainer = document.getElementById("avatarBtn");

                // Update name & role
                if (nameEl) nameEl.textContent = user.name;
                if (roleEl) roleEl.textContent = user.role;

                // Update avatar in navbar: prefer full URL from API, fallback to ui-avatars
                if (avatarContainer) {
                    const avatarUrl = user.avatar || user.avatar_url || null;

                    if (avatarUrl) {
                        avatarContainer.innerHTML = `<img src="${avatarUrl}" alt="User Avatar">`;
                    } else {
                        const initial =
                            (user.first_name || user.name || "")
                                .charAt(0)
                                .toUpperCase() || "P";
                        avatarContainer.innerHTML = `<div class="avatar-placeholder">${initial}</div>`;
                    }
                }

                // Cache user data for quick subsequent loads
                try {
                    localStorage.setItem("user_data", JSON.stringify(user));
                } catch (e) {
                    /* ignore */
                }
            }
        } catch (e) {
            console.error("Gagal mengambil data user:", e);
        }
    }

    // =========================================================
    // 2. LOGIKA NAVBAR (Search, Filter, Sort)
    // =========================================================

    const navbarSearchInput = document.getElementById("navbarSearchInput");

    // Filter Elements
    const filterBtn = document.getElementById("filterBtn");
    const filterBtnText = document.getElementById("filterBtnText"); // ID untuk teks tombol
    const filterDropdown = document.getElementById("filterDropdown");
    const filterItems = document.querySelectorAll(
        "#filterDropdown .dropdown-item"
    );

    // Sort Elements
    const sortBtn = document.getElementById("sortBtn");

    // A. Search Logic
    if (navbarSearchInput) {
        navbarSearchInput.addEventListener("input", function (e) {
            window.dispatchEvent(
                new CustomEvent("navbar-search", {
                    detail: { searchValue: e.target.value },
                })
            );
        });
    }

    // B. Filter Dropdown Logic
    if (filterBtn && filterDropdown) {
        // Toggle Dropdown saat tombol diklik
        filterBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            filterDropdown.classList.toggle("d-none");
            filterBtn.classList.toggle("active"); // Toggle state aktif tombol
        });

        // Handle Klik Item Dropdown
        filterItems.forEach((item) => {
            item.addEventListener("click", function () {
                const value = this.getAttribute("data-value");
                const label = this.textContent; // Ambil teks (misal: "Completed")

                // 1. Update Active State di Dropdown
                filterItems.forEach((i) => i.classList.remove("active"));
                this.classList.add("active");

                // 2. Update Teks & Warna Tombol Filter (UX Improvement)
                if (filterBtnText) {
                    if (value) {
                        filterBtnText.textContent = label; // Ubah jadi "Completed"
                        filterBtn.classList.add("selected-filter"); // Tambah class khusus (opsional)
                    } else {
                        filterBtnText.textContent = "Filter"; // Reset jadi "Filter"
                        filterBtn.classList.remove("selected-filter");
                    }
                }

                // 3. Sembunyikan dropdown
                filterDropdown.classList.add("d-none");
                filterBtn.classList.remove("active"); // Hapus state toggle active

                // 4. Kirim Event ke read-projek.js
                window.dispatchEvent(
                    new CustomEvent("navbar-filter", {
                        detail: { status: value },
                    })
                );
            });
        });

        // Tutup Dropdown jika klik di luar
        document.addEventListener("click", function (e) {
            if (
                !filterBtn.contains(e.target) &&
                !filterDropdown.contains(e.target)
            ) {
                filterDropdown.classList.add("d-none");
                filterBtn.classList.remove("active");
            }
        });
    }

    // C. Sort Logic
    if (sortBtn) {
        sortBtn.addEventListener("click", function () {
            const isActive = sortBtn.classList.toggle("active");
            window.dispatchEvent(
                new CustomEvent("navbar-sort", {
                    detail: { isActive: isActive },
                })
            );
        });
    }

    // =========================================================
    // 3. LOGIKA DROPDOWN AVATAR & LOGOUT
    // =========================================================
    const avatarBtn = document.getElementById("avatarBtn");
    const avatarDropdown = document.getElementById("avatarDropdown");
    const logoutBtn = document.getElementById("logoutBtn");

    if (avatarBtn && avatarDropdown) {
        // Toggle dropdown avatar
        avatarBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            avatarDropdown.classList.toggle("d-none");
        });

        // Logout Logic
        if (logoutBtn) {
            logoutBtn.addEventListener("click", function () {
                // Skenario 1: Logout via API (opsional tapi disarankan)
                const token = localStorage.getItem("api_token");
                fetch("/api/logout", {
                    method: "POST",
                    headers: {
                        Authorization: "Bearer " + token,
                        Accept: "application/json",
                    },
                }).finally(() => {
                    // Skenario 2: Hapus token dan redirect
                    localStorage.removeItem("api_token");
                    window.location.href = "/"; // Redirect ke halaman login
                });
            });
        }
    }

    // Update penutup klik di luar untuk menyertakan avatarDropdown
    document.addEventListener("click", function (e) {
        // Tutup filter dropdown
        if (
            filterBtn &&
            !filterBtn.contains(e.target) &&
            !filterDropdown.contains(e.target)
        ) {
            filterDropdown.classList.add("d-none");
            filterBtn.classList.remove("active");
        }

        // Tutup avatar dropdown
        if (
            avatarBtn &&
            !avatarBtn.contains(e.target) &&
            !avatarDropdown.contains(e.target)
        ) {
            avatarDropdown.classList.add("d-none");
        }
    });
});
