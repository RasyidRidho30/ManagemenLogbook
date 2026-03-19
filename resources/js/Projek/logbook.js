import axios from "axios";

const apiToken = localStorage.getItem("api_token");
if (apiToken) {
    axios.defaults.headers.common["Authorization"] = `Bearer ${apiToken}`;
}
axios.defaults.headers.common["Accept"] = "application/json";

let usedTaskIds = [];

const updateUsedTaskIds = async () => {
    const projectId = window.location.pathname.split('/')[2];
    try {
        const res = await axios.get(`/api/projek/${projectId}/logbook`);
        const existingLogbooks = res.data.data || [];

        const today = new Date().toISOString().split('T')[0];

        // Task yang sudah punya entry hari ini (tidak bisa add baru, hanya edit)
        const todayTaskIds = existingLogbooks
            .filter(log => log.lbk_tanggal?.split('T')[0] === today || log.lbk_tanggal === today)
            .map(log => parseInt(log.tgs_id));

        usedTaskIds = todayTaskIds;
        return { existingLogbooks, todayTaskIds };
    } catch (err) {
        console.error(err);
        return { existingLogbooks: [], todayTaskIds: [] };
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
        modalElement.addEventListener('show.bs.modal', async () => {
            formAdd?.reset();

            const projectId = window.location.pathname.split('/')[2];
            const res = await axios.get(`/api/projek/${projectId}/logbook`);
            const existingLogbooks = res.data.data || [];
            const today = new Date().toISOString().split('T')[0];

            // Hitung total progress per task
            const progressMap = {};
            existingLogbooks.forEach(log => {
                const id = parseInt(log.tgs_id);
                progressMap[id] = (progressMap[id] || 0) + parseInt(log.lbk_progress || 0);
            });

            // Task yang sudah ada entry hari ini
            const todayTaskIds = existingLogbooks
                .filter(log => (log.lbk_tanggal?.split('T')[0] ?? log.lbk_tanggal) === today)
                .map(log => parseInt(log.tgs_id));

            usedTaskIds = todayTaskIds;

            const tgsSelect = document.getElementById('tgs_id');
            const options   = tgsSelect.querySelectorAll('option');

            options.forEach(option => {
                const id    = parseInt(option.value);
                const total = progressMap[id] || 0;

                // Hapus label lama dulu
                option.textContent = option.textContent
                    .replace(/\[ALREADY LOGGED\] /g, '')
                    .replace(/\[COMPLETED\] /g, '');
                option.disabled = false;
                option.style.color = '';

                if (!id) return; // skip placeholder

                if (total >= 100) {
                    // Rule 3: Task sudah 100%, kunci permanen
                    option.disabled = true;
                    option.textContent = `[COMPLETED] ${option.textContent}`;
                    option.style.color = '#aaa';
                } else if (todayTaskIds.includes(id)) {
                    // Rule 1: Sudah ada entry hari ini, tidak bisa add baru
                    option.disabled = true;
                    option.textContent = `[ALREADY LOGGED] ${option.textContent}`;
                }
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


            // Rule 1: Tampilkan tombol Edit Progress hanya jika entry hari ini
            const isToday = button.getAttribute('data-is-today') === '1';
            const btnEditProgress = document.getElementById('btnEditProgress');
            if (btnEditProgress) {
                btnEditProgress.classList.toggle('d-none', !isToday);
            }
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
    // ── Sync slider & input number ──────────────────────
    const progressSlider = document.getElementById('progressSlider');
    const progressInput  = document.getElementById('progressInput');
    const progressLabel  = document.getElementById('progressDisplayLabel');

    if (progressSlider && progressInput) {
        // Slider → Input
        progressSlider.addEventListener('input', () => {
            progressInput.value    = progressSlider.value;
            progressLabel.textContent = `${progressSlider.value}%`;
        });

        // Input → Slider
        progressInput.addEventListener('input', () => {
            let val = parseInt(progressInput.value) || 0;
            val = Math.min(100, Math.max(0, val)); // clamp 0-100
            progressInput.value       = val;
            progressSlider.value      = val;
            progressLabel.textContent = `${val}%`;
        });
    }

    // ── Isi nilai saat modal Edit Progress dibuka ────────
    const editProgressModal = document.getElementById('modalEditProgress');
    if (editProgressModal) {
        editProgressModal.addEventListener('show.bs.modal', () => {
            // Ambil nilai dari modal Detail yang sudah terisi sebelumnya
            const currentProgress = parseInt(
                document.getElementById('detail-progress')?.textContent ?? '0'
            ) || 0;
            const lbkId = document.getElementById('lbk_id_edit').value;

            document.getElementById('lbk_id_progress').value = lbkId;
            progressSlider.value       = currentProgress;
            progressInput.value        = currentProgress;
            progressLabel.textContent  = `${currentProgress}%`;
        });
    }

    // ── Submit Edit Progress ─────────────────────────────
    const formEditProgress = document.getElementById('formEditProgress');
    if (formEditProgress) {
        formEditProgress.addEventListener('submit', (e) => {
            e.preventDefault();
            const lbkId    = document.getElementById('lbk_id_progress').value;
            const progress = parseInt(document.getElementById('progressInput').value) || 0;

            axios.put(`/api/logbook/${lbkId}`, { lbk_progress: progress })
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Progress updated successfully',
                        showConfirmButton: false,
                        timer: 1500,
                    }).then(() => location.reload());
                })
                .catch((err) => {
                    // Tampilkan pesan error dari backend (termasuk sisa progress)
                    Swal.fire(
                        'Failed',
                        err.response?.data?.message || 'An error occurred',
                        'error'
                    );
                });
        });
    }
});
