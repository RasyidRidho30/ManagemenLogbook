import axios from "axios";

const apiToken = localStorage.getItem("api_token");
axios.defaults.headers.common["Authorization"] = `Bearer ${apiToken}`;
axios.defaults.headers.common["Accept"] = "application/json";

document.addEventListener("DOMContentLoaded", function () {
    const urlParts = window.location.pathname.split("/");
    const projectId = urlParts[2];

    // --- LOGIKA UTAMA (LOAD DATA, EDIT, DELETE PROJECT) TETAP SAMA ---
    function loadProjectData() {
        axios
            .get(`/api/projek/${projectId}`)
            .then((res) => {
                const data = res.data.detail;
                document.getElementById("pjk_nama").value = data.nama;
                document.getElementById("pjk_deskripsi").value = data.deskripsi;
                document.getElementById("pjk_pic").value = data.pic;
                document.getElementById("pjk_status").value = data.status;
                document.getElementById("pjk_tgl_mulai").value =
                    data.tanggal_mulai;
                document.getElementById("pjk_tgl_selesai").value =
                    data.tanggal_selesai;
            })
            .catch(() =>
                Swal.fire("Error", "Failed to retrieve project data", "error"),
            );
    }
    loadProjectData();

    const form = document.getElementById("formEditProjek");
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            const payload = {
                nama: document.getElementById("pjk_nama").value,
                deskripsi: document.getElementById("pjk_deskripsi").value,
                pic: document.getElementById("pjk_pic").value,
                status: document.getElementById("pjk_status").value,
                tgl_mulai: document.getElementById("pjk_tgl_mulai").value,
                tgl_selesai: document.getElementById("pjk_tgl_selesai").value,
            };
            axios
                .put(`/api/projek/${projectId}`, payload)
                .then(() =>
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        timer: 1500,
                        showConfirmButton: false,
                    }).then(() => loadProjectData()),
                )
                .catch((err) =>
                    Swal.fire(
                        "Failed",
                        err.response?.data?.message || "Error",
                        "error",
                    ),
                );
        });
    }

    const btnHapus = document.getElementById("btnHapusProjek");
    if (btnHapus) {
        btnHapus.addEventListener("click", function () {
            // ... (Logika hapus projek tetap sama) ...
            // Agar kode tidak terlalu panjang, saya persingkat bagian ini karena tidak berubah
            Swal.fire({
                title: "Delete?",
                text: "Cannot be undone",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
            }).then((r) => {
                if (r.isConfirmed)
                    axios
                        .delete(`/api/projek/${projectId}`)
                        .then(() => (window.location.href = "/projek"));
            });
        });
    }

    // ==========================================================
    // LOGIKA KELOLA TIM (UPDATED: CLICK TO EDIT)
    // ==========================================================

    // Modal Edit Member Instance
    const modalEditMemberEl = document.getElementById("editMemberModal");
    const modalEditMember = modalEditMemberEl
        ? new bootstrap.Modal(modalEditMemberEl)
        : null;

    function loadMembers() {
        const listContainer = document.getElementById("teamMembersList");
        const countBadge = document.getElementById("memberCount");

        axios
            .get(`/api/projek/${projectId}/member`)
            .then((res) => {
                const members = res.data;
                if (countBadge) countBadge.textContent = members.length;

                if (members.length === 0) {
                    listContainer.innerHTML = `<div class="text-center p-4 text-muted small">No team members assigned yet.</div>`;
                    return;
                }

                let html = "";
                members.forEach((m) => {
                    const nameParts = m.user.name.split(" ");
                    let initials = nameParts[0].charAt(0);
                    if (nameParts.length > 1)
                        initials += nameParts[1].charAt(0);
                    initials = initials.toUpperCase();

                    // Tambahkan class 'cursor-pointer' dan event onclick
                    html += `
                        <div class="list-group-item member-item d-flex justify-content-between align-items-center py-3 px-3" 
                             style="cursor: pointer;"
                             onclick="openEditMemberModal(${m.id}, '${m.user.name}', '${m.role}')">
                            
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3">${initials}</div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">${m.user.name}</h6>
                                    <small class="text-muted"><i class="bi bi-briefcase me-1"></i>${m.role}</small>
                                </div>
                            </div>
                            
                            <button class="btn btn-sm btn-link text-danger btn-remove-member" 
                                    data-id="${m.id}" 
                                    title="Remove Member"
                                    onclick="event.stopPropagation(); removeMember(${m.id})">
                                <i class="bi bi-x-circle-fill fs-5"></i>
                            </button>
                        </div>
                    `;
                });
                listContainer.innerHTML = html;
            })
            .catch((err) => {
                if (listContainer)
                    listContainer.innerHTML = `<div class="text-center p-3 text-danger">Failed to load members.</div>`;
            });
    }

    loadMembers();

    // Fungsi Global untuk Membuka Modal Edit
    window.openEditMemberModal = function (id, name, role) {
        document.getElementById("editMemberId").value = id;
        document.getElementById("editMemberName").value = name;
        document.getElementById("editMemberRole").value = role;

        if (modalEditMember) modalEditMember.show();
    };

    // Fungsi Global Remove Member (Dipanggil langsung dari HTML string di atas)
    window.removeMember = function (memberId) {
        Swal.fire({
            title: "Remove Member?",
            text: "Are you sure you want to remove this user?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Yes, remove",
        }).then((result) => {
            if (result.isConfirmed) {
                axios
                    .delete(`/api/projek/${projectId}/member/${memberId}`)
                    .then(() => {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 2000,
                        });
                        Toast.fire({
                            icon: "success",
                            title: "Member removed",
                        });
                        loadMembers();
                    })
                    .catch(() =>
                        Swal.fire("Error", "Failed to remove member", "error"),
                    );
            }
        });
    };

    // Handle Submit Form Edit Member
    const formEditMember = document.getElementById("formEditMember");
    if (formEditMember) {
        formEditMember.addEventListener("submit", function (e) {
            e.preventDefault();
            const memberId = document.getElementById("editMemberId").value;
            const newRole = document.getElementById("editMemberRole").value;

            axios
                .put(`/api/projek/${projectId}/member/${memberId}`, {
                    role: newRole,
                })
                .then(() => {
                    modalEditMember.hide();
                    Swal.fire({
                        icon: "success",
                        title: "Role Updated",
                        showConfirmButton: false,
                        timer: 1000,
                    });
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
    }

    // ==========================================================
    // LOGIKA MODAL ADD MEMBER (TETAP SAMA)
    // ==========================================================
    // ... (Kode untuk Add Member Modal, Search, dll tidak berubah dari sebelumnya) ...
    // Sertakan kode Add Member di sini agar file tetap utuh.

    let availableUsers = [];
    let selectedUserId = null;

    const modalAddMember = document.getElementById("addMemberModal");
    const userListContainer = document.getElementById("userSelectionList");
    const searchInput = document.getElementById("searchUser");
    const inputRole = document.getElementById("inputRole");
    const btnSubmitMember = document.getElementById("btnSubmitAddMember");
    const btnSearchTrigger = document.getElementById("btnSearchTrigger");

    window.selectUserItem = function (id) {
        selectedUserId = id;
        const keyword = searchInput.value.toLowerCase();
        const currentList = availableUsers.filter((u) =>
            u.name.toLowerCase().includes(keyword),
        );
        renderUserList(currentList);
        checkSubmitButton();
    };

    function renderUserList(users) {
        if (users.length === 0) {
            userListContainer.innerHTML =
                '<div class="text-center py-4 text-muted">No users found.</div>';
            return;
        }
        let html = "";
        users.forEach((u) => {
            const nameParts = u.name.split(" ");
            let initials = nameParts[0].charAt(0);
            if (nameParts.length > 1) initials += nameParts[1].charAt(0);
            initials = initials.toUpperCase();
            const isSelected = u.id == selectedUserId ? "selected" : "";
            const btnClass =
                u.id == selectedUserId ? "btn-success" : "btn-select-user";
            const btnContent =
                u.id == selectedUserId
                    ? '<i class="bi bi-check-lg"></i>'
                    : "+ Add Member";
            const pointerEvents =
                u.id == selectedUserId ? "pointer-events: none;" : "";
            html += `
                <div class="user-select-item ${isSelected}" onclick="selectUserItem(${u.id})">
                    <div class="d-flex align-items-center">
                        <div class="user-avatar-dark">${initials}</div>
                        <div>
                            <div class="fw-semibold text-dark">${u.name}</div>
                            <small class="text-muted" style="font-size:0.75rem">${u.email}</small>
                        </div>
                    </div>
                    <button class="${btnClass}" style="border:none; border-radius:6px; padding:5px 12px; font-size:0.8rem; ${pointerEvents}">
                        ${btnContent}
                    </button>
                </div>
            `;
        });
        userListContainer.innerHTML = html;
    }

    function checkSubmitButton() {
        if (selectedUserId && inputRole.value.trim() !== "")
            btnSubmitMember.disabled = false;
        else btnSubmitMember.disabled = true;
    }

    function executeSearch() {
        const keyword = searchInput.value.toLowerCase();
        const filteredUsers = availableUsers.filter((u) =>
            u.name.toLowerCase().includes(keyword),
        );
        renderUserList(filteredUsers);
    }

    if (modalAddMember) {
        modalAddMember.addEventListener("show.bs.modal", function () {
            selectedUserId = null;
            inputRole.value = "";
            searchInput.value = "";
            btnSubmitMember.disabled = true;
            userListContainer.innerHTML = `<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm mb-2"></div><div>Loading users...</div></div>`;
            axios
                .get(`/api/users?role=User&exclude_project=${projectId}`)
                .then((res) => {
                    availableUsers = res.data;
                    renderUserList(availableUsers);
                })
                .catch(() => {
                    userListContainer.innerHTML =
                        '<div class="text-center py-4 text-danger">Error loading users.</div>';
                });
        });

        if (btnSearchTrigger)
            btnSearchTrigger.addEventListener("click", executeSearch);
        if (searchInput) {
            searchInput.addEventListener("keypress", function (e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    executeSearch();
                }
            });
            searchInput.addEventListener("input", function () {
                if (this.value === "") renderUserList(availableUsers);
            });
        }
        inputRole.addEventListener("input", checkSubmitButton);
        btnSubmitMember.addEventListener("click", function () {
            const role = inputRole.value;
            if (!selectedUserId || !role) return;
            const payload = {
                user_id: selectedUserId,
                role: role,
                pjk_id: projectId,
            };
            const originalText = btnSubmitMember.innerHTML;
            btnSubmitMember.innerHTML =
                '<span class="spinner-border spinner-border-sm"></span> Adding...';
            btnSubmitMember.disabled = true;
            axios
                .post(`/api/projek/${projectId}/member`, payload)
                .then(() => {
                    bootstrap.Modal.getInstance(modalAddMember).hide();
                    Swal.fire({
                        icon: "success",
                        title: "Member Added",
                        showConfirmButton: false,
                        timer: 1000,
                    });
                    loadMembers();
                })
                .catch((err) => {
                    Swal.fire(
                        "Error",
                        err.response?.data?.message || "Failed",
                        "error",
                    );
                    btnSubmitMember.innerHTML = originalText;
                    btnSubmitMember.disabled = false;
                });
        });
    }
});
