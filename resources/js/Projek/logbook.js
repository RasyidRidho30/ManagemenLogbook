import axios from "axios";

const apiToken = localStorage.getItem("api_token");
if (apiToken) {
    axios.defaults.headers.common["Authorization"] = `Bearer ${apiToken}`;
}
axios.defaults.headers.common["Accept"] = "application/json";

let usedTaskIds = [];

const updateUsedTaskIds = async () => {
    const projectId = window.location.pathname.split("/")[2];
    try {
        const res = await axios.get(`/api/projek/${projectId}/logbook`);
        const existingLogbooks = res.data.data || [];
        usedTaskIds = existingLogbooks.map((log) =>
            parseInt(log.id_tugas || log.tgs_id),
        );
        return usedTaskIds;
    } catch (err) {
        console.error(err);
        return [];
    }
};

const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString("id-ID", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    });
};

document.addEventListener("DOMContentLoaded", () => {
    const formAdd = document.getElementById("formAddLogbook");
    const modalElement = document.getElementById("modalAddLogbook");
    const detailModal = document.getElementById("modalDetailLogbook");
    const formEditComment = document.getElementById("formEditComment");

    if (formAdd) {
        formAdd.addEventListener("submit", (e) => {
            e.preventDefault();

            const tgsId = document.getElementById("tgs_id").value;
            const tgsSelect = document.getElementById("tgs_id");
            const selectedOption = tgsSelect.options[tgsSelect.selectedIndex];
            const progressValue = document.getElementById("lbk_progress").value;

            if (!tgsId) {
                Swal.fire(
                    "Validation Error",
                    "Please select a task first",
                    "warning",
                );
                return;
            }

            if (
                selectedOption.disabled ||
                usedTaskIds.includes(parseInt(tgsId))
            ) {
                Swal.fire(
                    "Task Already Logged",
                    "This task already has a logbook entry.",
                    "warning",
                );
                return;
            }

            const data = {
                tgs_id: tgsId,
                tanggal: document.getElementById("lbk_tanggal").value,
                deskripsi: document.getElementById("lbk_deskripsi").value,
                komentar: document.getElementById("lbk_komentar").value || "",
                progress: progressValue || 0,
            };

            axios
                .post("/api/logbook", data)
                .then(() => {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        showConfirmButton: false,
                        timer: 1500,
                    }).then(() => location.reload());
                })
                .catch((err) => {
                    Swal.fire(
                        "Failed",
                        err.response?.data?.message || "An error occurred",
                        "error",
                    );
                });
        });
    }

    if (modalElement) {
        modalElement.addEventListener("show.bs.modal", () => {
            formAdd?.reset();

            updateUsedTaskIds().then(() => {
                const tgsSelect = document.getElementById("tgs_id");
                const options = tgsSelect.querySelectorAll("option");
                const LOG_TAG = "[ALREADY LOGGED]";

                options.forEach((option) => {
                    const isUsed = usedTaskIds.includes(parseInt(option.value));
                    option.disabled = isUsed;

                    if (isUsed) {
                        if (!option.textContent.includes(LOG_TAG)) {
                            option.textContent = `${LOG_TAG} ${option.textContent}`;
                        }
                    } else {
                        option.textContent = option.textContent.replace(
                            `${LOG_TAG} `,
                            "",
                        );
                    }
                });
            });
        });
    }

    if (detailModal) {
        detailModal.addEventListener("show.bs.modal", (event) => {
            const button = event.relatedTarget;
            const progress = button.getAttribute("data-progress") || 0;
            const komentar = button.getAttribute("data-komentar") || "";

            document.getElementById("detail-tanggal").textContent = formatDate(
                button.getAttribute("data-tanggal"),
            );
            document.getElementById("detail-task").textContent =
                button.getAttribute("data-task");
            document.getElementById("detail-kode").textContent =
                `[${button.getAttribute("data-kode")}]`;
            document.getElementById("detail-deskripsi").textContent =
                button.getAttribute("data-deskripsi");
            document.getElementById("detail-pic").innerHTML =
                `<span class="badge bg-light text-dark border">${button.getAttribute("data-pic")}</span>`;
            document.getElementById("detail-start").textContent = formatDate(
                button.getAttribute("data-start"),
            );
            document.getElementById("detail-end").textContent = formatDate(
                button.getAttribute("data-end"),
            );

            const progressBadge = document.getElementById("detail-progress");
            progressBadge.textContent = `${progress}%`;
            progressBadge.className =
                progress >= 100 ? "badge bg-success" : "badge bg-secondary";

            const komentarEl = document.getElementById("detail-komentar");
            komentarEl.textContent = komentar.trim() ? komentar : "-";
            if (!komentar.trim()) komentarEl.classList.add("text-muted");

            document.getElementById("lbk_id_edit").value =
                button.getAttribute("data-lbk-id");
            document.getElementById("komentarEdit").value = komentar;
        });
    }

    if (formEditComment) {
        formEditComment.addEventListener("submit", (e) => {
            e.preventDefault();
            const lbkId = document.getElementById("lbk_id_edit").value;
            const komentar =
                document.getElementById("komentarEdit").value || "";

            axios
                .put(`/api/logbook/${lbkId}`, { lbk_komentar: komentar })
                .then(() => {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Comment saved successfully",
                        showConfirmButton: false,
                        timer: 1500,
                    }).then(() => location.reload());
                })
                .catch((err) => {
                    Swal.fire(
                        "Failed",
                        err.response?.data?.message || "An error occurred",
                        "error",
                    );
                });
        });
    }
});
