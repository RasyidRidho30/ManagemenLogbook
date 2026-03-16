window.populateGroupFilter = function (groups) {
    window._pendingGroups = groups;
};

document.addEventListener("DOMContentLoaded", async function () {
    const apiToken = localStorage.getItem("api_token");

    await initializeUserProfile(apiToken);

    setupNavbarSearch();
    setupStatusFilter();
    setupGroupFilter();
    setupSortButton();
    setupUserAvatarDropdown(apiToken);
    setupGlobalClickOutsideListener();
});

async function initializeUserProfile(token) {
    if (!token) return;

    try {
        const response = await fetch("/api/me", {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: "application/json",
            },
        });

        if (response.ok) {
            const responseData = await response.json();
            const user = responseData.data || responseData;

            updateUserInterface(user);
            saveUserDataAndHandleAdminFeatures(user);
        }
    } catch (error) {
        console.error(error);
    }
}

function updateUserInterface(user) {
    const nameElement = document.getElementById("nav-user-name");
    const roleElement = document.getElementById("nav-user-role");
    const avatarContainer = document.getElementById("avatarBtn");

    if (nameElement) nameElement.textContent = user.name;
    if (roleElement) roleElement.textContent = user.role;

    if (avatarContainer) {
        const avatarUrl = user.avatar || user.avatar_url || null;

        if (avatarUrl) {
            avatarContainer.innerHTML = `<img src="${avatarUrl}" alt="User Avatar">`;
        } else {
            const initialLetter = (user.first_name || user.name || "U")
                .charAt(0)
                .toUpperCase();
            avatarContainer.innerHTML = `<div class="avatar-placeholder">${initialLetter}</div>`;
        }
    }
}

function saveUserDataAndHandleAdminFeatures(user) {
    try {
        localStorage.setItem("user_data", JSON.stringify(user));

        const kategoriBtn = document.getElementById("kategoriBtn");
        if (kategoriBtn) {
            const isAdmin = user.role && user.role.toLowerCase() === "admin";
            kategoriBtn.style.display = isAdmin ? "flex" : "none";
        }
    } catch (error) {}
}

function setupNavbarSearch() {
    const searchInput = document.getElementById("navbarSearchInput");
    const searchBtn = document.getElementById("navbarSearchBtn");

    const triggerSearchEvent = (value) => {
        window.dispatchEvent(
            new CustomEvent("navbar-search", {
                detail: { searchValue: value },
            }),
        );
    };

    if (searchInput) {
        searchInput.addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                triggerSearchEvent(this.value);
            }
        });

        if (searchBtn) {
            searchBtn.addEventListener("click", function () {
                triggerSearchEvent(searchInput.value);
            });
        }
    }
}

function setupStatusFilter() {
    const filterBtn = document.getElementById("filterBtn");
    const filterBtnText = document.getElementById("filterBtnText");
    const filterDropdown = document.getElementById("filterDropdown");
    const filterItems = document.querySelectorAll(
        "#filterDropdown .dropdown-item",
    );

    if (!filterBtn || !filterDropdown) return;

    filterBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        closeAllDropdownsExcept(filterDropdown);
        filterDropdown.classList.toggle("d-none");
        filterBtn.classList.toggle("active");
    });

    filterItems.forEach((item) => {
        item.addEventListener("click", function () {
            const filterValue = this.getAttribute("data-value");
            const filterLabel = this.textContent.trim();

            filterItems.forEach((i) => i.classList.remove("active"));
            this.classList.add("active");

            if (filterBtnText) {
                if (filterValue) {
                    filterBtnText.textContent = filterLabel;
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
                    detail: { status: filterValue },
                }),
            );
        });
    });
}

