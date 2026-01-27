import axios from "axios";

// 1. Konfigurasi Dasar Axios
const apiToken = localStorage.getItem("api_token");
axios.defaults.headers.common["Authorization"] = `Bearer ${apiToken}`;
axios.defaults.headers.common["Accept"] = "application/json";

document.addEventListener("DOMContentLoaded", function () {
    const urlParts = window.location.pathname.split("/");
    const projectId = urlParts[2]; // Mengambil ID dari URL: /projek/{id}/edit

    /**
     * LOAD DATA PROJEK
     * Mengambil data detail untuk mengisi form awal
     */
    function loadProjectData() {
        axios
            .get(`/api/projek/${projectId}`)
            .then((res) => {
                const data = res.data.detail; 

                // Isi Form Input
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
                Swal.fire("Error", "Gagal mengambil data projek", "error");
            });
    }

    loadProjectData();

    /**
     * HANDLE UPDATE PROJEK (PUT)
     */
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
                        title: "Berhasil!",
                        text: "Data projek telah diperbarui.",
                        timer: 1500,
                        showConfirmButton: false,
                    }).then(() => location.reload());
                })
                .catch((err) => {
                    Swal.fire(
                        "Gagal",
                        err.response?.data?.message ||
                            "Terjadi kesalahan saat menyimpan",
                        "error"
                    );
                });
        });
    }

    /**
     * HANDLE DELETE PROJEK DENGAN KONFIRMASI TEKS
     * Mengharuskan user mengetik kalimat spesifik sebelum menghapus
     */
    const btnHapus = document.getElementById("btnHapusProjek");
    if (btnHapus) {
        btnHapus.addEventListener("click", function () {
            const namaProjek = document.getElementById("pjk_nama").value;
            const confirmationText = "Hapus projek " + namaProjek ;

            Swal.fire({
                title: "Konfirmasi Penghapusan",
                html: `
                    <div class="text-start">
                        <p>Tindakan ini <b>tidak dapat dibatalkan</b>. Seluruh modul, kegiatan, dan tugas dalam <b>${namaProjek}</b> akan dihapus permanen.</p>
                        <p class="mb-2">Silakan ketik kalimat di bawah untuk konfirmasi:</p>
                        <code class="d-block p-2 bg-light border rounded text-center mb-3" style="user-select: none;">${confirmationText}</code>
                    </div>
                `,
                icon: "warning",
                input: "text",
                inputAttributes: {
                    autocapitalize: "off",
                    placeholder: "Ketik kalimat konfirmasi di sini...",
                },
                showCancelButton: true,
                confirmButtonColor: "#d33",
                confirmButtonText: "Hapus Projek",
                cancelButtonText: "Batal",
                showLoaderOnConfirm: true,
                // Validasi teks input
                inputValidator: (value) => {
                    if (!value) {
                        return "Anda harus mengisi kalimat konfirmasi!";
                    }
                    if (value !== confirmationText) {
                        return "Kalimat konfirmasi tidak sesuai!";
                    }
                },
                // Eksekusi API Delete jika validasi lolos
                preConfirm: () => {
                    return axios
                        .delete(`/api/projek/${projectId}`)
                        .then((response) => {
                            return response.data;
                        })
                        .catch((error) => {
                            Swal.showValidationMessage(
                                `Gagal: ${
                                    error.response?.data?.message ||
                                    error.message
                                }`
                            );
                        });
                },
                allowOutsideClick: () => !Swal.isLoading(),
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: "success",
                        title: "Dihapus!",
                        text: "Projek dan seluruh datanya telah dihapus.",
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
