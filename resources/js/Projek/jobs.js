import axios from "axios";

const apiToken = localStorage.getItem("api_token");
axios.defaults.headers.common["Authorization"] = `Bearer ${apiToken}`;
axios.defaults.headers.common["Accept"] = "application/json";

// ========== DATE VALIDATION UTILITY FUNCTIONS ==========
// Mengambil batas tanggal dari hidden input di Blade
const getProjectDates = () => {
    const start = document.getElementById("pjk_start_date")?.value;
    const end = document.getElementById("pjk_end_date")?.value;
    return { start, end };
};

const initializeDateValidation = () => {
    const tglMulaiInput = document.getElementById("add_tgl_mulai");
    const tglSelesaiInput = document.getElementById("add_tgl_selesai");
    const alertBox = document.getElementById("alertAddTugas");
    const { start: pjkStart, end: pjkEnd } = getProjectDates();

    if (tglMulaiInput) {
        // Set batas minimal dan maksimal sesuai umur Projek
        if (pjkStart) tglMulaiInput.setAttribute("min", pjkStart);
        if (pjkEnd) tglMulaiInput.setAttribute("max", pjkEnd);

        tglMulaiInput.addEventListener("change", function () {
            if (alertBox) alertBox.innerHTML = "";

            if (!this.value) {
                if (tglSelesaiInput) {
                    tglSelesaiInput.disabled = false;
                    tglSelesaiInput.value = "";
                    if (pjkStart) tglSelesaiInput.setAttribute("min", pjkStart);
                }
            } else {
                const mulaiDate = new Date(this.value);
                const pjkStartDate = pjkStart ? new Date(pjkStart) : null;
                const pjkEndDate = pjkEnd ? new Date(pjkEnd) : null;

                // Validasi: Start Date tidak boleh mendahului Start Projek
                if (pjkStartDate && mulaiDate < pjkStartDate) {
                    this.value = "";
                    if (alertBox)
                        alertBox.innerHTML = `<div class="alert alert-warning small py-2 mb-0"><i class="bi bi-exclamation-circle me-2"></i>Start date cannot be before project start date!</div>`;
                    if (tglSelesaiInput) tglSelesaiInput.value = "";
                }
                // Validasi: Start Date tidak boleh melebihi End Projek
                else if (pjkEndDate && mulaiDate > pjkEndDate) {
                    this.value = "";
                    if (alertBox)
                        alertBox.innerHTML = `<div class="alert alert-warning small py-2 mb-0"><i class="bi bi-exclamation-circle me-2"></i>Start date cannot be after project end date!</div>`;
                    if (tglSelesaiInput) tglSelesaiInput.value = "";
                } else {
                    // Jika valid, set 'min' untuk tgl_selesai berdasarkan tgl_mulai ini
                    if (tglSelesaiInput) {
                        tglSelesaiInput.setAttribute("min", this.value);
                        if (pjkEnd) tglSelesaiInput.setAttribute("max", pjkEnd);

                        // Reset tgl_selesai jika terlanjur salah
                        if (tglSelesaiInput.value) {
                            const selesaiDate = new Date(tglSelesaiInput.value);
                            if (selesaiDate < mulaiDate) {
                                tglSelesaiInput.value = "";
                            }
                        }
                    }
                }
            }
        });
    }

    if (tglSelesaiInput) {
        if (pjkEnd) tglSelesaiInput.setAttribute("max", pjkEnd);

        tglSelesaiInput.addEventListener("change", function () {
            if (alertBox) alertBox.innerHTML = "";

            if (this.value) {
                const selesaiDate = new Date(this.value);
                const mulaiVal = tglMulaiInput ? tglMulaiInput.value : null;
                const pjkEndDate = pjkEnd ? new Date(pjkEnd) : null;

                if (mulaiVal && selesaiDate < new Date(mulaiVal)) {
                    this.value = "";
                    if (alertBox)
                        alertBox.innerHTML =
                            '<div class="alert alert-warning small py-2 mb-0"><i class="bi bi-exclamation-circle me-2"></i>End date cannot be before start date!</div>';
                } else if (pjkEndDate && selesaiDate > pjkEndDate) {
                    this.value = "";
                    if (alertBox)
                        alertBox.innerHTML =
                            '<div class="alert alert-warning small py-2 mb-0"><i class="bi bi-exclamation-circle me-2"></i>End date cannot exceed project end date!</div>';
                }
            }
        });
    }
};

