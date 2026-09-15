console.log("StudentHub Javascript Loaded Successfully");
console.log("Welcome to StudentHub");
console.log("Practical - 4 JavaScript");

// 1 Variable
let studentName = "Yash Paghadar";
let course = "Information Technology";
let semester = 3;
console.log(`Name: ${studentName} \nCourse: ${course} \nSemester: ${semester}`);

// 2 Data Type
let college = "CHARUSAT";
let year = 2026,
  isStudent = true;
console.log(`College: ${college} \nYear: ${year} \nStudent: ${isStudent}`);
//Function
function welcome() {
  return "\n!! Welcome to StudentHub !!";
}
console.log("Function Calling......" + welcome());

// Function with Parameter
function welcome_Student(name) {
  return name;
}
console.log(
  "Parameterized Function calling....." + welcome_Student("Yash Paghadar"),
);


document.addEventListener("DOMContentLoaded", function () {
  console.log("StudentHub buttons loaded");

  // ================================
  // CLOSE NOTIFICATION
  // ================================

  const closeButton = document.getElementById("closeBtn");
    const notification = document.getElementById("notification");

    if (closeButton && notification) {
        closeButton.addEventListener("click", function () {
            console.log("Close button clicked");
            notification.style.display = "none";
        });
    }

  // ================================
  // CHANGE HEADING
  // ================================

  const heading = document.getElementById("welcomeHeading");
  const changeBtn = document.getElementById("changeBtn");

  if (heading && changeBtn) {
    changeBtn.onclick = function () {
      heading.textContent =
        heading.textContent === "Welcome to StudentHub"
          ? "Welcome to StudentHub Portal!"
          : "Welcome to StudentHub";
    };
  }

  // ==========================================
  // DARK MODE
  // ==========================================

  const themeBtn = document.getElementById("themeBtn");

if (themeBtn) {

  // Load saved theme when page opens
  const savedTheme = localStorage.getItem("theme");

  if (savedTheme === "dark") {
    document.body.classList.add("dark-mode");
    themeBtn.textContent = "Light Mode";
  } else {
    document.body.classList.remove("dark-mode");
    themeBtn.textContent = "Dark Mode";
  }

  // Change theme when button is clicked
  themeBtn.addEventListener("click", function () {

    document.body.classList.toggle("dark-mode");

    if (document.body.classList.contains("dark-mode")) {
      themeBtn.textContent = "Light Mode";

      // Save dark mode
      localStorage.setItem("theme", "dark");

    } else {
      themeBtn.textContent = "Dark Mode";

      // Save light mode
      localStorage.setItem("theme", "light");
    }

  });
}
});

// ==========================================
// FAQ Accordion - Custom JavaScript
// ==========================================

const faqButtons = document.querySelectorAll(".faq-main .accordion-button");

faqButtons.forEach((button) => {
  button.addEventListener("click", function () {
    const targetId = this.getAttribute("aria-controls");
    const target = document.getElementById(targetId);

    // Check if this FAQ is already open
    const isOpen = this.getAttribute("aria-expanded") === "true";

    // Close all FAQ items
    faqButtons.forEach((otherButton) => {
      const otherTargetId = otherButton.getAttribute("aria-controls");
      const otherTarget = document.getElementById(otherTargetId);

      otherButton.setAttribute("aria-expanded", "false");
      otherButton.classList.add("collapsed");

      if (otherTarget) {
        otherTarget.classList.remove("show");
      }
    });

    // Open clicked FAQ if it was previously closed
    if (!isOpen) {
      this.setAttribute("aria-expanded", "true");
      this.classList.remove("collapsed");

      if (target) {
        target.classList.add("show");
      }
    }
  });
});
