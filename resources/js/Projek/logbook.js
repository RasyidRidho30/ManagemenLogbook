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
        const today = new Date().toISOString().split("T")[0];
        const todayTaskIds = existingLogbooks
            .filter(
                (log) =>
                    log.lbk_tanggal?.split("T")[0] === today ||
                    log.lbk_tanggal === today,
            )
            .map((log) => parseInt(log.tgs_id));
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
    // ── Elemen ──────────────────────────────────────────
    const formAdd = document.getElementById("formAddLogbook");
    const modalElement = document.getElementById("modalAddLogbook");
    const detailModal = document.getElementById("modalDetailLogbook");
    const formEditComment = document.getElementById("formEditComment");
    const formEditProgress = document.getElementById("formEditProgress");
    const editProgressModal = document.getElementById("modalEditProgress");
    const progressSlider = document.getElementById("progressSlider");
    const progressInput = document.getElementById("progressInput");
    const progressLabel = document.getElementById("progressDisplayLabel");
    const editEvidenceGroup = document.getElementById("editEvidenceGroup");
    const editEvidenceLink = document.getElementById("editEvidenceLink");

    // ── State untuk Edit Progress ────────────────────────
    let progressOutsideEntry = 0;
    let maxProgressAllowed = 100;
    let progressWaitingEvidence = false;

    // ── Helper: cek evidence untuk modal Add ────────────
    const checkShowEvidence = async (tgsId, inputProgress) => {
        if (!tgsId) return;
        try {
            const res = await axios.get(`/api/logbook/task-progress/${tgsId}`);
            const { total_progress, today_entry } = res.data;
            const progressDiluar = today_entry
                ? total_progress - today_entry.lbk_progress
                : total_progress;
            const totalNanti = progressDiluar + parseInt(inputProgress || 0);
            document
                .getElementById("evidenceLinkGroup")
                ?.classList.toggle("d-none", totalNanti < 100);
        } catch (err) {
            console.error(err);
        }
    };

    // ── Add Modal: listener task & progress ─────────────
    document.getElementById("tgs_id")?.addEventListener("change", (e) => {
        checkShowEvidence(
            e.target.value,
            document.getElementById("lbk_progress").value,
        );
    });
    document.getElementById("lbk_progress")?.addEventListener("input", (e) => {
        checkShowEvidence(
            document.getElementById("tgs_id").value,
            e.target.value,
        );
    });

    // ── Form Add Submit ──────────────────────────────────
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

            axios
                .post("/api/logbook", {
                    tgs_id: tgsId,
                    tanggal: document.getElementById("lbk_tanggal").value,
                    deskripsi: document.getElementById("lbk_deskripsi").value,
                    komentar:
                        document.getElementById("lbk_komentar").value || "",
                    progress: progressValue || 0,
                    evidence_link:
                        document.getElementById("evidence_link")?.value || "",
                })
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

    // ── Add Modal: load task status ──────────────────────
    if (modalElement) {
        modalElement.addEventListener("show.bs.modal", async () => {
            formAdd?.reset();
            const projectId = window.location.pathname.split("/")[2];
            const res = await axios.get(`/api/projek/${projectId}/logbook`);
            const existingLogbooks = res.data.data || [];
            const today = new Date().toISOString().split("T")[0];

            const progressMap = {};
            existingLogbooks.forEach((log) => {
                const id = parseInt(log.tgs_id);
                progressMap[id] =
                    (progressMap[id] || 0) + parseInt(log.lbk_progress || 0);
            });

            const todayTaskIds = existingLogbooks
                .filter(
                    (log) =>
                        (log.lbk_tanggal?.split("T")[0] ?? log.lbk_tanggal) ===
                        today,
                )
                .map((log) => parseInt(log.tgs_id));

            usedTaskIds = todayTaskIds;

            document
                .getElementById("tgs_id")
                ?.querySelectorAll("option")
                .forEach((option) => {
                    const id = parseInt(option.value);
                    const total = progressMap[id] || 0;

                    option.textContent = option.textContent
                        .replace(/\[ALREADY LOGGED\] /g, "")
                        .replace(/\[COMPLETED\] /g, "");
                    option.disabled = false;
                    option.style.color = "";

                    if (!id) return;

                    if (total >= 100) {
                        option.disabled = true;
                        option.textContent = `[COMPLETED] ${option.textContent}`;
                        option.style.color = "#aaa";
                    } else if (todayTaskIds.includes(id)) {
                        option.disabled = true;
                        option.textContent = `[ALREADY LOGGED] ${option.textContent}`;
                    }
                });
        });
    }

    // ── Detail Modal ─────────────────────────────────────
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
            komentarEl.classList.toggle("text-muted", !komentar.trim());

            const evidenceLink =
                button.getAttribute("data-evidence-link") || "";
            const evidenceEl = document.getElementById("detail-evidence");
            if (evidenceLink) {
                evidenceEl.innerHTML = `<a href="${evidenceLink}" target="_blank" rel="noreferrer">${evidenceLink}</a>`;
                evidenceEl.classList.remove("text-muted");
            } else {
                evidenceEl.innerHTML = `<em class="text-muted">No evidence link provided.</em>`;
            }

            document.getElementById("lbk_id_edit").value =
                button.getAttribute("data-lbk-id");
            document.getElementById("komentarEdit").value = komentar;

            const isToday = button.getAttribute("data-is-today") === "1";
            const btnEditProgress = document.getElementById("btnEditProgress");
            if (btnEditProgress) {
                btnEditProgress.classList.toggle("d-none", !isToday);
                btnEditProgress.dataset.evidenceLink = evidenceLink;
            }
        });
    }

    // ── Edit Comment Submit ───────────────────────────────
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

    // ── Slider sync — TANPA API call ────────────────────
    if (progressSlider && progressInput) {
        progressSlider.addEventListener("input", () => {
            progressInput.value = progressSlider.value;
            progressLabel.textContent = `${progressSlider.value}%`;
            const total = progressOutsideEntry + parseInt(progressSlider.value);
            editEvidenceGroup?.classList.toggle("d-none", total < 100);
        });

        progressInput.addEventListener("input", () => {
            let val = Math.min(
                maxProgressAllowed,
                Math.max(0, parseInt(progressInput.value) || 0),
            );
            progressInput.value = val;
            progressSlider.value = val;
            progressLabel.textContent = `${val}%`;
            const total = progressOutsideEntry + val;
            editEvidenceGroup?.classList.toggle("d-none", total < 100);
        });
    }

    // ── Edit Progress Modal: buka — fetch 1x ────────────
    if (editProgressModal) {
        editProgressModal.addEventListener("show.bs.modal", async () => {
            const currentProgress =
                parseInt(
                    document.getElementById("detail-progress")?.textContent ??
                        "0",
                ) || 0;
            const lbkId = document.getElementById("lbk_id_edit").value;

            document.getElementById("lbk_id_progress").value = lbkId;
            progressSlider.value = currentProgress;
            progressInput.value = currentProgress;
            progressLabel.textContent = `${currentProgress}%`;
            editEvidenceGroup?.classList.add("d-none");
            progressWaitingEvidence = false;

            // Reset tombol
            const saveBtn = formEditProgress?.querySelector(
                "button[type='submit']",
            );
            if (saveBtn)
                saveBtn.innerHTML =
                    '<i class="bi bi-check2-circle me-2"></i>Save Progress';

            try {
                const res = await axios.get(
                    `/api/logbook/task-progress-by-lbk/${lbkId}`,
                );
                const { total_progress, current_entry_progress } = res.data;
                progressOutsideEntry = total_progress - current_entry_progress;
                maxProgressAllowed = 100 - progressOutsideEntry;

                progressSlider.max = maxProgressAllowed;
                progressInput.max = maxProgressAllowed;

                // Jika sudah complete dari sebelumnya, langsung tampilkan evidence
                if (progressOutsideEntry + currentProgress >= 100) {
                    editEvidenceGroup?.classList.remove("d-none");
                }
            } catch (err) {
                console.error(err);
                progressOutsideEntry = 0;
                maxProgressAllowed = 100;
            }
        });

        // ── Edit Progress Modal: tutup — reset ───────────
        editProgressModal.addEventListener("hidden.bs.modal", () => {
            progressWaitingEvidence = false;
            progressOutsideEntry = 0;
            maxProgressAllowed = 100;
            progressSlider.max = 100;
            progressInput.max = 100;
            editEvidenceGroup?.classList.add("d-none");
            if (editEvidenceLink) editEvidenceLink.value = "";
            const saveBtn = formEditProgress?.querySelector(
                "button[type='submit']",
            );
            if (saveBtn)
                saveBtn.innerHTML =
                    '<i class="bi bi-check2-circle me-2"></i>Save Progress';
        });
    }

    // ── Edit Progress Submit ─────────────────────────────
    if (formEditProgress) {
        formEditProgress.addEventListener("submit", async (e) => {
            e.preventDefault();

            const lbkId = document.getElementById("lbk_id_progress").value;
            const progress = parseInt(progressInput.value) || 0;
            const saveBtn = formEditProgress.querySelector(
                "button[type='submit']",
            );
            const total = progressOutsideEntry + progress;

            // STEP 1: Jika total complete & belum tampil evidence → tampilkan dulu
            if (total >= 100 && !progressWaitingEvidence) {
                editEvidenceGroup?.classList.remove("d-none");
                if (saveBtn)
                    saveBtn.innerHTML =
                        '<i class="bi bi-check2-circle me-2"></i>Save with Evidence';
                progressWaitingEvidence = true;
                return; // stop, tunggu klik berikutnya
            }

            // STEP 2: Save
            axios
                .put(`/api/logbook/${lbkId}`, {
                    lbk_progress: progress,
                    evidence_link: editEvidenceLink?.value || "",
                })
                .then(() => {
                    progressWaitingEvidence = false;
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Progress updated successfully",
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
