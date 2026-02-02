import axios from "axios";

const apiToken = localStorage.getItem("api_token");
axios.defaults.headers.common["Authorization"] = `Bearer ${apiToken}`;
axios.defaults.headers.common["Accept"] = "application/json";

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
            modalEl.removeAttribute("aria-hidden");
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
            console.error("Failed to fetch module details");
            // Fallback: just set the name and use default sequence
            document.getElementById("edit_mdl_id").value = mdlId;
            document.getElementById("edit_mdl_nama").value = mdlNama;
            document.getElementById("edit_mdl_urut").value = 1;
            new bootstrap.Modal(
                document.getElementById("modalEditModul"),
            ).show();
        });
};

document.addEventListener("DOMContentLoaded", () => {
    const handleForm = (formId, method, urlGen) => {
        const form = document.getElementById(formId);
        if (!form) return;
        form.addEventListener("submit", function (e) {
            e.preventDefault();
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
                        Swal.fire(
                            "Deleted!",
                            "The module has been removed.",
                            "success",
                        ).then(() => location.reload());
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

    // === DELETE KEGIATAN ===
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
                            "Deleted!",
                            "The activity has been removed.",
                            "success",
                        ).then(() => location.reload());
                    });
                }
            });
        };
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
                            "Deleted!",
                            "The task has been removed.",
                            "success",
                        ).then(() => location.reload());
                    });
                }
            });
        };
    }
});
