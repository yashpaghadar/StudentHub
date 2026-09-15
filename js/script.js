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

  // ==========================================
  // PRACTICAL 6 - EVENTS
  // Fetch + Search + Filter + Sort + Pagination
  // ==========================================

  const eventList = document.getElementById("eventList");

  if (eventList) {
    const searchInput = document.getElementById("searchInput");
    const categoryFilter = document.getElementById("categoryFilter");
    const sortEvents = document.getElementById("sortEvents");
    const loadingMessage = document.getElementById("loadingMessage");
    const errorMessage = document.getElementById("errorMessage");
    const prevPage = document.getElementById("prevPage");
    const nextPage = document.getElementById("nextPage");
    const pageNumber = document.getElementById("pageNumber");

    let allEvents = [];
    let currentPage = 1;
    const recordsPerPage = 5;

    // ================================
    // FETCH EVENTS JSON
    // ================================

    function loadEvents() {
      if (loadingMessage) {
        loadingMessage.style.display = "block";
      }

      if (errorMessage) {
        errorMessage.style.display = "none";
      }

      fetch("../data/events.json")
        .then(function (response) {
          if (!response.ok) {
            throw new Error("Unable to load events data.");
          }
          return response.json();
        })

        .then(function (data) {
          allEvents = data;
          console.log("Events loaded:", allEvents);
          if (loadingMessage) {
            loadingMessage.style.display = "none";
          }
          renderEvents();
        })

        .catch(function (error) {
          console.error("Error:", error);
          if (loadingMessage) {
            loadingMessage.style.display = "none";
          }

          if (errorMessage) {
            errorMessage.textContent =
              "Failed to load events. Please try again.";
            errorMessage.style.display = "block";
          }
        });
    }

    // ================================
    // DISPLAY EVENTS
    // ================================

    function renderEvents() {
      let filteredEvents = [...allEvents];

      // ================================
      // SEARCH BY TITLE
      // ================================

      const searchText = searchInput
        ? searchInput.value.toLowerCase().trim()
        : "";

      if (searchText !== "") {
        filteredEvents = filteredEvents.filter(function (event) {
          return event.title.toLowerCase().includes(searchText);
        });
      }

      // ===============================
      // FILTER BY CATEGORY
      // ===============================

     const selectedCategory = categoryFilter ? categoryFilter.value.trim() : "All";

      if (
        selectedCategory !== "" &&
        selectedCategory.toLowerCase() !== "all"
      ) {
        filteredEvents = filteredEvents.filter(function (event) {
          return event.category === selectedCategory;
        });
      }

      // ================================
      // SORT EVENTS
      // ================================

      const sortValue = sortEvents ? sortEvents.value : "default";
      console.log("Selected sort:", sortValue);

      if (sortValue === "title-asc") {
        filteredEvents.sort(function (a, b) {
          return a.title.localeCompare(b.title);
        });

      } else if (sortValue === "title-desc") {
        filteredEvents.sort(function (a, b) {
          return b.title.localeCompare(a.title);
        });

      } else if (sortValue === "date-asc") {
        filteredEvents.sort(function (a, b) {
          return new Date(a.date).getTime() -
                new Date(b.date).getTime();
        });

      } else if (sortValue === "date-desc") {
        filteredEvents.sort(function (a, b) {
          return new Date(b.date).getTime() -
                new Date(a.date).getTime();
        });
      }

      // ================================
      // PAGINATION
      // ================================

      const totalPages = Math.ceil(filteredEvents.length / recordsPerPage);

      if (currentPage > totalPages && totalPages > 0) {
        currentPage = totalPages;
      }

      const startIndex = (currentPage - 1) * recordsPerPage;
      const endIndex = startIndex + recordsPerPage;
      const eventsToDisplay = filteredEvents.slice(startIndex, endIndex);

      // ================================
      // CLEAR OLD DATA
      // ================================

      eventList.innerHTML = "";

      // ================================
      // NO RESULTS
      // ================================

      if (eventsToDisplay.length === 0) {
        eventList.innerHTML = `
          <div class="col-12">
            <div class="alert alert-info text-center">
              No events found.
            </div>
          </div>
        `;

        if (pageNumber) {
          pageNumber.textContent = "0";
        }

        if (prevPage) {
          prevPage.disabled = true;
        }

        if (nextPage) {
          nextPage.disabled = true;
        }

        return;
      }

      // ================================
      // CREATE EVENT CARDS
      // ================================

      eventsToDisplay.forEach(function (event) {
        const col = document.createElement("div");
        col.className = "col-md-6 col-lg-4 mb-4";

        const card = document.createElement("div");
        card.className = "card h-100 shadow-sm";

        const cardBody = document.createElement("div");
        cardBody.className = "card-body";

        const title = document.createElement("h5");
        title.className = "card-title";
        title.textContent = event.title;

        const date = document.createElement("p");
        date.className = "card-text";
        date.innerHTML = `<strong>Date:</strong> ${event.date}`;

        const location = document.createElement("p");
        location.className = "card-text";
        location.innerHTML = `<strong>Location:</strong> ${event.location}`;

        const category = document.createElement("span");
        category.className = "badge bg-primary";
        category.textContent = event.category;

        cardBody.appendChild(title);
        cardBody.appendChild(date);
        cardBody.appendChild(location);
        cardBody.appendChild(category);

        card.appendChild(cardBody);
        col.appendChild(card);

        eventList.appendChild(col);
      });

      // ================================
      // UPDATE PAGINATION
      // ================================

      if (pageNumber) {
        pageNumber.textContent = totalPages > 0 ? currentPage : 0;
      }

      if (prevPage) {
        prevPage.disabled = currentPage === 1;
      }

      if (nextPage) {
        nextPage.disabled = currentPage === totalPages;
      }
    }

    // ================================
    // SEARCH EVENT
    // ================================

    if (searchInput) {
      searchInput.addEventListener("input", function () {
        currentPage = 1;
        renderEvents();
      });
    }

    // ================================
    // CATEGORY FILTER
    // ================================

    if (categoryFilter) {
      categoryFilter.addEventListener("change", function () {
        currentPage = 1;
        renderEvents();
      });
    }

    // ================================
    // SORT EVENTS
    // ================================

    if (sortEvents) {
      sortEvents.addEventListener("change", function () {
        console.log("Sorting changed:", this.value);
        currentPage = 1;
        renderEvents();
      });
    }

    // ================================
    // PREVIOUS PAGE
    // ================================

    if (prevPage) {
      prevPage.addEventListener("click", function () {
        if (currentPage > 1) {
          currentPage--;

          renderEvents();
        }
      });
    }

    // ================================
    // NEXT PAGE
    // ================================

    if (nextPage) {
      nextPage.addEventListener("click", function () {
        currentPage++;

        renderEvents();
      });
    }

    // Start loading events
    loadEvents();
  }

  // ==========================================
  // PRACTICAL 6 - FAQ
  // Fetch + Search + Sort + Pagination
  // ==========================================

  const faqAccordion = document.getElementById("faqAccordion");

  if (faqAccordion) {
    const faqSearchInput = document.getElementById("faqSearchInput");
    const faqSort = document.getElementById("faqSort");
    const faqLoadingMessage = document.getElementById("faqLoadingMessage");
    const faqErrorMessage = document.getElementById("faqErrorMessage");
    const faqPrevPage = document.getElementById("faqPrevPage");
    const faqNextPage = document.getElementById("faqNextPage");
    const faqPageNumber = document.getElementById("faqPageNumber");

    let allFAQs = [];
    let currentFAQPage = 1;

    const faqsPerPage = 5;

    // ================================
    // FETCH FAQ JSON
    // ================================

    function loadFAQs() {
      if (faqLoadingMessage) {
        faqLoadingMessage.style.display = "block";
      }

      if (faqErrorMessage) {
        faqErrorMessage.style.display = "none";
      }

      fetch("../data/faqs.json")
        .then(function (response) {
          if (!response.ok) {
            throw new Error("Unable to load FAQ data.");
          }

          return response.json();
        })

        .then(function (data) {
          allFAQs = data;

          console.log("FAQs loaded:", allFAQs);
          if (faqLoadingMessage) {
            faqLoadingMessage.style.display = "none";
          }

          renderFAQs();
        })

        .catch(function (error) {
          console.error("FAQ Error:", error);
          if (faqLoadingMessage) {
            faqLoadingMessage.style.display = "none";
          }

          if (faqErrorMessage) {
            faqErrorMessage.textContent =
              "Failed to load FAQs. Please try again.";
            faqErrorMessage.style.display = "block";
          }
        });
    }

    // ================================
    // DISPLAY FAQs
    // ================================

    function renderFAQs() {
      let filteredFAQs = [...allFAQs];

      // ================================
      // SEARCH FAQ
      // ================================

      const searchText = faqSearchInput
        ? faqSearchInput.value.toLowerCase().trim()
        : "";

      if (searchText !== "") {
        filteredFAQs = filteredFAQs.filter(function (faq) {
          return faq.question.toLowerCase().includes(searchText);
        });
      }

      // ================================
      // SORT FAQ
      // ================================

      const sortValue = faqSort ? faqSort.value : "default";

      if (sortValue === "az") {
        filteredFAQs.sort(function (a, b) {
          return a.question.localeCompare(b.question);
        });
      } else if (sortValue === "za") {
        filteredFAQs.sort(function (a, b) {
          return b.question.localeCompare(a.question);
        });
      }

      // ================================
      // PAGINATION
      // ================================

      const totalPages = Math.ceil(filteredFAQs.length / faqsPerPage);

      if (currentFAQPage > totalPages && totalPages > 0) {
        currentFAQPage = totalPages;
      }

      const startIndex = (currentFAQPage - 1) * faqsPerPage;

      const endIndex = startIndex + faqsPerPage;

      const faqsToDisplay = filteredFAQs.slice(startIndex, endIndex);

      // ================================
      // CLEAR OLD FAQs
      // ================================

      faqAccordion.innerHTML = "";

      // ================================
      // NO RESULTS
      // ================================

      if (faqsToDisplay.length === 0) {
        faqAccordion.innerHTML = `
          <div class="alert alert-info text-center">
            No FAQs found.
          </div>
        `;

        if (faqPageNumber) {
          faqPageNumber.textContent = "0";
        }

        if (faqPrevPage) {
          faqPrevPage.disabled = true;
        }

        if (faqNextPage) {
          faqNextPage.disabled = true;
        }

        return;
      }

      // ================================
      // CREATE FAQ ACCORDION
      // ================================

      faqsToDisplay.forEach(function (faq, index) {
        const item = document.createElement("div");
        item.className = "accordion-item";

        const header = document.createElement("h2");
        header.className = "accordion-header";

        const button = document.createElement("button");
        button.className = "accordion-button collapsed";
        button.type = "button";

        button.setAttribute("data-bs-toggle", "collapse");

        const collapseId = "faqCollapse" + faq.id;

        button.setAttribute("data-bs-target", "#" + collapseId);

        button.setAttribute("aria-expanded", "false");

        button.setAttribute("aria-controls", collapseId);

        button.textContent = faq.question;

        const collapse = document.createElement("div");

        collapse.id = collapseId;

        collapse.className = "accordion-collapse collapse";

        collapse.setAttribute("data-bs-parent", "#faqAccordion");

        const body = document.createElement("div");

        body.className = "accordion-body";

        body.textContent = faq.answer;

        collapse.appendChild(body);
        header.appendChild(button);

        item.appendChild(header);
        item.appendChild(collapse);

        faqAccordion.appendChild(item);
      });

      // ================================
      // UPDATE FAQ PAGINATION
      // ================================

      if (faqPageNumber) {
        faqPageNumber.textContent = totalPages > 0 ? currentFAQPage : 0;
      }

      if (faqPrevPage) {
        faqPrevPage.disabled = currentFAQPage === 1;
      }

      if (faqNextPage) {
        faqNextPage.disabled = currentFAQPage === totalPages;
      }
    }

    // ================================
    // FAQ SEARCH
    // ================================

    if (faqSearchInput) {
      faqSearchInput.addEventListener("input", function () {
        currentFAQPage = 1;
        renderFAQs();
      });
    }

    // ================================
    // FAQ SORT
    // ================================

    if (faqSort) {
      faqSort.addEventListener("change", function () {
        currentFAQPage = 1;
        renderFAQs();
      });
    }

    // ================================
    // FAQ PREVIOUS PAGE
    // ================================

    if (faqPrevPage) {
      faqPrevPage.addEventListener("click", function () {
        if (currentFAQPage > 1) {
          currentFAQPage--;

          renderFAQs();
        }
      });
    }

    // ================================
    // FAQ NEXT PAGE
    // ================================

    if (faqNextPage) {
      faqNextPage.addEventListener("click", function () {
        currentFAQPage++;

        renderFAQs();
      });
    }

    // Start loading FAQs
    loadFAQs();
  }

  // ==========================================
  // PRACTICAL 6 - STUDENTS
  // Fetch + Search + Filter + Sort + Pagination
  // ==========================================

  const studentList = document.getElementById("studentList");
  if (studentList) {
    const studentSearchInput = document.getElementById("studentSearchInput");
    const courseFilter = document.getElementById("courseFilter");
    const yearFilter = document.getElementById("yearFilter");
    const sortStudents = document.getElementById("sortStudents");
    const studentLoadingMessage = document.getElementById("studentLoadingMessage");
    const studentErrorMessage = document.getElementById("studentErrorMessage");
    const studentPrevPage = document.getElementById("studentPrevPage");
    const studentNextPage = document.getElementById("studentNextPage");
    const studentPageNumber = document.getElementById("studentPageNumber");

    let allStudents = [];
    let currentStudentPage = 1;
    const studentsPerPage = 5;

    // ================================
    // FETCH STUDENTS JSON
    // ================================

    function loadStudents() {
      if (studentLoadingMessage) {
        studentLoadingMessage.style.display = "block";
      }

      if (studentErrorMessage) {
        studentErrorMessage.style.display = "none";
      }

      fetch("../data/students.json")
        .then(function (response) {
          if (!response.ok) {
            throw new Error("Unable to load students data.");
          }

          return response.json();
        })

        .then(function (data) {
          allStudents = data;
          console.log("Students loaded:", allStudents);

          if (studentLoadingMessage) {
            studentLoadingMessage.style.display = "none";
          }

          renderStudents();
        })

        .catch(function (error) {
          console.error("Student Error:", error);
          if (studentLoadingMessage) {
            studentLoadingMessage.style.display = "none";
          }

          if (studentErrorMessage) {
            studentErrorMessage.textContent =
              "Failed to load students. Please try again.";

            studentErrorMessage.style.display = "block";
          }
        });
    }

    // ================================
    // RENDER STUDENTS
    // ================================

    function renderStudents() {
      let filteredStudents = [...allStudents];

      // ================================
      // SEARCH BY NAME
      // ================================

      const searchText = studentSearchInput
        ? studentSearchInput.value.toLowerCase().trim()
        : "";

      if (searchText !== "") {
        filteredStudents = filteredStudents.filter(function (student) {
          return student.name.toLowerCase().includes(searchText);
        });
      }

      // ================================
      // FILTER BY COURSE
      // ================================

      const selectedCourse = courseFilter ? courseFilter.value : "All";
      if (selectedCourse !== "All" && selectedCourse !== "") {
        filteredStudents = filteredStudents.filter(function (student) {
          return student.course === selectedCourse;
        });
      }

      // ================================
      // FILTER BY YEAR
      // ================================

      const selectedYear = yearFilter ? yearFilter.value : "All";
      if (selectedYear !== "All" && selectedYear !== "") {
        filteredStudents = filteredStudents.filter(function (student) {
          return student.year == selectedYear;
        });
      }

      // ================================
      // SORT STUDENTS
      // ================================

      const sortValue = sortStudents ? sortStudents.value : "default";

      if (sortValue === "name-asc") {
        filteredStudents.sort(function (a, b) {
          return a.name.localeCompare(b.name);
        });
      } else if (sortValue === "name-desc") {
        filteredStudents.sort(function (a, b) {
          return b.name.localeCompare(a.name);
        });
      } else if (sortValue === "year-asc") {
        filteredStudents.sort(function (a, b) {
          return a.year - b.year;
        });
      } else if (sortValue === "year-desc") {
        filteredStudents.sort(function (a, b) {
          return b.year - a.year;
        });
      }

      // ================================
      // PAGINATION
      // ================================

      const totalPages = Math.ceil(filteredStudents.length / studentsPerPage);
      if (currentStudentPage > totalPages && totalPages > 0) {
        currentStudentPage = totalPages;
      }

      const startIndex = (currentStudentPage - 1) * studentsPerPage;
      const endIndex = startIndex + studentsPerPage;
      const studentsToDisplay = filteredStudents.slice(startIndex, endIndex);

      // ================================
      // CLEAR OLD STUDENTS
      // ================================

      studentList.innerHTML = "";

      // ================================
      // NO RESULTS
      // ================================

      if (studentsToDisplay.length === 0) {
        studentList.innerHTML = `
          <div class="col-12">
            <div class="alert alert-info text-center">
              No students found.
            </div>
          </div>
        `;

        if (studentPageNumber) {
          studentPageNumber.textContent = "0";
        }

        if (studentPrevPage) {
          studentPrevPage.disabled = true;
        }

        if (studentNextPage) {
          studentNextPage.disabled = true;
        }

        return;
      }

      // ================================
      // CREATE STUDENT CARDS
      // ================================

      studentsToDisplay.forEach(function (student) {
        const col = document.createElement("div");
        col.className = "col-md-6 col-lg-4";

        const card = document.createElement("div");
        card.className = "card h-100 shadow-sm";

        const cardBody = document.createElement("div");
        cardBody.className = "card-body";

        // Student Icon
        const icon = document.createElement("div");
        icon.className = "text-center mb-3";
        icon.innerHTML = `<i class="bi bi-person-circle fs-1"></i>`;

        // Student Name
        const name = document.createElement("h5");
        name.className = "card-title text-center fw-bold";
        name.textContent = student.name;

        // Email
        const email = document.createElement("p");
        email.className = "card-text";
        email.innerHTML = `<strong>Email:</strong> ${student.email}`;

        // Course
        const course = document.createElement("p");
        course.className = "card-text";
        course.innerHTML = `<strong>Course:</strong> ${student.course}`;

        // Year
        const year = document.createElement("p");
        year.className = "card-text";
        year.innerHTML = `<strong>Year:</strong> ${student.year}`;

        // Add elements
        cardBody.appendChild(icon);
        cardBody.appendChild(name);
        cardBody.appendChild(email);
        cardBody.appendChild(course);
        cardBody.appendChild(year);
        card.appendChild(cardBody);
        col.appendChild(card);
        studentList.appendChild(col);
      });

      // ================================
      // UPDATE PAGINATION
      // ================================

      if (studentPageNumber) {
        studentPageNumber.textContent = totalPages > 0 ? currentStudentPage : 0;
      }

      if (studentPrevPage) {
        studentPrevPage.disabled = currentStudentPage === 1;
      }

      if (studentNextPage) {
        studentNextPage.disabled = currentStudentPage === totalPages;
      }
    }

    // ================================
    // SEARCH EVENT
    // ================================

    if (studentSearchInput) {
      studentSearchInput.addEventListener("input", function () {
        currentStudentPage = 1;
        renderStudents();
      });
    }

    // ================================
    // COURSE FILTER
    // ================================

    if (courseFilter) {
      courseFilter.addEventListener("change", function () {
        currentStudentPage = 1;
        renderStudents();
      });
    }

    // ================================
    // YEAR FILTER
    // ================================

    if (yearFilter) {
      yearFilter.addEventListener("change", function () {
        currentStudentPage = 1;
        renderStudents();
      });
    }

    // ================================
    // SORT STUDENTS
    // ================================

    if (sortStudents) {
      sortStudents.addEventListener("change", function () {
        currentStudentPage = 1;
        renderStudents();
      });
    }

    // ================================
    // PREVIOUS PAGE
    // ================================

    if (studentPrevPage) {
      studentPrevPage.addEventListener("click", function () {
        if (currentStudentPage > 1) {
          currentStudentPage--;
          renderStudents();
        }
      });
    }

    // ================================
    // NEXT PAGE
    // ================================

    if (studentNextPage) {
      studentNextPage.addEventListener("click", function () {
        currentStudentPage++;
        renderStudents();
      });
    }

    // ================================
    // START FETCHING STUDENTS
    // ================================

    loadStudents();
  }
});