const initializeEditDateValidation = () => {
    const tglSelesaiInput = document.getElementById("edit_tgl_selesai");
    const alertBox = document.getElementById("alertEditTugas");
    const { end: pjkEnd } = getProjectDates();

    if (tglSelesaiInput) {
        // Hapus batas 'today' dan ganti dengan batas akhir projek
        tglSelesaiInput.removeAttribute("min");
        if (pjkEnd) tglSelesaiInput.setAttribute("max", pjkEnd);

        tglSelesaiInput.addEventListener("change", function () {
            if (alertBox) alertBox.innerHTML = "";
            if (this.value) {
                const selesaiDate = new Date(this.value);
                const pjkEndDate = pjkEnd ? new Date(pjkEnd) : null;

                if (pjkEndDate && selesaiDate > pjkEndDate) {
                    this.value = "";
                    if (alertBox)
                        alertBox.innerHTML =
                            '<div class="alert alert-warning small py-2 mb-0"><i class="bi bi-exclamation-circle me-2"></i>End date cannot exceed project end date!</div>';
                }
            }
        });
    }
};

// ... MODAL HANDLERS (Tidak Berubah) ...

window.openModalKegiatan = (mdlId, mdlNama) => {
    document.getElementById("input_mdl_id").value = mdlId;
    document.getElementById("title_mdl").innerText = mdlNama;
    new bootstrap.Modal(document.getElementById("modalAddKegiatan")).show();
};

window.openModalTugas = (kgtId, kgtNama) => {
    document.getElementById("input_kgt_id").value = kgtId;
    document.getElementById("title_kgt").innerText = kgtNama;

    const projectId = window.location.pathname.split("/")[2];
    const selectPic = document.getElementById("select_pic");
    selectPic.innerHTML = '<option value="">Select Team Member PIC...</option>';

    initializeDateValidation();

    const alertBox = document.getElementById("alertAddTugas");
    if (alertBox) alertBox.innerHTML = "";

    axios
        .get(`/api/projek/${projectId}/member`)
        .then((res) => {
            res.data.forEach((member) => {
                selectPic.add(new Option(member.user.name, member.user.id));
            });
        })
        .catch((err) => {
            console.error("Failed to load members:", err);
            Swal.fire("Error", "Failed to load team members", "error");
        });
    new bootstrap.Modal(document.getElementById("modalAddTugas")).show();
};

window.openModalEditTugas = (tgsId) => {
    axios
        .get(`/api/tugas/${tgsId}`)
        .then((res) => {
            const tgs = res.data.data;
            document.getElementById("edit_tgs_id").value = tgs.id;
            document.getElementById("display_edit_tgs_nama").innerText =
                tgs.nama;
            document.getElementById("edit_nama").value = tgs.nama;
            document.getElementById("edit_kode").value = tgs.kode;
            document.getElementById("edit_bobot").value = tgs.bobot;
            document.getElementById("edit_tgl_selesai").value =
                tgs.tanggal_selesai;
            document.getElementById("edit_progress").value = Math.round(
                tgs.persentase_progress,
            );

            const projectId = window.location.pathname.split("/")[2];
            const selectEditPic = document.getElementById("edit_usr_id");
            selectEditPic.innerHTML = '<option value="">Select PIC...</option>';

            axios.get(`/api/projek/${projectId}/member`).then((resMember) => {
                resMember.data.forEach((member) => {
                    let opt = new Option(member.user.name, member.user.id);
                    if (member.user.id === tgs.id_pic) opt.selected = true;
                    selectEditPic.add(opt);
                });
            });

            const modalEl = document.getElementById("modalEditTugas");
            const alertBox = document.getElementById("alertEditTugas");
            if (alertBox) alertBox.innerHTML = "";

            modalEl.removeAttribute("aria-hidden");
            const modal = new bootstrap.Modal(modalEl);

            modalEl.addEventListener(
                "shown.bs.modal",
                function () {
                    initializeEditDateValidation();
                },
                { once: true },
            );

            modal.show();
        })
        .catch(() =>
            Swal.fire("Error", "Failed to retrieve task details", "error"),
        );
};

