import "../bootstrap";
import { Modal } from "bootstrap";

document.addEventListener("DOMContentLoaded", () => {
    const elements = {
        container: document.getElementById("projekContainer"),
        search: document.getElementById("search"),
        status: document.getElementById("status"),
        searchBtn: document.getElementById("searchBtn"),
        errorMsg: document.getElementById("errorMsg"),
        logoutBtn: document.getElementById("logoutBtn"),
        navbarSearchInput: document.getElementById("navbarSearchInput"),
        filterBtn: document.getElementById("filterBtn"),
        filterDropdown: document.getElementById("filterDropdown"),
        sortBtn: document.getElementById("sortBtn"),
        kategoriBtn: document.getElementById("kategoriBtn"),
        groupFilterBtn: document.getElementById("groupFilterBtn"),
        groupFilterDropdown: document.getElementById("groupFilterDropdown"),
        groupFilterBtnText: document.getElementById("groupFilterBtnText"),
        filterCard: document.getElementById("filterCard"),
        addForm: document.getElementById("formAddProjek"),
        modalElement: document.getElementById("addProjekModal"),
        tglMulai: document.getElementById("tgl_mulai"),
        tglSelesai: document.getElementById("tgl_selesai"),
        nama: document.getElementById("nama"),
        kategori: document.getElementById("kategori"),
        pic: document.getElementById("pic"),
        deskripsi: document.getElementById("deskripsi"),
        btnSubmit: document.getElementById("btnSubmit"),
        btnText: document.getElementById("btnText"),
        btnLoader: document.getElementById("btnLoader"),
        modalAlert: document.getElementById("modalAlert"),
    };

    const addModal = elements.modalElement
        ? new Modal(elements.modalElement)
        : null;
    const apiBase = "/api";
    const userData = JSON.parse(localStorage.getItem("user_data") || "{}");

    const state = {
        sortOrder: "desc",
        sortBy: "progress",
        statusFilter: "",
        groupFilter: "",
    };

    if (userData.role !== "Admin") {
        elements.kategoriBtn?.classList.add("d-none");
    }

    const formatDate = (date) => date.toISOString().split("T")[0];

    const getToday = () => {
        const d = new Date();
        d.setHours(0, 0, 0, 0);
        return d;
    };

    const showAlert = (type, message) => {
        if (elements.modalAlert) {
            elements.modalAlert.innerHTML = `<div class="alert alert-${type} small py-2">${message}</div>`;
        }
    };

    const clearAlert = () => {
        if (elements.modalAlert) elements.modalAlert.innerHTML = "";
    };

    const setAuthToken = (token) => {
        if (!token) {
            delete axios.defaults.headers.common.Authorization;
            localStorage.removeItem("api_token");
            elements.logoutBtn?.classList.add("d-none");
            return;
        }
        axios.defaults.headers.common.Authorization = `Bearer ${token}`;
        localStorage.setItem("api_token", token);
        elements.logoutBtn?.classList.remove("d-none");
    };

    const loadCategories = async () => {
        try {
            const { data: response } = await axios.get(`${apiBase}/kategori`);
            const categories = Array.isArray(response)
                ? response
                : response.data || [];

            if (elements.kategori) {
                const selectElement = elements.kategori;
                const currentValue = selectElement.value;

                selectElement.innerHTML =
                    '<option value="">-- Select Category --</option>';
                categories.forEach((cat) => {
                    if (cat.ktg_is_active === 0) return;
                    const option = document.createElement("option");
                    option.value = cat.ktg_id;
                    option.textContent = cat.ktg_nama;
                    selectElement.appendChild(option);
                });
                if (currentValue) selectElement.value = currentValue;
            }
        } catch (err) {
            console.error(err);
        }
    };

    const loadGroupsForNavbar = async () => {
        try {
            const { data: response } = await axios.get(`${apiBase}/kategori`);
            const categories = Array.isArray(response)
                ? response
                : response.data || [];
            const activeCategories = categories
                .filter((cat) => cat.ktg_is_active !== 0)
                .map((cat) => ({ id: cat.ktg_id, name: cat.ktg_nama }));

            window.populateGroupFilter(activeCategories);
        } catch (err) {
            console.error(err);
        }
    };

    const initDateValidation = () => {
        const todayStr = formatDate(getToday());
        elements.tglMulai?.setAttribute("min", todayStr);

        if (elements.tglSelesai) {
            elements.tglSelesai.disabled = true;
            elements.tglSelesai.placeholder =
                "Isi tanggal mulai terlebih dahulu";
        }
    };

    const handleTglMulaiChange = (e) => {
        const value = e.target.value;
        const { tglSelesai } = elements;

        if (!value) {
            if (tglSelesai) {
                tglSelesai.disabled = true;
                tglSelesai.value = "";
                tglSelesai.removeAttribute("min");
            }
            return;
        }

        const minDate = new Date(value);
        minDate.setDate(minDate.getDate() + 1);
        const minStr = formatDate(minDate);

        if (tglSelesai) {
            tglSelesai.disabled = false;
            tglSelesai.setAttribute("min", minStr);
            tglSelesai.removeAttribute("placeholder");

            if (
                tglSelesai.value &&
                new Date(tglSelesai.value) <= new Date(value)
            ) {
                tglSelesai.value = "";
            }
        }
    };

    const validateForm = (e) => {
        const tglMulai = elements.tglMulai?.value;
        const tglSelesai = elements.tglSelesai?.value;
        const today = getToday();

        if (!tglMulai) {
            e.preventDefault();
            return showAlert("warning", "Tanggal mulai harus diisi!");
        }

        if (new Date(tglMulai) < today) {
            e.preventDefault();
            return showAlert(
                "warning",
                "Tanggal mulai tidak boleh kurang dari hari ini!",
            );
        }

        if (!tglSelesai) {
            e.preventDefault();
            return showAlert("warning", "Tanggal selesai harus diisi!");
        }

        const dMulai = new Date(tglMulai);
        const dSelesai = new Date(tglSelesai);

        if (dSelesai <= dMulai) {
            e.preventDefault();
            return showAlert(
                "warning",
                "Tanggal selesai harus lebih besar dari tanggal mulai!",
            );
        }

        if (dSelesai <= today) {
            e.preventDefault();
            return showAlert(
                "warning",
                "Tanggal selesai tidak boleh kurang dari atau sama dengan hari ini!",
            );
        }
    };

    const calculateProgressFromBreakdown = async (project) => {
        try {
            const { data: breakdown } = await axios.get(
                `${apiBase}/projek/${project.id}/breakdown`,
            );
            const totalProgress = Array.isArray(breakdown)
                ? breakdown
                      .filter((item) => item.tipe_item === "Kegiatan")
                      .reduce(
                          (sum, item) =>
                              sum + (parseFloat(item.kontribusi_total) || 0),
                          0,
                      )
                : 0;
            project.persentase_progress = Math.round(totalProgress * 100) / 100;
        } catch (err) {
            project.persentase_progress ||= 0;
        }
    };

    const sortProjects = (projects) => {
        const { sortBy, sortOrder } = state;
        const multiplier = sortOrder === "asc" ? 1 : -1;

        return [...projects].sort((a, b) => {
            let valA, valB;
            switch (sortBy) {
                case "name":
                    valA = (a.nama || "").toLowerCase();
                    valB = (b.nama || "").toLowerCase();
                    break;
                case "date":
                    valA = new Date(a.tanggal_mulai || 0).getTime();
                    valB = new Date(b.tanggal_mulai || 0).getTime();
                    break;
                default:
                    valA = parseFloat(a.persentase_progress || 0);
                    valB = parseFloat(b.persentase_progress || 0);
            }
            return (valA > valB ? 1 : -1) * multiplier;
        });
    };

    const renderProjectCards = async (projects) => {
        try {
            const { data } = await axios.post("/projek/render-cards", {
                projects,
            });
            elements.container.innerHTML = data.html;
        } catch {
            elements.container.innerHTML = projects
                .map(
                    (p) => `
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="card p-3 h-100 shadow-sm border-0" onclick="showProjectDetail(${p.id || p.pjk_id})">
                        <h5 class="mb-1 fw-bold text-primary">${p.nama || p.pjk_nama || "No Name"}</h5>
                        <p class="mb-1 text-muted small"><i class="bi bi-tags me-1"></i>${p.kategori_nama || p.pjk_kategori_nama || "-"}</p>
                        <p class="mb-1 text-muted small"><i class="bi bi-calendar-event me-1"></i>${p.tanggal_mulai || p.pjk_tanggal_mulai || "-"} to ${p.tanggal_selesai || p.pjk_tanggal_selesai || "-"}</p>
                        <p class="mb-2 text-muted small"><i class="bi bi-person-badge me-1"></i> PIC: <span class="fw-bold">${p.pic || p.pjk_pic || "-"}</span></p>
                        <div class="progress mt-auto" style="height: 8px;"><div class="progress-bar" style="width: ${p.persentase_progress ?? p.pjk_persentasi_progress ?? 0}%"></div></div>
                        <p class="mb-0 mt-1 small">Progress: ${p.persentase_progress ?? p.pjk_persentasi_progress ?? 0}%</p>
                    </div>
                </div>`,
                )
                .join("");
        }
    };

    const loadProjects = async (searchQuery = "") => {
        if (elements.errorMsg) elements.errorMsg.textContent = "";
        elements.container.innerHTML =
            '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';

        const params = {};
        const finalSearch =
            searchQuery || elements.navbarSearchInput?.value || "";
        if (finalSearch) params.search = finalSearch;
        if (state.statusFilter) params.status = state.statusFilter;
        if (state.groupFilter) params.kategori_id = state.groupFilter;

        try {
            const { data: responseData } = await axios.get(
                `${apiBase}/projek`,
                { params },
            );
            let data = Array.isArray(responseData)
                ? responseData
                : responseData.data || [];

            await Promise.all(data.map(calculateProgressFromBreakdown));
            data = sortProjects(data);

            if (!data.length) {
                elements.container.innerHTML =
                    '<div class="col-12 text-center text-muted py-4">No projects found.</div>';
                return;
            }

            await renderProjectCards(data);
        } catch (err) {
            const msg =
                err.response?.status === 401
                    ? 'Session expired. Please <a href="/login">Login again</a>.'
                    : `Failed to load data: ${err.message || "Unknown error"}`;
            if (elements.errorMsg) elements.errorMsg.innerHTML = msg;
        }
    };

    const toggleSort = () => {
        state.sortOrder = state.sortOrder === "asc" ? "desc" : "asc";
        const icon = elements.sortBtn?.querySelector("i");
        if (icon) {
            icon.className =
                state.sortOrder === "asc"
                    ? "bi bi-sort-up"
                    : "bi bi-sort-down-alt";
        }
        loadProjects();
    };

    const handleAddSubmit = async (e) => {
        e.preventDefault();
        const { btnSubmit, btnText, btnLoader, addForm } = elements;

        btnSubmit.disabled = true;
        btnText?.classList.add("d-none");
        btnLoader?.classList.remove("d-none");

        const payload = {
            nama: elements.nama?.value,
            kategori_id: elements.kategori?.value,
            pic: elements.pic?.value,
            deskripsi: elements.deskripsi?.value,
            tgl_mulai: elements.tglMulai?.value,
            tgl_selesai: elements.tglSelesai?.value,
        };

        try {
            await axios.post(`${apiBase}/projek`, payload);
            showAlert("success", "Project successfully added!");
            setTimeout(() => {
                addModal?.hide();
                addForm?.reset();
                clearAlert();
                loadProjects();
            }, 1000);
        } catch (error) {
            showAlert(
                "danger",
                error.response?.data?.message || "Failed to save data.",
            );
        } finally {
            btnSubmit.disabled = false;
            btnText?.classList.remove("d-none");
            btnLoader?.classList.add("d-none");
        }
    };

    elements.tglMulai?.addEventListener("change", handleTglMulaiChange);
    elements.modalElement?.addEventListener("show.bs.modal", () => {
        initDateValidation();
        loadCategories();
    });
    elements.addForm?.addEventListener("submit", validateForm, true);
    elements.addForm?.addEventListener("submit", handleAddSubmit);

    elements.sortBtn?.addEventListener("click", () => {
        elements.sortBtn.classList.toggle("active");
        toggleSort();
    });
    elements.logoutBtn?.addEventListener("click", () => {
        setAuthToken(null);
        window.location.href = "/";
    });

    window.addEventListener("navbar-filter", (e) => {
        state.statusFilter = e.detail.status;
        loadProjects();
    });
    window.addEventListener("navbar-search", (e) =>
        loadProjects(e.detail.searchValue),
    );
    window.addEventListener("navbar-group-filter", (e) => {
        state.groupFilter = e.detail.group;
        loadProjects();
    });

    window.showProjectDetail = (id) => {
        window.location.href = `/projek/${id}/dashboard`;
    };

    const savedToken = localStorage.getItem("api_token");
    if (savedToken) {
        setAuthToken(savedToken);
        loadGroupsForNavbar().then(() => loadProjects());
    } else if (elements.errorMsg) {
        elements.errorMsg.innerHTML =
            'You are not logged in. <a href="/login">Login here</a>';
    }
});
