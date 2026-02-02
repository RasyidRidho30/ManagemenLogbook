import "../bootstrap";
import { Modal } from "bootstrap";

document.addEventListener("DOMContentLoaded", function () {
    const apiBase = "/api";
    const container = document.getElementById("projekContainer");
    const searchEl = document.getElementById("search");
    const statusEl = document.getElementById("status");
    const searchBtn = document.getElementById("searchBtn");
    const errorMsg = document.getElementById("errorMsg");
    const logoutBtn = document.getElementById("logoutBtn");

    const navbarSearchInput = document.getElementById("navbarSearchInput");
    const filterBtn = document.getElementById("filterBtn");
    const sortBtn = document.getElementById("sortBtn");
    const filterCard = document.getElementById("filterCard");

    const addForm = document.getElementById("formAddProjek");
    const modalElement = document.getElementById("addProjekModal");
    const addModal = modalElement ? new Modal(modalElement) : null;

    let currentSortOrder = "desc";
    let currentSortBy = "progress";
    let currentStatusFilter = "";

    function setAuthToken(token) {
        if (!token) {
            delete axios.defaults.headers.common["Authorization"];
            localStorage.removeItem("api_token");
            if (logoutBtn) logoutBtn.classList.add("d-none");
            return;
        }
        axios.defaults.headers.common["Authorization"] = "Bearer " + token;
        localStorage.setItem("api_token", token);
        if (logoutBtn) logoutBtn.classList.remove("d-none");
    }

    const savedToken = localStorage.getItem("api_token");
    if (savedToken) setAuthToken(savedToken);

    async function loadProjects(searchQuery = "") {
        if (errorMsg) errorMsg.textContent = "";
        container.innerHTML =
            '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';

        const params = {};
        const finalSearch =
            searchQuery || (navbarSearchInput ? navbarSearchInput.value : "");

        if (finalSearch) params.search = finalSearch;
        if (currentStatusFilter) params.status = currentStatusFilter;

        try {
            const res = await axios.get(apiBase + "/projek", { params });
            let data = Array.isArray(res.data) ? res.data : res.data.data || [];

            data = sortProjects(data);
            container.innerHTML = "";

            if (data.length === 0) {
                container.innerHTML =
                    '<div class="col-12 text-center text-muted py-4">No projects found.</div>';
                return;
            }

            renderProjectCards(data);
        } catch (err) {
            console.error(err);
            if (err.response && err.response.status === 401) {
                if (errorMsg)
                    errorMsg.innerHTML =
                        'Session expired. Please <a href="/login">Login again</a>.';
            } else {
                if (errorMsg)
                    errorMsg.textContent =
                        "Failed to load data: " +
                        (err.message || "Unknown error");
            }
        }
    }

    async function renderProjectCards(projects) {
        try {
            const response = await axios.post("/projek/render-cards", {
                projects,
            });
            container.innerHTML = response.data.html;
        } catch (err) {
            container.innerHTML = "";
            projects.forEach((p) => {
                const col = document.createElement("div");
                col.className = "col-12 col-sm-6 col-md-4";
                col.innerHTML = `
                    <div class="card p-3 h-100 shadow-sm border-0" onclick="showProjectDetail(${
                        p.id || p.pjk_id
                    })">
                        <h5 class="mb-1 fw-bold text-primary">${
                            p.nama || p.pjk_nama || "No Name"
                        }</h5>
                        
                        <p class="mb-1 text-muted small">
                            <i class="bi bi-calendar-event me-1"></i>
                            ${p.tanggal_mulai || p.pjk_tanggal_mulai || "-"} to 
                            ${p.tanggal_selesai || p.pjk_tanggal_selesai || "-"}
                        </p>

                        <p class="mb-2 text-muted small">
                            <i class="bi bi-person-badge me-1"></i> PIC: 
                            <span class="fw-bold">${
                                p.pic || p.pjk_pic || "-"
                            }</span>
                        </p>

                        <div class="progress mt-auto" style="height: 8px;">
                            <div class="progress-bar" style="width: ${
                                p.persentase_progress ??
                                p.pjk_persentasi_progress ??
                                0
                            }%"></div>
                        </div>
                        <p class="mb-0 mt-1 small">Progress: ${
                            p.persentase_progress ??
                            p.pjk_persentasi_progress ??
                            0
                        }%</p>
                    </div>`;
                container.appendChild(col);
            });
        }
    }

    function sortProjects(projects) {
        return projects.sort((a, b) => {
            let valA, valB;
            switch (currentSortBy) {
                case "name":
                    valA = (a.nama || "").toLowerCase();
                    valB = (b.nama || "").toLowerCase();
                    break;
                case "date":
                    valA = new Date(a.tanggal_mulai || 0);
                    valB = new Date(b.tanggal_mulai || 0);
                    break;
                default:
                    valA = parseFloat(a.persentase_progress || 0);
                    valB = parseFloat(b.persentase_progress || 0);
            }
            return currentSortOrder === "asc"
                ? valA > valB
                    ? 1
                    : -1
                : valA < valB
                  ? 1
                  : -1;
        });
    }

    function toggleSort() {
        currentSortOrder = currentSortOrder === "asc" ? "desc" : "asc";
        if (sortBtn) {
            const icon = sortBtn.querySelector("i");
            if (icon)
                icon.className =
                    currentSortOrder === "asc"
                        ? "bi bi-sort-up"
                        : "bi bi-sort-down-alt";
        }
        loadProjects();
    }

    if (addForm) {
        addForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            const btnSubmit = document.getElementById("btnSubmit");
            const btnText = document.getElementById("btnText");
            const btnLoader = document.getElementById("btnLoader");
            const alertBox = document.getElementById("modalAlert");

            btnSubmit.disabled = true;
            btnText.classList.add("d-none");
            btnLoader.classList.remove("d-none");

            const payload = {
                nama: document.getElementById("nama").value,
                pic: document.getElementById("pic").value,
                deskripsi: document.getElementById("deskripsi").value,
                tgl_mulai: document.getElementById("tgl_mulai").value,
                tgl_selesai: document.getElementById("tgl_selesai").value,
            };

            try {
                await axios.post(apiBase + "/projek", payload);
                alertBox.innerHTML =
                    '<div class="alert alert-success small py-2">Project successfully added!</div>';

                setTimeout(() => {
                    if (addModal) addModal.hide();
                    addForm.reset();
                    alertBox.innerHTML = "";
                    loadProjects();
                }, 1000);
            } catch (error) {
                const msg =
                    error.response?.data?.message || "Failed to save data.";
                alertBox.innerHTML = `<div class="alert alert-danger small py-2">${msg}</div>`;
            } finally {
                btnSubmit.disabled = false;
                btnText.classList.remove("d-none");
                btnLoader.classList.add("d-none");
            }
        });
    }

    if (filterBtn) {
        filterBtn.addEventListener("click", () => {
            filterBtn.classList.toggle("active");
        });
    }

    if (sortBtn) {
        sortBtn.addEventListener("click", () => {
            sortBtn.classList.toggle("active");
            toggleSort();
        });
    }

    if (statusEl) {
        statusEl.addEventListener("change", () => loadProjects());
    }

    if (logoutBtn) {
        logoutBtn.addEventListener("click", () => {
            setAuthToken(null);
            window.location.href = "/";
        });
    }

    if (localStorage.getItem("api_token")) {
        loadProjects();
    } else {
        if (errorMsg)
            errorMsg.innerHTML =
                'You are not logged in. <a href="/login">Login here</a>';
    }

    window.addEventListener("navbar-filter", function (e) {
        currentStatusFilter = e.detail.status;
        loadProjects();
    });

    window.addEventListener("navbar-search", function (e) {
        loadProjects(e.detail.searchValue);
    });

    window.showProjectDetail = function (id) {
        window.location.href = `/projek/${id}/dashboard`;
    };
});
