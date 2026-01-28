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

    axios.get(`/api/projek/${projectId}/members`).then((res) => {
        res.data.data.forEach((member) => {
            selectPic.add(
                new Option(
                    `${member.usr_first_name} ${member.usr_last_name}`,
                    member.usr_id,
                ),
            );
        });
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

            axios.get(`/api/projek/${projectId}/members`).then((resMember) => {
                resMember.data.data.forEach((member) => {
                    let opt = new Option(
                        `${member.usr_first_name} ${member.usr_last_name}`,
                        member.usr_id,
                    );
                    if (member.usr_id === tgs.id_pic) opt.selected = true;
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
