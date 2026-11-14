document.addEventListener("DOMContentLoaded", () => {

    const mainToggles = document.querySelectorAll(".toggle-menu-sidebar");
    const subToggles = document.querySelectorAll(".toggle-sub-menu-sidebar");

    function closeAllMain(except = null) {
        document.querySelectorAll(".list-item").forEach(item => {
            if (item !== except) {
                item.classList.remove("show");
                const dropdown = item.querySelector(".content-dropdown");
                if (dropdown) dropdown.style.maxHeight = "0";
            }
        });
    }

    function closeAllSub(except = null) {
        document.querySelectorAll(".list-content-dropdown").forEach(el => {
            if (el !== except) {
                el.classList.remove("show");
                el.style.maxHeight = "0";
            }
        });
    }

    // ===== MAIN DROPDOWN =====
    mainToggles.forEach(toggle => {
        toggle.addEventListener("click", e => {
            e.preventDefault();

            const item = toggle.closest(".list-item");
            const dropdown = item.querySelector(".content-dropdown");
            const isOpen = item.classList.contains("show");

            closeAllMain(item);

            if (!isOpen) {
                item.classList.add("show");
                dropdown.style.maxHeight = "none";
            } else {
                item.classList.remove("show");
                dropdown.style.maxHeight = "0";
            }
        });
    });

    // ===== SUB DROPDOWN =====
    subToggles.forEach(toggle => {
        toggle.addEventListener("click", e => {
            e.preventDefault();
            e.stopPropagation();

            const sub = toggle.nextElementSibling;
            if (!sub) return;

            const isOpen = sub.classList.contains("show");
            closeAllSub(sub);

            if (!isOpen) {
                sub.classList.add("show");
                sub.style.maxHeight = sub.scrollHeight + "px";
                toggle.classList.add("show");
            } else {
                sub.classList.remove("show");
                sub.style.maxHeight = "0";
                toggle.classList.remove("show");
            }
        });
    });

    // ===== AUTO ACTIVE URL =====
    const currentUrl = location.href.replace(/\/$/, "");

    document.querySelectorAll(".link-href").forEach(link => {
        const linkUrl = link.href.replace(/\/$/, "");

        if (currentUrl === linkUrl || currentUrl.startsWith(linkUrl + "/")) {

            link.classList.add("active");

            const subDropdown = link.closest(".list-content-dropdown");
            const subToggle = subDropdown?.previousElementSibling;
            const mainDropdown = link.closest(".content-dropdown");
            const listItem = link.closest(".list-item");

            // buka parent
            listItem?.classList.add("show", "active");
            if (mainDropdown) {
                mainDropdown.style.maxHeight = "none";
            }

            // buka sub
            if (subDropdown) {
                subDropdown.classList.add("show");
                subDropdown.style.maxHeight = subDropdown.scrollHeight + "px";
            }

            subToggle?.classList.add("show", "active");
        }
    });

});
