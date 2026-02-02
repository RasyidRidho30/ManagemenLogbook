document.addEventListener("DOMContentLoaded", async function () {
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

                if (nameEl) nameEl.textContent = user.name;
                if (roleEl) roleEl.textContent = user.role;

                if (avatarContainer) {
                    const avatarUrl = user.avatar || user.avatar_url || null;

                    if (avatarUrl) {
                        avatarContainer.innerHTML = `<img src="${avatarUrl}" alt="User Avatar">`;
                    } else {
                        const initial =
                            (user.first_name || user.name || "")
                                .charAt(0)
                                .toUpperCase() || "U";
                        avatarContainer.innerHTML = `<div class="avatar-placeholder">${initial}</div>`;
                    }
                }

                try {
                    localStorage.setItem("user_data", JSON.stringify(user));
                } catch (e) {}
            }
        } catch (e) {
            console.error(e);
        }
    }

    const navbarSearchInput = document.getElementById("navbarSearchInput");
    const navbarSearchBtn = document.getElementById("navbarSearchBtn");

    const filterBtn = document.getElementById("filterBtn");
    const filterBtnText = document.getElementById("filterBtnText");
    const filterDropdown = document.getElementById("filterDropdown");
    const filterItems = document.querySelectorAll(
        "#filterDropdown .dropdown-item",
    );
    const sortBtn = document.getElementById("sortBtn");

    function triggerSearch(value) {
        window.dispatchEvent(
            new CustomEvent("navbar-search", {
                detail: { searchValue: value },
            }),
        );
    }

    if (navbarSearchInput) {
        navbarSearchInput.addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                triggerSearch(this.value);
            }
        });

        if (navbarSearchBtn) {
            navbarSearchBtn.addEventListener("click", function () {
                triggerSearch(navbarSearchInput.value);
            });
        }
    }

    if (filterBtn && filterDropdown) {
        filterBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            filterDropdown.classList.toggle("d-none");
            filterBtn.classList.toggle("active");
        });

        filterItems.forEach((item) => {
            item.addEventListener("click", function () {
                const value = this.getAttribute("data-value");
                const label = this.textContent;

                filterItems.forEach((i) => i.classList.remove("active"));
                this.classList.add("active");

                if (filterBtnText) {
                    if (value) {
                        filterBtnText.textContent = label;
                        filterBtn.classList.add("selected-filter");
                    } else {
                        filterBtnText.textContent = "Filter";
                        filterBtn.classList.remove("selected-filter");
                    }
                }

                filterDropdown.classList.add("d-none");
                filterBtn.classList.remove("active");

                window.dispatchEvent(
                    new CustomEvent("navbar-filter", {
                        detail: { status: value },
                    }),
                );
            });
        });

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

    if (sortBtn) {
        sortBtn.addEventListener("click", function () {
            const isActive = sortBtn.classList.toggle("active");
            window.dispatchEvent(
                new CustomEvent("navbar-sort", {
                    detail: { isActive: isActive },
                }),
            );
        });
    }

    const avatarBtn = document.getElementById("avatarBtn");
    const avatarDropdown = document.getElementById("avatarDropdown");
    const logoutBtn = document.getElementById("logoutBtn");

    if (avatarBtn && avatarDropdown) {
        avatarBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            avatarDropdown.classList.toggle("d-none");
        });

        if (logoutBtn) {
            logoutBtn.addEventListener("click", function () {
                const token = localStorage.getItem("api_token");
                fetch("/api/logout", {
                    method: "POST",
                    headers: {
                        Authorization: "Bearer " + token,
                        Accept: "application/json",
                    },
                }).finally(() => {
                    localStorage.removeItem("api_token");
                    window.location.href = "/";
                });
            });
        }
    }

    document.addEventListener("click", function (e) {
        if (
            filterBtn &&
            !filterBtn.contains(e.target) &&
            !filterDropdown.contains(e.target)
        ) {
            filterDropdown.classList.add("d-none");
            filterBtn.classList.remove("active");
        }

        if (
            avatarBtn &&
            !avatarBtn.contains(e.target) &&
            !avatarDropdown.contains(e.target)
        ) {
            avatarDropdown.classList.add("d-none");
        }
    });
});
