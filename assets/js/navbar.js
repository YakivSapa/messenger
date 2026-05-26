function initNavbarDropdowns() {
    document.querySelectorAll(".dropdown").forEach(el => {
        el.classList.remove("open");
    });

    document.querySelectorAll(".dropdown-toggle").forEach(toggle => {
        if (toggle.dataset.bound) return;
        toggle.dataset.bound = "true";

        toggle.addEventListener("click", (e) => {
            e.preventDefault();

            const dropdown = toggle.closest(".dropdown");

            document.querySelectorAll(".dropdown.open").forEach(el => {
                if (el !== dropdown) el.classList.remove("open");
            });

            dropdown.classList.toggle("open");
        });
    });
    document.addEventListener("click", (e) => {
    const isInside = e.target.closest(".dropdown");
    const isToggle = e.target.closest(".dropdown-toggle");

    if (!isInside && !isToggle) {
        document.querySelectorAll(".dropdown.open").forEach(el => {
            el.classList.remove("open");
        });
    }
});
}

document.addEventListener("turbo:load", initNavbarDropdowns);
document.addEventListener("DOMContentLoaded", initNavbarDropdowns);