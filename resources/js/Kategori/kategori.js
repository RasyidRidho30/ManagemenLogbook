import axios from "axios";

const apiToken = localStorage.getItem("api_token");

if (apiToken) {
    axios.defaults.headers.common["Authorization"] = `Bearer ${apiToken}`;
}
axios.defaults.headers.common["Accept"] = "application/json";

document.addEventListener("DOMContentLoaded", () => {
    fetchKategori();
    initializeFormEvents();
});

function initializeFormEvents() {
    const formAdd = document.getElementById("formAddKategori");
    const formEdit = document.getElementById("formEditKategori");

    formAdd?.addEventListener("submit", async (e) => {
        e.preventDefault();
        const payload = {
            nama: document.getElementById("add_nama").value,
            deskripsi: document.getElementById("add_deskripsi").value,
        };
        await submitKategori(
            payload,
            "POST",
            "/api/kategori",
            "modalAddKategori",
            formAdd,
        );
    });

    formEdit?.addEventListener("submit", async (e) => {
        e.preventDefault();
        const id = document.getElementById("edit_id").value;
        const payload = {
            nama: document.getElementById("edit_nama").value,
            deskripsi: document.getElementById("edit_deskripsi").value,
        };
        await submitKategori(
            payload,
            "PUT",
            `/api/kategori/${id}`,
            "modalEditKategori",
        );
    });
}

async function submitKategori(
    payload,
    method,
    url,
    modalId,
    formToReset = null,
) {
    try {
        const response = await axios({ method, url, data: payload });

        bootstrap.Modal.getInstance(document.getElementById(modalId)).hide();
        if (formToReset) formToReset.reset();

        showSuccessToast(response.data.message);
        fetchKategori();
    } catch (error) {
        showError(error);
    }
}

async function fetchKategori() {
    const tbody = document.getElementById("tableBodyKategori");
    renderTableLoading(tbody);

    try {
        const { data: response } = await axios.get("/api/kategori");
        const categories = response.data;

        if (!categories.length) {
            renderTableEmpty(tbody);
            return;
        }

        renderTableData(tbody, categories);
    } catch (error) {
        renderTableError(tbody);
    }
}

function renderTableData(container, categories) {
    container.innerHTML = "";
    categories.forEach((ktg, index) => {
        const isActive = ktg.ktg_is_active == 1;
        const initials = ktg.ktg_nama
            .split(" ")
            .map((n) => n[0])
            .join("")
            .substring(0, 2)
            .toUpperCase();

        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td class="text-center">#${index + 1}</td>
            <td>
                <div class="category-name">
                    <div class="category-avatar">${initials}</div>
                    ${ktg.ktg_nama}
                </div>
            </td>
            <td>
                <div class="category-desc ${!ktg.ktg_deskripsi ? "empty" : ""}">
                    ${ktg.ktg_deskripsi || "No description"}
                </div>
            </td>
            <td class="text-center">
                <span class="badge-status ${isActive ? "badge-aktif" : "badge-nonaktif"}">
                    ${isActive ? "Active" : "Inactive"}
                </span>
            </td>
            <td class="text-center">
                <div class="btn-action-group">
                    <button class="btn btn-edit" onclick="editKategori(${ktg.ktg_id})" title="Edit">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn ${isActive ? "btn-toggle-active" : "btn-toggle-inactive"}" 
                            onclick="toggleStatus(${ktg.ktg_id}, ${isActive})" 
                            title="${isActive ? "Deactivate" : "Activate"}">
                        <i class="bi ${isActive ? "bi-eye-slash" : "bi-eye"}"></i>
                    </button>
                </div>
            </td>
        `;
        container.appendChild(tr);
    });
}

window.editKategori = async (id) => {
    try {
        const { data: response } = await axios.get(`/api/kategori/${id}`);
        const data = response.data;

        document.getElementById("edit_id").value = data.ktg_id;
        document.getElementById("edit_nama").value = data.ktg_nama;
        document.getElementById("edit_deskripsi").value =
            data.ktg_deskripsi || "";

        new bootstrap.Modal(
            document.getElementById("modalEditKategori"),
        ).show();
    } catch (error) {
        showError(error);
    }
};

window.toggleStatus = (id, currentStatus) => {
    const actionLabel = currentStatus ? "Deactivate" : "Activate";
    const confirmColor = currentStatus ? "#ffc107" : "#198754";

    Swal.fire({
        title: `${actionLabel} Group?`,
        text: `Are you sure you want to ${actionLabel.toLowerCase()} this group?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        confirmButtonText: "Yes, Continue!",
        reverseButtons: true,
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await axios.patch(
                    `/api/kategori/${id}/toggle-status`,
                );
                showSuccessToast(response.data.message);
                fetchKategori();
            } catch (error) {
                showError(error);
            }
        }
    });
};

function renderTableLoading(container) {
    container.innerHTML = `
        <tr>
            <td colspan="5" class="loading-row">
                <div class="loading-spinner"></div>
                <p class="text-muted mt-3 mb-0">Loading groups...</p>
            </td>
        </tr>`;
}

function renderTableEmpty(container) {
    container.innerHTML = `
        <tr>
            <td colspan="5">
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                    <h5>No Groups Yet</h5>
                    <p>Start adding new groups to organize your projects</p>
                    <button class="btn btn-add-kategori mt-3" data-bs-toggle="modal" data-bs-target="#modalAddKategori">
                        <i class="bi bi-plus-lg"></i> Add First Group
                    </button>
                </div>
            </td>
        </tr>`;
}

function renderTableError(container) {
    container.innerHTML = `
        <tr>
            <td colspan="5">
                <div class="empty-state">
                    <div class="empty-state-icon" style="background: rgba(220,53,69,0.1); color:#dc3545;"><i class="bi bi-exclamation-triangle"></i></div>
                    <h5>Failed to Load Data</h5>
                    <button class="btn btn-outline-primary mt-3" onclick="fetchKategori()">Try Again</button>
                </div>
            </td>
        </tr>`;
}

function showSuccessToast(message) {
    Swal.fire({
        icon: "success",
        title: "Success!",
        text: message,
        timer: 1500,
        showConfirmButton: false,
        toast: true,
        position: "top-end",
    });
}

function showError(error) {
    const message =
        error.response?.data?.message || "An error occurred on the server.";
    Swal.fire({
        icon: "error",
        title: "Failed!",
        text: message,
        confirmButtonColor: "#143752",
    });
}
