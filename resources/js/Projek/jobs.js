import axios from "axios";

const apiToken = localStorage.getItem("api_token");
if (apiToken) {
    axios.defaults.headers.common["Authorization"] = `Bearer ${apiToken}`;
}
axios.defaults.headers.common["Accept"] = "application/json";

const apiBase = "/api";

const getProjectDates = () => ({
    start: document.getElementById("pjk_start_date")?.value,
    end: document.getElementById("pjk_end_date")?.value,
});

const showAlert = (container, message) => {
    if (container) {
        container.innerHTML = `<div class="alert alert-warning small py-2 mb-0"><i class="bi bi-exclamation-circle me-2"></i>${message}</div>`;
    }
};

const initializeDateValidation = () => {
    const tglMulaiInput = document.getElementById("add_tgl_mulai");
    const tglSelesaiInput = document.getElementById("add_tgl_selesai");
    const alertBox = document.getElementById("alertAddTugas");
    const { start: pjkStart, end: pjkEnd } = getProjectDates();

    if (tglMulaiInput) {
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
                return;
            }

            const mulaiDate = new Date(this.value);
            const pjkStartDate = pjkStart ? new Date(pjkStart) : null;
            const pjkEndDate = pjkEnd ? new Date(pjkEnd) : null;

            if (pjkStartDate && mulaiDate < pjkStartDate) {
                this.value = "";
                showAlert(
                    alertBox,
                    "Start date cannot be before project start date!",
                );
                if (tglSelesaiInput) tglSelesaiInput.value = "";
            } else if (pjkEndDate && mulaiDate > pjkEndDate) {
                this.value = "";
                showAlert(
                    alertBox,
                    "Start date cannot be after project end date!",
                );
                if (tglSelesaiInput) tglSelesaiInput.value = "";
            } else if (tglSelesaiInput) {
                tglSelesaiInput.setAttribute("min", this.value);
                if (pjkEnd) tglSelesaiInput.setAttribute("max", pjkEnd);

                if (
                    tglSelesaiInput.value &&
                    new Date(tglSelesaiInput.value) < mulaiDate
                ) {
                    tglSelesaiInput.value = "";
                }
            }
        });
    }

    if (tglSelesaiInput) {
        if (pjkEnd) tglSelesaiInput.setAttribute("max", pjkEnd);

        tglSelesaiInput.addEventListener("change", function () {
            if (alertBox) alertBox.innerHTML = "";
            if (!this.value) return;

            const selesaiDate = new Date(this.value);
            const mulaiVal = tglMulaiInput?.value;
            const pjkEndDate = pjkEnd ? new Date(pjkEnd) : null;

            if (mulaiVal && selesaiDate < new Date(mulaiVal)) {
                this.value = "";
                showAlert(alertBox, "End date cannot be before start date!");
            } else if (pjkEndDate && selesaiDate > pjkEndDate) {
                this.value = "";
                showAlert(alertBox, "End date cannot exceed project end date!");
            }
        });
    }
};

const initializeEditDateValidation = () => {
    const tglSelesaiInput = document.getElementById("edit_tgl_selesai");
    const alertBox = document.getElementById("alertEditTugas");
    const { end: pjkEnd } = getProjectDates();

    if (tglSelesaiInput) {
        tglSelesaiInput.removeAttribute("min");
        if (pjkEnd) tglSelesaiInput.setAttribute("max", pjkEnd);

        tglSelesaiInput.addEventListener("change", function () {
            if (alertBox) alertBox.innerHTML = "";
            if (this.value) {
                const selesaiDate = new Date(this.value);
                const pjkEndDate = pjkEnd ? new Date(pjkEnd) : null;

                if (pjkEndDate && selesaiDate > pjkEndDate) {
                    this.value = "";
                    showAlert(
                        alertBox,
                        "End date cannot exceed project end date!",
                    );
                }
            }
        });
    }
};

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

    if (document.getElementById("alertAddTugas")) {
        document.getElementById("alertAddTugas").innerHTML = "";
    }

    axios
        .get(`${apiBase}/projek/${projectId}/member`)
        .then((res) => {
            res.data.forEach((member) => {
                selectPic.add(new Option(member.user.name, member.user.id));
            });
        })
        .catch(() =>
            Swal.fire("Error", "Failed to load team members", "error"),
        );

    new bootstrap.Modal(document.getElementById("modalAddTugas")).show();
};