window.openModalEditKegiatan = (kgtId, kgtNama) => {
    document.getElementById("edit_kgt_id").value = kgtId;
    document.getElementById("edit_kgt_nama").value = kgtNama;
    new bootstrap.Modal(document.getElementById("modalEditKegiatan")).show();
};

window.openModalEditModul = (mdlId, mdlNama) => {
    axios
        .get(`/api/modul/${mdlId}`)
        .then((res) => {
            const mdl = res.data.data ?? res.data;
            document.getElementById("edit_mdl_id").value = mdl.id ?? mdlId;
            document.getElementById("edit_mdl_nama").value =
                mdl.nama ?? mdlNama;
            document.getElementById("edit_mdl_urut").value = mdl.urut ?? 1;
            new bootstrap.Modal(
                document.getElementById("modalEditModul"),
            ).show();
        })
        .catch(() => {
            document.getElementById("edit_mdl_id").value = mdlId;
            document.getElementById("edit_mdl_nama").value = mdlNama;
            document.getElementById("edit_mdl_urut").value = 1;
            new bootstrap.Modal(
                document.getElementById("modalEditModul"),
            ).show();
        });
};

// ========== FORM SUBMIT HANDLERS ==========

document.addEventListener("DOMContentLoaded", () => {
    const handleForm = (formId, method, urlGen) => {
        const form = document.getElementById(formId);
        if (!form) return;
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            const { start: pjkStart, end: pjkEnd } = getProjectDates();
            const pjkStartDate = pjkStart ? new Date(pjkStart) : null;
            const pjkEndDate = pjkEnd ? new Date(pjkEnd) : null;

            // Validasi Submit formAddTugas
            if (formId === "formAddTugas") {
                const alertBox = document.getElementById("alertAddTugas");
                const tglMulaiValue =
                    document.getElementById("add_tgl_mulai").value;
                const tglSelesaiValue =
                    document.getElementById("add_tgl_selesai").value;

                if (!tglMulaiValue || !tglSelesaiValue) {
                    if (alertBox)
                        alertBox.innerHTML =
                            '<div class="alert alert-warning small py-2 mb-0"><i class="bi bi-exclamation-circle me-2"></i>Dates are required!</div>';
                    return;
                }

                const mulaiDate = new Date(tglMulaiValue);
                const selesaiDate = new Date(tglSelesaiValue);

                if (pjkStartDate && mulaiDate < pjkStartDate) {
                    if (alertBox)
                        alertBox.innerHTML =
                            '<div class="alert alert-warning small py-2 mb-0"><i class="bi bi-exclamation-circle me-2"></i>Start date cannot be before project start date!</div>';
                    return;
                }
                if (selesaiDate < mulaiDate) {
                    if (alertBox)
                        alertBox.innerHTML =
                            '<div class="alert alert-warning small py-2 mb-0"><i class="bi bi-exclamation-circle me-2"></i>End date cannot be before start date!</div>';
                    return;
                }
                if (pjkEndDate && selesaiDate > pjkEndDate) {
                    if (alertBox)
                        alertBox.innerHTML =
                            '<div class="alert alert-warning small py-2 mb-0"><i class="bi bi-exclamation-circle me-2"></i>End date cannot exceed project end date!</div>';
                    return;
                }
                if (alertBox) alertBox.innerHTML = "";
            }

            // Validasi Submit formEditTugas
            if (formId === "formEditTugas") {
                const alertBox = document.getElementById("alertEditTugas");
                const tglSelesaiValue =
                    document.getElementById("edit_tgl_selesai").value;

                if (!tglSelesaiValue) {
                    if (alertBox)
                        alertBox.innerHTML =
                            '<div class="alert alert-warning small py-2 mb-0"><i class="bi bi-exclamation-circle me-2"></i>End date is required!</div>';
                    return;
                }

                const selesaiDate = new Date(tglSelesaiValue);
                if (pjkEndDate && selesaiDate > pjkEndDate) {
                    if (alertBox)
                        alertBox.innerHTML =
                            '<div class="alert alert-warning small py-2 mb-0"><i class="bi bi-exclamation-circle me-2"></i>End date cannot exceed project end date!</div>';
                    return;
                }
                if (alertBox) alertBox.innerHTML = "";
            }

            const id = document.getElementById("edit_tgs_id")?.value;
            const url = typeof urlGen === "function" ? urlGen(id) : urlGen;
            let data;

            if (formId === "formEditTugas") {
                data = {
                    nama: document.getElementById("edit_nama").value,
                    usr_id: document.getElementById("edit_usr_id").value,
                    tgl_selesai:
                        document.getElementById("edit_tgl_selesai").value,
                    progress: document.getElementById("edit_progress").value,
                };
            } else {
                data = Object.fromEntries(new FormData(this));
            }

            axios({ method, url, data })
                .then(() => {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "The task has been updated.",
                        showConfirmButton: false,
                        timer: 1500,
                        toast: true,
                        position: "top-end",
                    }).then(() => location.reload());
                })
                .catch((err) =>
                    Swal.fire({
                        icon: "error",
                        title: "Failed",
                        text: err.response?.data?.message || "An error occurred",
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: "top-end",
                    }),
                   
                );
        });
    };

    handleForm("formAddModul", "post", "/api/modul");
    handleForm("formAddKegiatan", "post", "/api/kegiatan");
    handleForm("formAddTugas", "post", "/api/tugas");
    handleForm("formEditTugas", "put", (id) => `/api/tugas/${id}`);

    const formEditModul = document.getElementById("formEditModul");
    if (formEditModul) {
        formEditModul.addEventListener("submit", function (e) {
            e.preventDefault();
            const mdlId = document.getElementById("edit_mdl_id").value;
            const nama = document.getElementById("edit_mdl_nama").value;
            const urut = document.getElementById("edit_mdl_urut").value;

            axios
                .put(`/api/modul/${mdlId}`, { nama, urut })
                .then(() => {
                    bootstrap.Modal.getInstance(
                        document.getElementById("modalEditModul"),
                    ).hide();
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "The module has been updated.",
                        showConfirmButton: false,
                        timer: 1500,
                        toast: true,
                        position: "top-end",
                    }).then(() => location.reload());
                })
                .catch((err) =>
                    Swal.fire({
                        icon: "error",
                        title: "Failed",
                        text: err.response?.data?.message || "An error occurred",
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: "top-end",
                    }),
                );
        });
    }

    const btnHapusModul = document.getElementById("btnHapusModul");
    if (btnHapusModul) {
        btnHapusModul.onclick = () => {
            const mdlId = document.getElementById("edit_mdl_id").value;
            const mdlNama = document.getElementById("edit_mdl_nama").value;

            Swal.fire({
                title: "Delete Module?",
                text: `Module "${mdlNama}" and all its activities and tasks will be permanently deleted!`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                confirmButtonText: "Yes, Delete!",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`/api/modul/${mdlId}`).then(() => {
                        bootstrap.Modal.getInstance(
                            document.getElementById("modalEditModul"),
                        ).hide();
                        Swal.fire({
                            icon: "success",
                            title: "Deleted!",
                            text:"The module has been removed.",
                            timer: 1500,
                            showConfirmButton: false,
                            toast: true,
                            position: "top-end",
                        }).then(() => location.reload());
                    });
                }
            });
        };
    }

    const formEditKegiatan = document.getElementById("formEditKegiatan");
    if (formEditKegiatan) {
        formEditKegiatan.addEventListener("submit", function (e) {
            e.preventDefault();
            const kgtId = document.getElementById("edit_kgt_id").value;
            const nama = document.getElementById("edit_kgt_nama").value;

            axios
                .put(`/api/kegiatan/${kgtId}`, { nama })
                .then(() => {
                    bootstrap.Modal.getInstance(
                        document.getElementById("modalEditKegiatan"),
                    ).hide();
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "The activity has been updated.",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 1500,
                    }).then(() => location.reload());
                })
                .catch((err) =>
                    Swal.fire(
                        "Failed",
                        err.response?.data?.message || "An error occurred",
                        "error",
                    ),
                );
        });
    }

    const btnHapusKegiatan = document.getElementById("btnHapusKegiatan");
    if (btnHapusKegiatan) {
        btnHapusKegiatan.onclick = () => {
            const kgtId = document.getElementById("edit_kgt_id").value;
            const kgtNama = document.getElementById("edit_kgt_nama").value;

            Swal.fire({
                title: "Delete Activity?",
                text: `Activity "${kgtNama}" and all its tasks will be permanently deleted!`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                confirmButtonText: "Yes, Delete!",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`/api/kegiatan/${kgtId}`).then(() => {
                        bootstrap.Modal.getInstance(
                            document.getElementById("modalEditKegiatan"),
                        ).hide();
                        Swal.fire(
                            {
                                icon: "success",
                                title: "Deleted!",
                                text: "The activity has been removed.",
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 2000,
                                toast: true,
                                position: "top-end",
                            }
                        ).then(() => location.reload());
                    });
                }
            });
        };
    }

    const formImportExcel = document.querySelector(
        'form[action*="jobs/import"]',
    );
     if (formImportExcel) {
         formImportExcel.addEventListener("submit", function (e) {
             e.preventDefault();
             const url = this.action;
             const formData = new FormData(this);
             const btnSubmit = this.querySelector('button[type="submit"]');
             const originalText = btnSubmit.innerHTML;

             btnSubmit.innerHTML =
                 '<span class="spinner-border spinner-border-sm"></span> Importing...';
             btnSubmit.disabled = true;

             axios
                 .post(url, formData, {
                     headers: { "Content-Type": "multipart/form-data" },
                 })
                 .then(() => {
                     const modalEl =
                         document.getElementById("modalImportExcel");
                     if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
                     Swal.fire({
                         icon: "success",
                         title: "Import Successful!",
                         text: "Data jobs berhasil dimasukkan.",
                         showConfirmButton: false,
                         timer: 1500,
                     }).then(() => location.reload());
                 })
                 .catch((err) => {
                     btnSubmit.innerHTML = originalText;
                     btnSubmit.disabled = false;
                     Swal.fire(
                         "Import Failed",
                         err.response?.data?.message ||
                             "Terjadi kesalahan saat import data.",
                         "error",
                     );
                 });
         });
     }


    const btnHapus = document.getElementById("btnHapusTugas");
    if (btnHapus) {
        btnHapus.onclick = () => {
            const id = document.getElementById("edit_tgs_id").value;
            const nama = document.getElementById(
                "display_edit_tgs_nama",
            ).innerText;

            Swal.fire({
                title: "Delete Task?",
                text: `Task "${nama}" will be permanently deleted!`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                confirmButtonText: "Yes, Delete!",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`/api/tugas/${id}`).then(() => {
                        Swal.fire(
                            {
                                icon: "success",
                                title: "Deleted!",
                                text: "The task has been removed.",
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 2000,
                            }
                        ).then(() => location.reload());
                    });
                }
            });
        };
    }
});
