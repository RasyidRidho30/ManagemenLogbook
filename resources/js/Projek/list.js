document.addEventListener("DOMContentLoaded", () => {
    // --- TOOLTIP ---
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]'),
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const timelineContent = document.getElementById("timelineContent");
    const scrollContainer = document.getElementById("timelineScroll");
    const rows = document.querySelectorAll(".timeline-row");

    // Fungsi Helper Parse
    const parseDate = (s) => (s ? new Date(s + "T00:00:00") : null);

    // --- 1. TENTUKAN RANGE TANGGAL (PROJEK VS TUGAS) ---
    const projectStartStr = timelineContent.dataset.pstart;
    const projectEndStr = timelineContent.dataset.pend;

    let minDate, maxDate;

    // Cek apakah Tanggal Projek tersedia
    if (projectStartStr && projectEndStr) {
        minDate = new Date(projectStartStr + "T00:00:00");
        maxDate = new Date(projectEndStr + "T00:00:00");

        // Opsional: Tambahkan 1-2 hari di akhir agar batas kanan tidak terlalu mepet
        maxDate.setDate(maxDate.getDate() + 2);
    } else {
        // Fallback: Jika tanggal projek kosong, hitung dari tugas
        const dates = Array.from(rows)
            .map((r) => ({
                start: parseDate(r.dataset.start),
                end: parseDate(r.dataset.end),
            }))
            .filter((d) => d.start && d.end);

        if (dates.length > 0) {
            minDate = new Date(Math.min(...dates.map((d) => d.start)));
            maxDate = new Date(Math.max(...dates.map((d) => d.end)));
            // Padding default
            minDate.setDate(minDate.getDate() - 5);
            maxDate.setDate(maxDate.getDate() + 15);
        } else {
            // Jika sama sekali tidak ada data, pakai bulan ini
            minDate = new Date();
            minDate.setDate(1);
            maxDate = new Date(minDate);
            maxDate.setMonth(maxDate.getMonth() + 1);
        }
    }

    const totalDays = Math.ceil((maxDate - minDate) / (24 * 3600 * 1000)) + 1;
    const dayWidth = 40;
    const contentWidth = totalDays * dayWidth;

    timelineContent.style.width = contentWidth + "px";

    // --- 2. RENDER HEADER SKALA ---
    const dateScale = document.getElementById("dateScale");
    const timelineBody = document.getElementById("timelineBody");
    const months = [
        "JAN",
        "FEB",
        "MAR",
        "APR",
        "MEI",
        "JUN",
        "JUL",
        "AGU",
        "SEP",
        "OKT",
        "NOV",
        "DES",
    ];

    let curr = new Date(minDate);
    let lastMonth = -1;

    for (let i = 0; i < totalDays; i++) {
        const leftPos = i * dayWidth;

        // Render Label Bulan
        if (curr.getMonth() !== lastMonth) {
            const mLabel = document.createElement("div");
            mLabel.className = "month-label";
            mLabel.style.left = leftPos + "px";
            mLabel.innerText = `${months[curr.getMonth()]} ${curr.getFullYear()}`;
            dateScale.appendChild(mLabel);
            lastMonth = curr.getMonth();
        }

        // Render Angka Tanggal
        const dayNum = document.createElement("div");
        dayNum.className = "day-number";
        dayNum.style.left = leftPos + dayWidth / 2 + "px";
        dayNum.innerText = curr.getDate();
        dateScale.appendChild(dayNum);

        // Render Garis Grid
        const gridLine = document.createElement("div");
        gridLine.className = "day-grid-line";
        gridLine.style.left = leftPos + "px";
        timelineBody.appendChild(gridLine);

        curr.setDate(curr.getDate() + 1);
    }

    // --- 3. RENDER BAR TUGAS ---
    rows.forEach((r) => {
        const start = parseDate(r.dataset.start);
        const end = parseDate(r.dataset.end);
        const progress = parseInt(r.dataset.progress || "0");
        const durBar = r.querySelector(".duration-bar");
        const progFill = r.querySelector(".progress-fill");
        const progText = r.querySelector(".progress-text");

        if (!start || !end) return;

        const startDiff = Math.floor((start - minDate) / (24 * 3600 * 1000));
        const duration = Math.ceil((end - start) / (24 * 3600 * 1000)) + 1;

        durBar.style.left = startDiff * dayWidth + "px";
        durBar.style.width = duration * dayWidth + "px";
        progFill.style.width = Math.min(progress, 100) + "%";

        if (progress > 30) {
            progText.classList.add("text-white-force");
        }
    });

    // --- 4. CLICK TO SCROLL ---
    const taskLinks = document.querySelectorAll(".js-task-click");
    taskLinks.forEach((link) => {
        link.addEventListener("click", function () {
            const targetId = this.getAttribute("data-target");
            const targetBar = document.getElementById(targetId);

            if (targetBar) {
                const barLeft = parseInt(targetBar.style.left) || 0;
                const barWidth = parseInt(targetBar.style.width) || 0;
                const containerWidth = scrollContainer.offsetWidth;
                const scrollTo = barLeft - containerWidth / 2 + barWidth / 2;

                scrollContainer.scrollTo({
                    left: scrollTo,
                    behavior: "smooth",
                });

                document
                    .querySelectorAll(".highlight-active")
                    .forEach((el) => el.classList.remove("highlight-active"));
                targetBar.classList.add("highlight-active");
                setTimeout(() => {
                    targetBar.classList.remove("highlight-active");
                }, 2000);
            }
        });
    });

    // --- 5. TODAY MARKER ---
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Normalisasi untuk perbandingan
    const minDateNorm = new Date(minDate);
    minDateNorm.setHours(0, 0, 0, 0);
    const maxDateNorm = new Date(maxDate);
    maxDateNorm.setHours(0, 0, 0, 0);

    if (today >= minDateNorm && today <= maxDateNorm) {
        const diffTime = Math.abs(today - minDateNorm);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        const leftPos = diffDays * dayWidth;

        const marker = document.getElementById("todayMarker");
        marker.style.left = leftPos + "px";
        marker.style.display = "block";

        setTimeout(() => {
            scrollContainer.scrollLeft =
                leftPos - scrollContainer.offsetWidth / 2;
        }, 100);
    }

    // --- 6. DRAG TO SCROLL (HAND TOOL) ---
    let isDown = false;
    let startX;
    let scrollLeft;

    scrollContainer.addEventListener("mousedown", (e) => {
        isDown = true;
        scrollContainer.style.cursor = "grabbing";
        startX = e.pageX - scrollContainer.offsetLeft;
        scrollLeft = scrollContainer.scrollLeft;
    });

    scrollContainer.addEventListener("mouseleave", () => {
        isDown = false;
        scrollContainer.style.cursor = "grab";
    });

    scrollContainer.addEventListener("mouseup", () => {
        isDown = false;
        scrollContainer.style.cursor = "grab";
    });

    scrollContainer.addEventListener("mousemove", (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - scrollContainer.offsetLeft;
        const walk = (x - startX) * 1.5;
        scrollContainer.scrollLeft = scrollLeft - walk;
    });
});