window.openModalEditTugas = (tgsId) => {
    axios
        .get(`${apiBase}/tugas/${tgsId}`)
        .then((res) => {
            const tgs = res.data.data;
            const projectId = window.location.pathname.split("/")[2];

            document.getElementById("edit_tgs_id").value = tgs.id;
            document.getElementById("display_edit_tgs_nama").innerText =
                tgs.nama;
            document.getElementById("edit_nama").value = tgs.nama;
            document.getElementById("edit_kode").value = tgs.kode;
            document.getElementById("edit_bobot").value = tgs.bobot;
            document.getElementById("edit_tgl_selesai").value =
                tgs.tanggal_selesai;
            // document.getElementById("edit_progress").value = Math.round(
            //     tgs.persentase_progress,
            // );

            const selectEditPic = document.getElementById("edit_usr_id");
            selectEditPic.innerHTML = '<option value="">Select PIC...</option>';

            axios
                .get(`${apiBase}/projek/${projectId}/member`)
                .then((resMember) => {
                    resMember.data.forEach((member) => {
                        let opt = new Option(member.user.name, member.user.id);
                        if (member.user.id === tgs.id_pic) opt.selected = true;
                        selectEditPic.add(opt);
                    });
                });

            const modalEl = document.getElementById("modalEditTugas");
            if (document.getElementById("alertEditTugas")) {
                document.getElementById("alertEditTugas").innerHTML = "";
            }

            modalEl.removeAttribute("aria-hidden");
            modalEl.addEventListener(
                "shown.bs.modal",
                () => initializeEditDateValidation(),
                { once: true },
            );

            new bootstrap.Modal(modalEl).show();
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
        .get(`${apiBase}/modul/${mdlId}`)
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

document.addEventListener("DOMContentLoaded", () => {
    const showSuccess = (message) => {
        Swal.fire({
            icon: "success",
            title: "Success!",
            text: message,
            showConfirmButton: false,
            timer: 1500,
            toast: true,
            position: "top-end",
        }).then(() => location.reload());
    };

    const handleForm = (formId, method, urlGen) => {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener("submit", function (e) {
            e.preventDefault();
            const { start, end } = getProjectDates();
            const pjkStartDate = start ? new Date(start) : null;
            const pjkEndDate = end ? new Date(end) : null;

            if (formId === "formAddTugas") {
                const alertBox = document.getElementById("alertAddTugas");
                const tglMulai = document.getElementById("add_tgl_mulai").value;
                const tglSelesai =
                    document.getElementById("add_tgl_selesai").value;

                if (!tglMulai || !tglSelesai)
                    return showAlert(alertBox, "Dates are required!");

                const dMulai = new Date(tglMulai);
                const dSelesai = new Date(tglSelesai);

                if (pjkStartDate && dMulai < pjkStartDate)
                    return showAlert(
                        alertBox,
                        "Start date cannot be before project start date!",
                    );
                if (dSelesai < dMulai)
                    return showAlert(
                        alertBox,
                        "End date cannot be before start date!",
                    );
                if (pjkEndDate && dSelesai > pjkEndDate)
                    return showAlert(
                        alertBox,
                        "End date cannot exceed project end date!",
                    );
            }

            if (formId === "formEditTugas") {
                const alertBox = document.getElementById("alertEditTugas");
                const tglSelesai =
                    document.getElementById("edit_tgl_selesai").value;

                if (!tglSelesai)
                    return showAlert(alertBox, "End date is required!");
                if (pjkEndDate && new Date(tglSelesai) > pjkEndDate)
                    return showAlert(
                        alertBox,
                        "End date cannot exceed project end date!",
                    );
            }

            const id = document.getElementById("edit_tgs_id")?.value;
            const url = typeof urlGen === "function" ? urlGen(id) : urlGen;

            const data =
                formId === "formEditTugas"
                    ? {
                          nama: document.getElementById("edit_nama").value,
                          usr_id: document.getElementById("edit_usr_id").value,
                          tgl_selesai:
                              document.getElementById("edit_tgl_selesai").value,
                          //   progress:
                          //       document.getElementById("edit_progress").value,
                      }
                    : Object.fromEntries(new FormData(this));

            axios({ method, url, data })
                .then(() => showSuccess("Operation successful."))
                .catch((err) =>
                    Swal.fire({
                        icon: "error",
                        title: "Failed",
                        text:
                            err.response?.data?.message || "An error occurred",
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: "top-end",
                    }),
                );
        });
    };

    handleForm("formAddModul", "post", `${apiBase}/modul`);
    handleForm("formAddKegiatan", "post", `${apiBase}/kegiatan`);
    handleForm("formAddTugas", "post", `${apiBase}/tugas`);
    handleForm("formEditTugas", "put", (id) => `${apiBase}/tugas/${id}`);

    const formEditModul = document.getElementById("formEditModul");
    if (formEditModul) {
        formEditModul.addEventListener("submit", function (e) {
            e.preventDefault();
            const mdlId = document.getElementById("edit_mdl_id").value;
            const payload = {
                nama: document.getElementById("edit_mdl_nama").value,
                urut: document.getElementById("edit_mdl_urut").value,
            };

            axios
                .put(`${apiBase}/modul/${mdlId}`, payload)
                .then(() => {
                    bootstrap.Modal.getInstance(
                        document.getElementById("modalEditModul"),
                    ).hide();
                    showSuccess("The module has been updated.");
                })
                .catch((err) =>
                    Swal.fire(
                        "Error",
                        err.response?.data?.message || "An error occurred",
                        "error",
                    ),
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
                text: `Module "${mdlNama}" and all its contents will be permanently deleted!`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                confirmButtonText: "Yes, Delete!",
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`${apiBase}/modul/${mdlId}`).then(() => {
                        bootstrap.Modal.getInstance(
                            document.getElementById("modalEditModul"),
                        ).hide();
                        showSuccess("The module has been removed.");
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
                .put(`${apiBase}/kegiatan/${kgtId}`, { nama })
                .then(() => {
                    bootstrap.Modal.getInstance(
                        document.getElementById("modalEditKegiatan"),
                    ).hide();
                    showSuccess("The activity has been updated.");
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
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`${apiBase}/kegiatan/${kgtId}`).then(() => {
                        bootstrap.Modal.getInstance(
                            document.getElementById("modalEditKegiatan"),
                        ).hide();
                        showSuccess("The activity has been removed.");
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
            const btnSubmit = this.querySelector('button[type="submit"]');
            const originalText = btnSubmit.innerHTML;

            btnSubmit.innerHTML =
                '<span class="spinner-border spinner-border-sm"></span> Importing...';
            btnSubmit.disabled = true;

            axios
                .post(this.action, new FormData(this), {
                    headers: { "Content-Type": "multipart/form-data" },
                })
                .then(() => {
                    const modalEl = document.getElementById("modalImportExcel");
                    if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
                    showSuccess("Import Successful!");
                })
                .catch((err) => {
                    btnSubmit.innerHTML = originalText;
                    btnSubmit.disabled = false;
                    Swal.fire(
                        "Import Failed",
                        err.response?.data?.message || "Error during import.",
                        "error",
                    );
                });
        });
    }

    const btnHapusTugas = document.getElementById("btnHapusTugas");
    if (btnHapusTugas) {
        btnHapusTugas.onclick = () => {
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
            }).then((result) => {
                if (result.isConfirmed) {
                    axios
                        .delete(`${apiBase}/tugas/${id}`)
                        .then(() => showSuccess("The task has been removed."));
                }
            });
        };
    }
});
