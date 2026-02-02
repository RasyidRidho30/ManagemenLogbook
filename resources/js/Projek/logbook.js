// Di dalam file JS logbook
import axios from "axios";

const apiToken = localStorage.getItem("api_token");
axios.defaults.headers.common["Authorization"] = `Bearer ${apiToken}`;
axios.defaults.headers.common["Accept"] = "application/json";

const formAdd = document.getElementById("formAddLogbook");
if (formAdd) {
    formAdd.addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        axios
            .post("/api/logbook", formData) // Pastikan route API sesuai
            .then((res) => {
                Swal.fire("Berhasil", "Logbook ditambahkan", "success").then(
                    () => location.reload(),
                );
            })
            .catch((err) => {
                Swal.fire("Gagal", "Terjadi kesalahan", "error");
            });
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const handleForm = (formId, method, urlGen) => {
        const form = document.getElementById(formId);
        if (!form) return;
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            const id = document.getElementById("edit_tgs_id")?.value;
            const url = typeof urlGen === "function" ? urlGen(id) : urlGen;

            let data;
            if (formId === "modalAddLogbook") {
                data = {
                    tgs_id: document.getElementById("tgs_id").value,
                    tanggal: document.getElementById("lbk_tanggal").value,
                    deskripsi: document.getElementById("lbk_deskripsi").value,
                    komentar: document.getElementById("lbk_komentar").value,
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
    handleForm("modalAddLogbook", "post", "/api/logbook");
});