function setupGroupFilter() {
    const groupFilterBtn = document.getElementById("groupFilterBtn");
    const groupFilterDropdown = document.getElementById("groupFilterDropdown");
    const groupFilterBtnText = document.getElementById("groupFilterBtnText");

    if (!groupFilterBtn || !groupFilterDropdown) return;

    groupFilterBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        closeAllDropdownsExcept(groupFilterDropdown);
        groupFilterDropdown.classList.toggle("d-none");
        groupFilterBtn.classList.toggle("active");
    });

    const allGroupsDefaultItem =
        groupFilterDropdown.querySelector("[data-value='']");
    if (allGroupsDefaultItem) {
        allGroupsDefaultItem.addEventListener("click", function () {
            groupFilterDropdown
                .querySelectorAll(".dropdown-item")
                .forEach((i) => i.classList.remove("active"));

            allGroupsDefaultItem.classList.add("active");

            if (groupFilterBtnText) {
                groupFilterBtnText.textContent = "All Groups";
                groupFilterBtn.classList.remove("selected-filter");
            }

            groupFilterDropdown.classList.add("d-none");
            groupFilterBtn.classList.remove("active");

            window.dispatchEvent(
                new CustomEvent("navbar-group-filter", {
                    detail: { group: "" },
                }),
            );
        });
    }

    window.populateGroupFilter = function (groups) {
        const normalizedGroups = Array.isArray(groups) ? groups : [];

        groupFilterDropdown
            .querySelectorAll(".dropdown-item:not([data-value=''])")
            .forEach((el) => el.remove());

        normalizedGroups.forEach(function (group) {
            const groupId =
                typeof group === "object" ? (group.id ?? group.ktg_id) : group;
            const groupName =
                typeof group === "object"
                    ? (group.name ?? group.ktg_nama)
                    : group;

            if (!groupId || !groupName) return;

            const dropdownItem = document.createElement("div");
            dropdownItem.className = "dropdown-item";
            dropdownItem.setAttribute("data-value", groupId);
            dropdownItem.innerHTML = `<i class="bi bi-tag"></i> ${groupName}`;

            dropdownItem.addEventListener("click", function () {
                groupFilterDropdown
                    .querySelectorAll(".dropdown-item")
                    .forEach((i) => i.classList.remove("active"));

                dropdownItem.classList.add("active");

                const selectedValue = dropdownItem.getAttribute("data-value");

                if (groupFilterBtnText) {
                    groupFilterBtnText.textContent = selectedValue
                        ? groupName
                        : "All Groups";
                    selectedValue
                        ? groupFilterBtn.classList.add("selected-filter")
                        : groupFilterBtn.classList.remove("selected-filter");
                }

                groupFilterDropdown.classList.add("d-none");
                groupFilterBtn.classList.remove("active");

                window.dispatchEvent(
                    new CustomEvent("navbar-group-filter", {
                        detail: { group: selectedValue },
                    }),
                );
            });

            groupFilterDropdown.appendChild(dropdownItem);
        });
    };

    if (window._pendingGroups) {
        window.populateGroupFilter(window._pendingGroups);
        window._pendingGroups = null;
    }
}

function setupSortButton() {
    const sortBtn = document.getElementById("sortBtn");
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
}

function setupUserAvatarDropdown(token) {
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
                executeLogout(token);
            });
        }
    }
}

function executeLogout(token) {
    fetch("/api/logout", {
        method: "POST",
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
        },
    }).finally(() => {
        localStorage.removeItem("api_token");
        window.location.href = "/";
    });
}

function closeAllDropdownsExcept(exceptDropdownElement) {
    const filterDropdown = document.getElementById("filterDropdown");
    const filterBtn = document.getElementById("filterBtn");
    const groupFilterDropdown = document.getElementById("groupFilterDropdown");
    const groupFilterBtn = document.getElementById("groupFilterBtn");

    if (exceptDropdownElement !== filterDropdown && filterDropdown) {
        filterDropdown.classList.add("d-none");
        filterBtn?.classList.remove("active");
    }

    if (exceptDropdownElement !== groupFilterDropdown && groupFilterDropdown) {
        groupFilterDropdown.classList.add("d-none");
        groupFilterBtn?.classList.remove("active");
    }
}

function setupGlobalClickOutsideListener() {
    document.addEventListener("click", function (e) {
        const filterBtn = document.getElementById("filterBtn");
        const filterDropdown = document.getElementById("filterDropdown");
        const groupFilterBtn = document.getElementById("groupFilterBtn");
        const groupFilterDropdown = document.getElementById(
            "groupFilterDropdown",
        );
        const avatarBtn = document.getElementById("avatarBtn");
        const avatarDropdown = document.getElementById("avatarDropdown");

        if (
            filterBtn &&
            filterDropdown &&
            !filterBtn.contains(e.target) &&
            !filterDropdown.contains(e.target)
        ) {
            filterDropdown.classList.add("d-none");
            filterBtn.classList.remove("active");
        }

        if (
            groupFilterBtn &&
            groupFilterDropdown &&
            !groupFilterBtn.contains(e.target) &&
            !groupFilterDropdown.contains(e.target)
        ) {
            groupFilterDropdown.classList.add("d-none");
            groupFilterBtn.classList.remove("active");
        }

        if (
            avatarBtn &&
            avatarDropdown &&
            !avatarBtn.contains(e.target) &&
            !avatarDropdown.contains(e.target)
        ) {
            avatarDropdown.classList.add("d-none");
        }
    });
}
