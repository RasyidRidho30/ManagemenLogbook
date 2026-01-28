import axios from "axios";

const apiToken = localStorage.getItem("api_token");
axios.defaults.headers.common["Authorization"] = `Bearer ${apiToken}`;
axios.defaults.headers.common["Accept"] = "application/json";

document.addEventListener("DOMContentLoaded", function () {
    const urlParts = window.location.pathname.split("/");
    const projectId = urlParts[2];

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
            .catch(() => {
                Swal.fire("Error", "Failed to retrieve project data", "error");
            });
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
                .then(() => {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Project data has been updated.",
                        timer: 1500,
                        showConfirmButton: false,
                    }).then(() => location.reload());
                })
                .catch((err) => {
                    Swal.fire(
                        "Failed",
                        err.response?.data?.message ||
                            "An error occurred while saving",
                        "error",
                    );
                });
        });
    }

    const btnHapus = document.getElementById("btnHapusProjek");
    if (btnHapus) {
        btnHapus.addEventListener("click", function () {
            const namaProjek = document.getElementById("pjk_nama").value;
            const confirmationText = "Delete project " + namaProjek;

            Swal.fire({
                title: "Confirm Deletion",
                html: `
                    <div class="text-start">
                        <p>This action <b>cannot be undone</b>. All modules, activities, and tasks within <b>${namaProjek}</b> will be permanently deleted.</p>
                        <p class="mb-2">Please type the sentence below to confirm:</p>
                        <code class="d-block p-2 bg-light border rounded text-center mb-3" style="user-select: none;">${confirmationText}</code>
                    </div>
                `,
                icon: "warning",
                input: "text",
                inputAttributes: {
                    autocapitalize: "off",
                    placeholder: "Type the confirmation sentence here...",
                },
                showCancelButton: true,
                confirmButtonColor: "#d33",
                confirmButtonText: "Delete Project",
                cancelButtonText: "Cancel",
                showLoaderOnConfirm: true,
                inputValidator: (value) => {
                    if (!value) {
                        return "You must enter the confirmation sentence!";
                    }
                    if (value !== confirmationText) {
                        return "The confirmation sentence does not match!";
                    }
                },
                preConfirm: () => {
                    return axios
                        .delete(`/api/projek/${projectId}`)
                        .then((response) => {
                            return response.data;
                        })
                        .catch((error) => {
                            Swal.showValidationMessage(
                                `Failed: ${
                                    error.response?.data?.message ||
                                    error.message
                                }`,
                            );
                        });
                },
                allowOutsideClick: () => !Swal.isLoading(),
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: "success",
                        title: "Deleted!",
                        text: "The project and all its data have been deleted.",
                        showConfirmButton: false,
                        timer: 1500,
                    }).then(() => {
                        window.location.href = "/projek";
                    });
                }
            });
        });
    }
});
