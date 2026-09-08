/**
 * ==================================================================================
 * BOOKBUDDY MAIN SCRIPT
 * NAVIGATION: Handles the mobile hamburger menu open, close, and backdrop toggle.
 * ==================================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    const hamburgerBtn = document.querySelector(".hamburger");
    const navList = document.querySelector(".nav-list");
    const closeBtn = document.querySelector(".close");

    if (hamburgerBtn && navList) {
        hamburgerBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            navList.classList.add("active");
        });
    }

    if (closeBtn && navList) {
        closeBtn.addEventListener("click", () => {
            navList.classList.remove("active");
        });
    }

    document.addEventListener("click", (e) => {
        if (navList && navList.classList.contains("active")) {
            if (!navList.contains(e.target) && (!hamburgerBtn || !hamburgerBtn.contains(e.target))) {
                navList.classList.remove("active");
            }
        }
    });
});