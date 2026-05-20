const openBtn = document.getElementById("openStudentLogin");
const modal = document.getElementById("studentLoginModal");
const closeBtn = document.getElementById("closeStudentLogin");

// Open modal
openBtn.addEventListener("click", () => {
    modal.style.display = "flex";
});

// Close modal
closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
});

// Close when clicking outside modal
window.addEventListener("click", (e) => {
    if (e.target === modal) {
        modal.style.display = "none";
    }
});

const openGuidanceBtn = document.getElementById("openGuidanceLogin");
const guidanceModal = document.getElementById("guidanceLoginModal");
const closeGuidanceBtn = document.getElementById("closeGuidanceLogin");

// Open Guidance modal
openGuidanceBtn.addEventListener("click", () => {
    guidanceModal.style.display = "flex";
});

// Close Guidance modal
closeGuidanceBtn.addEventListener("click", () => {
    guidanceModal.style.display = "none";
});

// Close when clicking outside
window.addEventListener("click", (e) => {
    if (e.target === guidanceModal) {
        guidanceModal.style.display = "none";
    }
});

const openAdminBtn = document.getElementById("openAdminLogin");
const adminModal = document.getElementById("adminLoginModal");
const closeAdminBtn = document.getElementById("closeAdminLogin");

// Open Admin modal
openAdminBtn.addEventListener("click", () => {
    adminModal.style.display = "flex";
});

// Close Admin modal
closeAdminBtn.addEventListener("click", () => {
    adminModal.style.display = "none";
});

// Close when clicking outside
window.addEventListener("click", (e) => {
    if (e.target === adminModal) {
        adminModal.style.display = "none";
    }
});

