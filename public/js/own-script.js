// Custom JavaScript for navigation scroll position preservation

document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.querySelector(".sidebar-scroll");

    if (sidebar) {
        // Restore scroll position on page load
        const savedScrollPosition = localStorage.getItem(
            "sidebarScrollPosition"
        );
        if (savedScrollPosition) {
            sidebar.scrollTop = parseInt(savedScrollPosition);
        }

        // Save scroll position when scrolling
        let scrollTimeout;
        sidebar.addEventListener("scroll", function () {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function () {
                localStorage.setItem(
                    "sidebarScrollPosition",
                    sidebar.scrollTop
                );
            }, 100); // Debounce to avoid too frequent saves
        });

        // Save scroll position before page unload
        window.addEventListener("beforeunload", function () {
            localStorage.setItem("sidebarScrollPosition", sidebar.scrollTop);
        });
    }
});
