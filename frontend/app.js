// Toggle mobile menu
document.querySelector(".menu-toggle").addEventListener("click", () => {
    document.querySelector("nav ul").classList.toggle("active");
});

// Review slider
let reviews = document.querySelectorAll(".review");
let current = 0;
setInterval(() => {
    reviews[current].classList.remove("active");
    current = (current + 1) % reviews.length;
    reviews[current].classList.add("active");
}, 3000);

// FAQ toggle
document.querySelectorAll(".faq-question").forEach(q => {
    q.addEventListener("click", () => {
        let answer = q.nextElementSibling;
        answer.style.display = answer.style.display === "block" ? "none" : "block";
    });
});

// Back to top button
let backToTop = document.getElementById("backToTop");
window.addEventListener("scroll", () => {
    backToTop.style.display = window.scrollY > 200 ? "block" : "none";
});
backToTop.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
});

// Search functionality
const search = document.getElementById("search");
if (search) {
    search.addEventListener("keypress", function (e) {
        if (e.key === "Enter") {
            goToSearch();
        }
    });
}
