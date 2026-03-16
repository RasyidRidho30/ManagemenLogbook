document.addEventListener("DOMContentLoaded", () => {
    const timelineContent = document.getElementById("timelineContent");
    const scrollContainer = document.getElementById("timelineScroll");
    const dateScale = document.getElementById("dateScale");
    const timelineBody = document.getElementById("timelineBody");
    const rows = document.querySelectorAll(".timeline-row");

    const DAY_WIDTH = 40;
    const MONTHS = [
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

    const parseDate = (s) => (s ? new Date(s + "T00:00:00") : null);

    const getTimelineRange = () => {
        const ps = timelineContent.dataset.pstart;
        const pe = timelineContent.dataset.pend;

        if (ps && pe) {
            const start = new Date(ps + "T00:00:00");
            const end = new Date(pe + "T00:00:00");
            end.setDate(end.getDate() + 2);
            return { minDate: start, maxDate: end };
        }

        const dates = Array.from(rows)
            .map((r) => ({
                start: parseDate(r.dataset.start),
                end: parseDate(r.dataset.end),
            }))
            .filter((d) => d.start && d.end);

        if (dates.length > 0) {
            const min = new Date(Math.min(...dates.map((d) => d.start)));
            const max = new Date(Math.max(...dates.map((d) => d.end)));
            min.setDate(min.getDate() - 5);
            max.setDate(max.getDate() + 15);
            return { minDate: min, maxDate: max };
        }

        const fallbackMin = new Date();
        fallbackMin.setDate(1);
        const fallbackMax = new Date(fallbackMin);
        fallbackMax.setMonth(fallbackMax.getMonth() + 1);
        return { minDate: fallbackMin, maxDate: fallbackMax };
    };

    const { minDate, maxDate } = getTimelineRange();
    const totalDays = Math.ceil((maxDate - minDate) / (24 * 3600 * 1000)) + 1;
    timelineContent.style.width = totalDays * DAY_WIDTH + "px";

    const renderCalendarGrid = () => {
        let current = new Date(minDate);
        let lastMonth = -1;

        for (let i = 0; i < totalDays; i++) {
            const leftPos = i * DAY_WIDTH;

            if (current.getMonth() !== lastMonth) {
                const label = document.createElement("div");
                label.className = "month-label";
                label.style.left = `${leftPos}px`;
                label.innerText = `${MONTHS[current.getMonth()]} ${current.getFullYear()}`;
                dateScale.appendChild(label);
                lastMonth = current.getMonth();
            }

            const dayNum = document.createElement("div");
            dayNum.className = "day-number";
            dayNum.style.left = `${leftPos + DAY_WIDTH / 2}px`;
            dayNum.innerText = current.getDate();
            dateScale.appendChild(dayNum);

            const gridLine = document.createElement("div");
            gridLine.className = "day-grid-line";
            gridLine.style.left = `${leftPos}px`;
            timelineBody.appendChild(gridLine);

            current.setDate(current.getDate() + 1);
        }
    };

    const renderTaskBars = () => {
        rows.forEach((row) => {
            const start = parseDate(row.dataset.start);
            const end = parseDate(row.dataset.end);
            const progress = parseInt(row.dataset.progress || "0");

            if (!start || !end) return;

            const startDiff = Math.floor(
                (start - minDate) / (24 * 3600 * 1000),
            );
            const duration = Math.ceil((end - start) / (24 * 3600 * 1000)) + 1;

            const bar = row.querySelector(".duration-bar");
            const fill = row.querySelector(".progress-fill");
            const text = row.querySelector(".progress-text");

            bar.style.left = `${startDiff * DAY_WIDTH}px`;
            bar.style.width = `${duration * DAY_WIDTH}px`;
            fill.style.width = `${Math.min(progress, 100)}%`;

            if (progress > 30) text.classList.add("text-white-force");
        });
    };

    const setupAutoScroll = () => {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const minNorm = new Date(minDate).setHours(0, 0, 0, 0);
        const maxNorm = new Date(maxDate).setHours(0, 0, 0, 0);

        if (today >= minNorm && today <= maxNorm) {
            const diffDays = Math.ceil(
                Math.abs(today - minNorm) / (1000 * 60 * 60 * 24),
            );
            const leftPos = diffDays * DAY_WIDTH;

            const marker = document.getElementById("todayMarker");
            marker.style.left = `${leftPos}px`;
            marker.style.display = "block";

            setTimeout(() => {
                scrollContainer.scrollLeft =
                    leftPos - scrollContainer.offsetWidth / 2;
            }, 100);
        }
    };

    const setupInteractions = () => {
        document.querySelectorAll(".js-task-click").forEach((link) => {
            link.addEventListener("click", () => {
                const targetBar = document.getElementById(
                    link.getAttribute("data-target"),
                );
                if (!targetBar) return;

                const barLeft = parseInt(targetBar.style.left) || 0;
                const barWidth = parseInt(targetBar.style.width) || 0;
                const scrollTo =
                    barLeft - scrollContainer.offsetWidth / 2 + barWidth / 2;

                scrollContainer.scrollTo({
                    left: scrollTo,
                    behavior: "smooth",
                });

                document
                    .querySelectorAll(".highlight-active")
                    .forEach((el) => el.classList.remove("highlight-active"));
                targetBar.classList.add("highlight-active");
                setTimeout(
                    () => targetBar.classList.remove("highlight-active"),
                    2000,
                );
            });
        });

        let isDown = false,
            startX,
            scrollLeft;

        scrollContainer.addEventListener("mousedown", (e) => {
            isDown = true;
            scrollContainer.style.cursor = "grabbing";
            startX = e.pageX - scrollContainer.offsetLeft;
            scrollLeft = scrollContainer.scrollLeft;
        });

        scrollContainer.addEventListener("mouseleave", () => {
            isDown = false;
        });
        scrollContainer.addEventListener("mouseup", () => {
            isDown = false;
        });

        scrollContainer.addEventListener("mousemove", (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - scrollContainer.offsetLeft;
            const walk = (x - startX) * 1.5;
            scrollContainer.scrollLeft = scrollLeft - walk;
        });
    };

    renderCalendarGrid();
    renderTaskBars();
    setupAutoScroll();
    setupInteractions();
});
