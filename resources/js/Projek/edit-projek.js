import axios from "axios";

const apiToken = localStorage.getItem("api_token");
if (apiToken)
    axios.defaults.headers.common["Authorization"] = `Bearer ${apiToken}`;
axios.defaults.headers.common["Accept"] = "application/json";

const apiBase = "/api";

document.addEventListener("DOMContentLoaded", function () {
    const urlParts = window.location.pathname.split("/");
    const projectId = urlParts[2];

    const tglMulaiInput = document.getElementById("pjk_tgl_mulai");
    const tglSelesaiInput = document.getElementById("pjk_tgl_selesai");
    const kategoriSelect = document.getElementById("pjk_kategori");

    let currentUserRole = null;
    let userAccountRole = null;
    let originalTglMulai = null;
    let originalTglSelesai = null;

    const isAdmin = () =>
        currentUserRole === "Ketua" || userAccountRole === "Admin";

    const toast = (icon, title, text = null) =>
        Swal.fire({
            icon,
            title,
            text,
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: text ? 2000 : 1500,
        });

    // ─── CATEGORIES ───────────────────────────────────────────

    const loadCategories = async () => {
        try {
            const { data: response } = await axios.get(`${apiBase}/kategori`);
            const categories = Array.isArray(response)
                ? response
                : response.data || [];

            if (!kategoriSelect) return;

            const currentValue = kategoriSelect.value;
            kategoriSelect.innerHTML =
                '<option value="">-- Select Category --</option>';

            categories
                .filter(
                    (cat) =>
                        cat.ktg_is_active != 0 && cat.ktg_is_active !== false,
                )
                .forEach((cat) => {
                    const opt = document.createElement("option");
                    opt.value = cat.id || cat.ktg_id;
                    opt.textContent = cat.nama || cat.ktg_nama;
                    kategoriSelect.appendChild(opt);
                });

            if (currentValue) kategoriSelect.value = currentValue;
        } catch (err) {
            console.error("Failed to load categories:", err);
        }
    };

    loadCategories();

    // ─── DATE VALIDATION ──────────────────────────────────────

    const initializeDateValidation = () => {
        tglMulaiInput?.removeAttribute("min");
        tglSelesaiInput?.removeAttribute("min");
    };

    tglMulaiInput?.addEventListener("change", function () {
        const val = this.value;

        if (!val) {
            if (tglSelesaiInput) {
                tglSelesaiInput.value = "";
                tglSelesaiInput.removeAttribute("min");
            }
            return;
        }

        const mulai = new Date(val);
        const minSelesai = new Date(mulai);
        minSelesai.setDate(minSelesai.getDate() + 1);
        tglSelesaiInput?.setAttribute(
            "min",
            minSelesai.toISOString().split("T")[0],
        );

        if (
            tglSelesaiInput?.value &&
            new Date(tglSelesaiInput.value) <= mulai
        ) {
            tglSelesaiInput.value = "";
        }
    });

    // ─── PROJECT DATA ─────────────────────────────────────────

    function loadProjectData() {
        axios
            .get(`/api/projek/${projectId}`)
            .then(({ data: { detail: d } }) => {
                document.getElementById("pjk_nama").value = d.nama;
                document.getElementById("pjk_deskripsi").value = d.deskripsi;
                document.getElementById("pjk_pic").value = d.pic;
                document.getElementById("pjk_status").value = d.status;
                document.getElementById("pjk_kategori").value =
                    d.kategori_id || "";
                document.getElementById("pjk_tgl_mulai").value =
                    d.tanggal_mulai;
                document.getElementById("pjk_tgl_selesai").value =
                    d.tanggal_selesai;

                originalTglMulai = d.tanggal_mulai;
                originalTglSelesai = d.tanggal_selesai;

                initializeDateValidation();
                document.getElementById("confirmationText").textContent =
                    `Delete project ${d.nama}`;
                loadCurrentUserRole();
            })
            .catch(() =>
                Swal.fire("Error", "Failed to retrieve project data", "error"),
            );
    }

    function loadCurrentUserRole() {
        axios
            .get(`/api/projek/${projectId}/member`)
            .then(({ data: members }) => {
                const userData = JSON.parse(
                    localStorage.getItem("user_data") || "{}",
                );
                const currentUserId = userData.usr_id || userData.id;
                userAccountRole = userData.role;

                const me = members.find((m) => m.user.id === currentUserId);
                if (me) currentUserRole = me.role;

                const btnAdd = document.getElementById("btnAddTeamMember");
                const btnDelete = document.getElementById("btnHapusProjek");
                const display = isAdmin() ? "block" : "none";
                if (btnAdd) btnAdd.style.display = display;
                if (btnDelete) btnDelete.style.display = display;

                loadMembers();
            })
            .catch(() => {
                console.error("Failed to load user role");
                loadMembers();
            });
    }

    loadProjectData();

    // ─── EDIT PROJECT FORM ────────────────────────────────────

    document
        .getElementById("formEditProjek")
        ?.addEventListener("submit", function (e) {
            e.preventDefault();

            const tglMulaiValue = tglMulaiInput.value;
            const tglSelesaiValue = tglSelesaiInput.value;
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const mulaiChanged = tglMulaiValue !== originalTglMulai;
            const selesaiChanged = tglSelesaiValue !== originalTglSelesai;

            if (mulaiChanged) {
                if (!tglMulaiValue)
                    return Swal.fire(
                        "Validation Error",
                        "Tanggal mulai harus diisi!",
                        "warning",
                    );
                if (new Date(tglMulaiValue) < today)
                    return Swal.fire(
                        "Validation Error",
                        "Tanggal mulai tidak boleh kurang dari hari ini!",
                        "warning",
                    );
            }

            if (selesaiChanged) {
                if (!tglSelesaiValue)
                    return Swal.fire(
                        "Validation Error",
                        "Tanggal selesai harus diisi!",
                        "warning",
                    );
                const effectiveMulai = mulaiChanged
                    ? tglMulaiValue
                    : originalTglMulai;
                if (new Date(tglSelesaiValue) <= new Date(effectiveMulai)) {
                    return Swal.fire(
                        "Validation Error",
                        "Tanggal selesai harus lebih besar dari tanggal mulai!",
                        "warning",
                    );
                }
            }

            const payload = {
                nama: document.getElementById("pjk_nama").value,
                kategori_id: document.getElementById("pjk_kategori").value,
                deskripsi: document.getElementById("pjk_deskripsi").value,
                pic: document.getElementById("pjk_pic").value,
                status: document.getElementById("pjk_status").value,
                tgl_mulai: tglMulaiValue,
                tgl_selesai: tglSelesaiValue,
            };

            axios
                .put(`/api/projek/${projectId}`, payload)
                .then(() =>
                    toast("success", "Success!").then(() => loadProjectData()),
                )
                .catch((err) =>
                    toast(
                        "error",
                        "Failed",
                        err.response?.data?.message || "Error",
                    ),
                );
        });

    // ─── DELETE PROJECT ───────────────────────────────────────

    const deleteConfirmInput = document.getElementById("deleteConfirmInput");
    const btnConfirmDelete = document.getElementById("btnConfirmDelete");
    const deleteConfirmModal = document.getElementById("deleteConfirmModal");

    deleteConfirmInput?.addEventListener("input", function () {
        const expected =
            document.getElementById("confirmationText").textContent;
        btnConfirmDelete.disabled = this.value !== expected;
    });

    btnConfirmDelete?.addEventListener("click", function () {
        const expected =
            document.getElementById("confirmationText").textContent;
        if (deleteConfirmInput.value !== expected) {
            return Swal.fire(
                "Error",
                "Confirmation text does not match!",
                "error",
            );
        }

        btnConfirmDelete.disabled = true;
        const originalHTML = btnConfirmDelete.innerHTML;
        btnConfirmDelete.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

        axios
            .delete(`/api/projek/${projectId}`)
            .then(() =>
                toast(
                    "success",
                    "Project Deleted",
                    "Project has been permanently deleted.",
                ).then(() => (window.location.href = "/projek")),
            )
            .catch((err) => {
                btnConfirmDelete.disabled = false;
                btnConfirmDelete.innerHTML = originalHTML;
                Swal.fire(
                    "Error",
                    err.response?.data?.message || "Failed to delete project",
                    "error",
                );
            });
    });

    deleteConfirmModal?.addEventListener("show.bs.modal", function () {
        deleteConfirmInput.value = "";
        btnConfirmDelete.disabled = true;
        btnConfirmDelete.innerHTML = "Delete Project Permanently";
    });

    // ─── TEAM MEMBERS ─────────────────────────────────────────

    const modalEditMemberEl = document.getElementById("editMemberModal");
    const modalEditMember = modalEditMemberEl
        ? new bootstrap.Modal(modalEditMemberEl)
        : null;

    function getInitials(name) {
        const parts = name.split(" ");
        let init = parts[0].charAt(0);
        if (parts.length > 1) init += parts[1].charAt(0);
        return init.toUpperCase();
    }

    function loadMembers() {
        const listContainer = document.getElementById("teamMembersList");
        const countBadge = document.getElementById("memberCount");

        axios
            .get(`/api/projek/${projectId}/member`)
            .then(({ data: members }) => {
                if (countBadge) countBadge.textContent = members.length;

                if (members.length === 0) {
                    listContainer.innerHTML = `<div class="text-center p-4 text-muted small">No team members assigned yet.</div>`;
                    return;
                }

                listContainer.innerHTML = members
                    .map((m) => {
                        const initials = getInitials(m.user.name);

                        const removeBtn = isAdmin()
                            ? `<button class="btn btn-sm btn-link text-danger btn-remove-member"
                                data-id="${m.id}"
                                title="Remove Member"
                                onclick="event.stopPropagation(); removeMember(${m.id})">
                               <i class="bi bi-x-circle-fill fs-5"></i>
                           </button>`
                            : "";

                        const onClickEdit = isAdmin()
                            ? `openEditMemberModal(${m.id}, '${m.user.name}', '${m.role}')`
                            : "";

                        return `
                        <div class="list-group-item member-item d-flex justify-content-between align-items-center py-3 px-3"
                             style="cursor: pointer;"
                             onclick="${onClickEdit}">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3">${initials}</div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">${m.user.name}</h6>
                                    <small class="text-muted"><i class="bi bi-briefcase me-1"></i>${m.role}</small>
                                </div>
                            </div>
                            ${removeBtn}
                        </div>`;
                    })
                    .join("");
            })
            .catch(() => {
                if (listContainer)
                    listContainer.innerHTML = `<div class="text-center p-3 text-danger">Failed to load members.</div>`;
            });
    }

    window.openEditMemberModal = function (id, name, role) {
        document.getElementById("editMemberId").value = id;
        document.getElementById("editMemberName").value = name;
        document.getElementById("editMemberRole").value = role;
        modalEditMember?.show();
    };

    window.removeMember = function (memberId) {
        Swal.fire({
            title: "Remove Member?",
            text: "Are you sure you want to remove this user?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Yes, remove",
        }).then((result) => {
            if (!result.isConfirmed) return;
            axios
                .delete(`/api/projek/${projectId}/member/${memberId}`)
                .then(() => {
                    toast("success", "Member removed");
                    loadMembers();
                })
                .catch((err) => {
                    const msg =
                        err.response?.data?.message ||
                        "Failed to remove member";
                    const icon =
                        err.response?.status === 422 ? "warning" : "error";
                    Swal.fire({
                        title:
                            icon === "warning"
                                ? "Cannot Remove Member"
                                : "Error",
                        text: msg,
                        icon,
                    });
                });
        });
    };

    document
        .getElementById("formEditMember")
        ?.addEventListener("submit", function (e) {
            e.preventDefault();
            const memberId = document.getElementById("editMemberId").value;
            const newRole = document.getElementById("editMemberRole").value;

            axios
                .put(`/api/projek/${projectId}/member/${memberId}`, {
                    role: newRole,
                })
                .then(() => {
                    modalEditMember?.hide();
                    toast("success", "Role Updated");
                    loadMembers();
                })
                .catch((err) =>
                    Swal.fire(
                        "Error",
                        err.response?.data?.message || "Failed to update role",
                        "error",
                    ),
                );
        });


    let availableUsers = [];
    let selectedUserId = null;

    const modalAddMemberEl = document.getElementById("addMemberModal");
    const modalAddMember = modalAddMemberEl
        ? new bootstrap.Modal(modalAddMemberEl)
        : null;
    const userListContainer = document.getElementById("userSelectionList");
    const searchInput = document.getElementById("searchUser");
    const inputRole = document.getElementById("inputRole");
    const btnSubmitMember = document.getElementById("btnSubmitAddMember");
    const btnSearchTrigger = document.getElementById("btnSearchTrigger");
    const btnAddTeamMember = document.getElementById("btnAddTeamMember");

    btnAddTeamMember?.addEventListener("click", (e) => {
        e.preventDefault();
        modalAddMember?.show();
    });

    const checkSubmitButton = () => {
        btnSubmitMember.disabled = !(selectedUserId && inputRole.value.trim());
    };

    window.selectUserItem = function (id) {
        selectedUserId = id;
        const keyword = searchInput.value.toLowerCase();
        renderUserList(
            availableUsers.filter((u) =>
                u.name.toLowerCase().includes(keyword),
            ),
        );
        checkSubmitButton();
    };

    function renderUserList(users) {
        if (users.length === 0) {
            userListContainer.innerHTML =
                '<div class="text-center py-4 text-muted">No users found.</div>';
            return;
        }

        userListContainer.innerHTML = users
            .map((u) => {
                const initials = getInitials(u.name);
                const isSelected = u.id == selectedUserId;
                const btnClass = isSelected ? "btn-success" : "btn-select-user";
                const btnContent = isSelected
                    ? '<i class="bi bi-check-lg"></i>'
                    : "+ Add Member";
                const ptrEvents = isSelected ? "pointer-events: none;" : "";

                return `
                <div class="user-select-item ${isSelected ? "selected" : ""}" onclick="selectUserItem(${u.id})">
                    <div class="d-flex align-items-center">
                        <div class="user-avatar-dark">${initials}</div>
                        <div>
                            <div class="fw-semibold text-dark">${u.name}</div>
                            <small class="text-muted" style="font-size:0.75rem">${u.email}</small>
                        </div>
                    </div>
                    <button class="${btnClass}" style="border:none; border-radius:6px; padding:5px 12px; font-size:0.8rem; ${ptrEvents}">
                        ${btnContent}
                    </button>
                </div>`;
            })
            .join("");
    }

    const executeSearch = () => {
        const keyword = searchInput.value.toLowerCase();
        renderUserList(
            availableUsers.filter((u) =>
                u.name.toLowerCase().includes(keyword),
            ),
        );
    };

    if (modalAddMemberEl) {
        modalAddMemberEl.addEventListener("show.bs.modal", function () {
            selectedUserId = null;
            inputRole.value = "";
            searchInput.value = "";
            btnSubmitMember.disabled = true;
            document.getElementById("btnSubmitText").style.display = "inline";
            document.getElementById("btnSubmitLoader").style.display = "none";
            userListContainer.innerHTML = `<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm mb-2"></div><div>Loading users...</div></div>`;

            axios
                .get(`/api/users?role=User&exclude_project=${projectId}`)
                .then(({ data }) => {
                    availableUsers = data;
                    renderUserList(availableUsers);
                })
                .catch(() => {
                    userListContainer.innerHTML =
                        '<div class="text-center py-4 text-danger">Error loading users.</div>';
                });
        });

        btnSearchTrigger?.addEventListener("click", executeSearch);

        searchInput?.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                executeSearch();
            }
        });
        searchInput?.addEventListener("input", function () {
            if (!this.value) renderUserList(availableUsers);
        });

        inputRole?.addEventListener("input", checkSubmitButton);

        btnSubmitMember?.addEventListener("click", function () {
            const role = inputRole.value;
            if (!selectedUserId || !role) return;

            document.getElementById("btnSubmitText").style.display = "none";
            document.getElementById("btnSubmitLoader").style.display =
                "inline-block";
            btnSubmitMember.disabled = true;

            axios
                .post(`/api/projek/${projectId}/member`, {
                    user_id: selectedUserId,
                    role,
                    pjk_id: projectId,
                })
                .then(() => {
                    modalAddMember?.hide();
                    toast("success", "Member Added");
                    loadMembers();
                })
                .catch((err) => {
                    Swal.fire(
                        "Error",
                        err.response?.data?.message || "Failed",
                        "error",
                    );
                    document.getElementById("btnSubmitText").style.display =
                        "inline";
                    document.getElementById("btnSubmitLoader").style.display =
                        "none";
                    btnSubmitMember.disabled = false;
                });
        });
    }
});
