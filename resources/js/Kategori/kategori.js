import axios from "axios";

// Setup Axios Default
const apiToken = localStorage.getItem("api_token");
if (apiToken) {
    axios.defaults.headers.common["Authorization"] = `Bearer ${apiToken}`;
}
axios.defaults.headers.common["Accept"] = "application/json";

document.addEventListener("DOMContentLoaded", () => {
    fetchKategori();

    // Form Add
    const formAdd = document.getElementById("formAddKategori");
    formAdd?.addEventListener("submit", async (e) => {
        e.preventDefault();
        const data = {
            nama: document.getElementById("add_nama").value,
            deskripsi: document.getElementById("add_deskripsi").value,
        };

        try {
            const res = await axios.post("/api/kategori", data);
            bootstrap.Modal.getInstance(
                document.getElementById("modalAddKategori"),
            ).hide();
            formAdd.reset();

            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: res.data.message,
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: "top-end",
            });
            fetchKategori();
        } catch (err) {
            showError(err);
        }
    });

    // Form Edit
    const formEdit = document.getElementById("formEditKategori");
    formEdit?.addEventListener("submit", async (e) => {
        e.preventDefault();
        const id = document.getElementById("edit_id").value;
        const data = {
            nama: document.getElementById("edit_nama").value,
            deskripsi: document.getElementById("edit_deskripsi").value,
        };

        try {
            const res = await axios.put(`/api/kategori/${id}`, data);
            bootstrap.Modal.getInstance(
                document.getElementById("modalEditKategori"),
            ).hide();

            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: res.data.message,
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: "top-end",
            });
            fetchKategori();
        } catch (err) {
            showError(err);
        }
    });
});


// Fetch and Render
async function fetchKategori() {
    const tbody = document.getElementById("tableBodyKategori");
    tbody.innerHTML = `
        <tr>
            <td colspan="5" class="loading-row">
                <div class="loading-spinner"></div>
                <p class="text-muted mt-3 mb-0">Memuat data kategori...</p>
            </td>
        </tr>
    `;

    try {
        const { data: response } = await axios.get("/api/kategori");
        const categories = response.data;

        tbody.innerHTML = "";

        if (!categories.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <h5>Belum Ada Kategori</h5>
                            <p>Mulai tambahkan kategori baru untuk mengorganisir proyek Anda</p>
                            <button class="btn btn-add-kategori mt-3" data-bs-toggle="modal" data-bs-target="#modalAddKategori">
                                <i class="bi bi-plus-lg"></i> Tambah Kategori Pertama
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        categories.forEach((ktg, index) => {
            const isActive = ktg.ktg_is_active == 1;
            const badgeClass = isActive ? "badge-aktif" : "badge-nonaktif";
            const badgeText = isActive ? "Aktif" : "Nonaktif";
            const btnToggleClass = isActive
                ? "btn-toggle-active"
                : "btn-toggle-inactive";
            const btnToggleIcon = isActive ? "bi-eye-slash" : "bi-eye";
            const btnToggleTitle = isActive ? "Nonaktifkan" : "Aktifkan";
            const initials = ktg.ktg_nama
                .split(" ")
                .map((n) => n[0])
                .join("")
                .substring(0, 2)
                .toUpperCase();
            const descClass = ktg.ktg_deskripsi
                ? "category-desc"
                : "category-desc empty";
            const descText = ktg.ktg_deskripsi || "Tidak ada deskripsi";

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
                    <div class="${descClass}">${descText}</div>
                </td>
                <td class="text-center">
                    <span class="badge-status ${badgeClass}">
                        ${badgeText}
                    </span>
                </td>
                <td class="text-center">
                    <div class="btn-action-group">
                        <button class="btn btn-edit" onclick="editKategori(${ktg.ktg_id})" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn ${btnToggleClass}" onclick="toggleStatus(${ktg.ktg_id}, ${isActive})" title="${btnToggleTitle}">
                            <i class="bi ${btnToggleIcon}"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (err) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5">
                    <div class="empty-state">
                        <div class="empty-state-icon" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <h5>Gagal Memuat Data</h5>
                        <p>Terjadi kesalahan saat mengambil data dari server. Silakan coba lagi.</p>
                        <button class="btn btn-outline-primary mt-3" onclick="fetchKategori()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Coba Lagi
                        </button>
                    </div>
                </td>
            </tr>
        `;
        console.error(err);
    }
}

// Edit Handler
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
    } catch (err) {
        showError(err);
    }
};

// Toggle Status Handler
window.toggleStatus = (id, currentStatus) => {
    const actionText = currentStatus ? "menonaktifkan" : "mengaktifkan";
    const confirmBtnColor = currentStatus ? "#ffc107" : "#198754";

    Swal.fire({
        title: currentStatus ? "Nonaktifkan Kategori?" : "Aktifkan Kategori?",
        text: `Anda yakin ingin ${actionText} kategori ini?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: confirmBtnColor,
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya, Lanjutkan!",
        cancelButtonText: "Batal",
        reverseButtons: true,
        customClass: {
            popup: "rounded-3",
            confirmButton: "px-4",
            cancelButton: "px-4",
        },
    }).then(async (result) => {
        if (!result.isConfirmed) return;

        try {
            const res = await axios.patch(`/api/kategori/${id}/toggle-status`);
            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: res.data.message,
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: "top-end",
            });
            fetchKategori();
        } catch (err) {
            showError(err);
        }
    });
};

// Delete Handler
window.deleteKategori = (id, nama) => {
    Swal.fire({
        title: "Hapus Kategori?",
        html: `
            <div class="text-center mb-3">
                <div class="mb-3" style="font-size: 3rem; color: #dc3545;">
                    <i class="bi bi-trash"></i>
                </div>
                <p class="mb-1">Anda yakin ingin menghapus</p>
                <h5 class="text-dark fw-bold">"${nama}"</h5>
            </div>
            <div class="alert alert-warning d-flex align-items-center text-start" style="font-size: 0.875rem;">
                <i class="bi bi-info-circle-fill me-2"></i>
                <div>Projek yang menggunakan kategori ini tidak akan terpengaruh.</div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal",
        reverseButtons: true,
        customClass: {
            popup: "rounded-3",
            htmlContainer: "px-4",
        },
    }).then(async (result) => {
        if (!result.isConfirmed) return;

        try {
            const res = await axios.delete(`/api/kategori/${id}`);
            Swal.fire({
                icon: "success",
                title: "Terhapus!",
                text: res.data.message,
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: "top-end",
            });
            fetchKategori();
        } catch (err) {
            showError(err);
        }
    });
};

// Error Handler
function showError(err) {
    const message =
        err.response?.data?.message || "Terjadi kesalahan pada server.";
    Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: message,
        confirmButtonColor: "#143752",
        customClass: {
            popup: "rounded-3",
            confirmButton: "px-4",
        },
    });
}
