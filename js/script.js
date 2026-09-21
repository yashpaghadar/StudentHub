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

document.addEventListener("DOMContentLoaded", () => {

  // ================================
  // CLOSE NOTIFICATION
  // ================================
  const closeBtn = document.getElementById("closeBtn");
  const notification = document.getElementById("notification");

  if (closeBtn && notification) {
    closeBtn.onclick = () => notification.style.display = "none";
  }


  // ================================
  // CHANGE HEADING
  // ================================
  const heading = document.getElementById("welcomeHeading");
  const changeBtn = document.getElementById("changeBtn");

  if (heading && changeBtn) {
    changeBtn.onclick = () => {
      heading.textContent =
        heading.textContent === "Welcome to StudentHub"
          ? "Welcome to StudentHub Portal!"
          : "Welcome to StudentHub";
    };
  }


  // ================================
  // DARK MODE
  // ================================
  const themeBtn = document.getElementById("themeBtn");

  if (themeBtn) {
    const setTheme = (dark) => {
      document.body.classList.toggle("dark-mode", dark);
      themeBtn.textContent = dark ? "Light Mode" : "Dark Mode";
      localStorage.setItem("theme", dark ? "dark" : "light");
    };

    setTheme(localStorage.getItem("theme") === "dark");

    themeBtn.onclick = () => {
      setTheme(!document.body.classList.contains("dark-mode"));
    };
  }


  // ================================
  // COMMON JSON LOADER
  // ================================
  function loadJSON(url, loading, error, callback) {
    if (loading) loading.style.display = "block";
    if (error) error.style.display = "none";

    fetch(url)
      .then(response => {
        if (!response.ok) throw new Error("Unable to load data.");
        return response.json();
      })
      .then(data => {
        if (loading) loading.style.display = "none";
        callback(data);
      })
      .catch(err => {
        console.error(err);
        if (loading) loading.style.display = "none";

        if (error) {
          error.textContent = "Failed to load data. Please try again.";
          error.style.display = "block";
        }
      });
  }


  // ================================
  // COMMON PAGINATION
  // ================================
  function paginate(data, page, perPage) {
    const totalPages = Math.ceil(data.length / perPage);
    const start = (page - 1) * perPage;

    return {
      items: data.slice(start, start + perPage),
      totalPages
    };
  }


  // ================================
  // EVENTS
  // ================================
  const eventList = document.getElementById("eventList");

  if (eventList) {
    const search = document.getElementById("searchInput");
    const category = document.getElementById("categoryFilter");
    const sort = document.getElementById("sortEvents");
    const loading = document.getElementById("loadingMessage");
    const error = document.getElementById("errorMessage");
    const prev = document.getElementById("prevPage");
    const next = document.getElementById("nextPage");
    const pageNo = document.getElementById("pageNumber");

    let data = [];
    let page = 1;

    function render() {
      let result = [...data];
      const text = search?.value.toLowerCase().trim() || "";
      const cat = category?.value || "All";
      const sortValue = sort?.value || "default";

      // Search
      if (text) {
        result = result.filter(e =>
          e.title.toLowerCase().includes(text)
        );
      }

      // Category filter
      if (cat && cat.toLowerCase() !== "all") {
        result = result.filter(e => e.category === cat);
      }

      // Sort
      if (sortValue === "title-asc")
        result.sort((a, b) => a.title.localeCompare(b.title));

      if (sortValue === "title-desc")
        result.sort((a, b) => b.title.localeCompare(a.title));

      if (sortValue === "date-asc")
        result.sort((a, b) => new Date(a.date) - new Date(b.date));

      if (sortValue === "date-desc")
        result.sort((a, b) => new Date(b.date) - new Date(a.date));

      const resultPage = paginate(result, page, 5);

      if (page > resultPage.totalPages && resultPage.totalPages > 0) {
        page = resultPage.totalPages;
        return render();
      }

      eventList.innerHTML = "";

      if (!resultPage.items.length) {
        eventList.innerHTML = `
          <div class="col-12">
            <div class="alert alert-info text-center">
              No events found.
            </div>
          </div>
        `;

        if (pageNo) pageNo.textContent = "0";
        if (prev) prev.disabled = true;
        if (next) next.disabled = true;
        return;
      }

      resultPage.items.forEach(event => {
        eventList.innerHTML += `
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
              <div class="card-body">
                <h5 class="card-title">${event.title}</h5>
                <p class="card-text"><strong>Date:</strong> ${event.date}</p>
                <p class="card-text"><strong>Location:</strong> ${event.location}</p>
                <span class="badge bg-primary">${event.category}</span>
              </div>
            </div>
          </div>
        `;
      });

      if (pageNo) pageNo.textContent = page;
      if (prev) prev.disabled = page === 1;
      if (next) next.disabled = page === resultPage.totalPages;
    }

    loadJSON(
      "../data/events.json",
      loading,
      error,
      result => {
        data = result;
        render();
      }
    );

    [search, category, sort].forEach(input => {
      if (input) {
        input.addEventListener(
          input === search ? "input" : "change",
          () => {
            page = 1;
            render();
          }
        );
      }
    });

    if (prev) {
      prev.onclick = () => {
        if (page > 1) {
          page--;
          render();
        }
      };
    }

    if (next) {
      next.onclick = () => {
        page++;
        render();
      };
    }
  }


  // ================================
  // FAQs
  // ================================
  const faqAccordion = document.getElementById("faqAccordion");

  if (faqAccordion) {
    const search = document.getElementById("faqSearchInput");
    const sort = document.getElementById("faqSort");
    const loading = document.getElementById("faqLoadingMessage");
    const error = document.getElementById("faqErrorMessage");
    const prev = document.getElementById("faqPrevPage");
    const next = document.getElementById("faqNextPage");
    const pageNo = document.getElementById("faqPageNumber");

    let data = [];
    let page = 1;

    function render() {
      let result = [...data];
      const text = search?.value.toLowerCase().trim() || "";
      const sortValue = sort?.value || "default";

      // Search
      if (text) {
        result = result.filter(faq =>
          faq.question.toLowerCase().includes(text)
        );
      }

      // Sort
      if (sortValue === "az")
        result.sort((a, b) =>
          a.question.localeCompare(b.question)
        );

      if (sortValue === "za")
        result.sort((a, b) =>
          b.question.localeCompare(a.question)
        );

      const resultPage = paginate(result, page, 5);

      if (page > resultPage.totalPages && resultPage.totalPages > 0) {
        page = resultPage.totalPages;
        return render();
      }

      faqAccordion.innerHTML = "";

      if (!resultPage.items.length) {
        faqAccordion.innerHTML = `
          <div class="alert alert-info text-center">
            No FAQs found.
          </div>
        `;

        if (pageNo) pageNo.textContent = "0";
        if (prev) prev.disabled = true;
        if (next) next.disabled = true;
        return;
      }

      resultPage.items.forEach(faq => {
        const id = "faqCollapse" + faq.id;

        faqAccordion.innerHTML += `
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button
                class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#${id}"
                aria-expanded="false"
                aria-controls="${id}">
                ${faq.question}
              </button>
            </h2>

            <div
              id="${id}"
              class="accordion-collapse collapse"
              data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                ${faq.answer}
              </div>
            </div>
          </div>
        `;
      });

      if (pageNo) pageNo.textContent = page;
      if (prev) prev.disabled = page === 1;
      if (next) next.disabled = page === resultPage.totalPages;
    }

    loadJSON(
      "../data/faqs.json",
      loading,
      error,
      result => {
        data = result;
        render();
      }
    );

    if (search) {
      search.oninput = () => {
        page = 1;
        render();
      };
    }

    if (sort) {
      sort.onchange = () => {
        page = 1;
        render();
      };
    }

    if (prev) {
      prev.onclick = () => {
        if (page > 1) {
          page--;
          render();
        }
      };
    }

    if (next) {
      next.onclick = () => {
        page++;
        render();
      };
    }
  }


  // ================================
  // STUDENTS
  // ================================
  const studentList = document.getElementById("studentList");

  if (studentList) {
    const search = document.getElementById("studentSearchInput");
    const course = document.getElementById("courseFilter");
    const year = document.getElementById("yearFilter");
    const sort = document.getElementById("sortStudents");
    const loading = document.getElementById("studentLoadingMessage");
    const error = document.getElementById("studentErrorMessage");
    const prev = document.getElementById("studentPrevPage");
    const next = document.getElementById("studentNextPage");
    const pageNo = document.getElementById("studentPageNumber");

    let data = [];
    let page = 1;

    function render() {
      let result = [...data];

      const text = search?.value.toLowerCase().trim() || "";
      const selectedCourse = course?.value || "All";
      const selectedYear = year?.value || "All";
      const sortValue = sort?.value || "default";

      // Search
      if (text) {
        result = result.filter(student =>
          student.name.toLowerCase().includes(text)
        );
      }

      // Course filter
      if (selectedCourse !== "All" && selectedCourse !== "") {
        result = result.filter(
          student => student.course === selectedCourse
        );
      }

      // Year filter
      if (selectedYear !== "All" && selectedYear !== "") {
        result = result.filter(
          student => student.year == selectedYear
        );
      }

      // Sort
      if (sortValue === "name-asc")
        result.sort((a, b) => a.name.localeCompare(b.name));

      if (sortValue === "name-desc")
        result.sort((a, b) => b.name.localeCompare(a.name));

      if (sortValue === "year-asc")
        result.sort((a, b) => a.year - b.year);

      if (sortValue === "year-desc")
        result.sort((a, b) => b.year - a.year);

      const resultPage = paginate(result, page, 5);

      if (page > resultPage.totalPages && resultPage.totalPages > 0) {
        page = resultPage.totalPages;
        return render();
      }

      studentList.innerHTML = "";

      if (!resultPage.items.length) {
        studentList.innerHTML = `
          <div class="col-12">
            <div class="alert alert-info text-center">
              No students found.
            </div>
          </div>
        `;

        if (pageNo) pageNo.textContent = "0";
        if (prev) prev.disabled = true;
        if (next) next.disabled = true;
        return;
      }

      resultPage.items.forEach(student => {
        studentList.innerHTML += `
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
              <div class="card-body">
                <div class="text-center mb-3">
                  <i class="bi bi-person-circle fs-1"></i>
                </div>

                <h5 class="card-title text-center fw-bold">
                  ${student.name}
                </h5>

                <p class="card-text">
                  <strong>Email:</strong> ${student.email}
                </p>

                <p class="card-text">
                  <strong>Course:</strong> ${student.course}
                </p>

                <p class="card-text">
                  <strong>Year:</strong> ${student.year}
                </p>
              </div>
            </div>
          </div>
        `;
      });

      if (pageNo) pageNo.textContent = page;
      if (prev) prev.disabled = page === 1;
      if (next) next.disabled = page === resultPage.totalPages;
    }

    loadJSON(
      "../data/students.json",
      loading,
      error,
      result => {
        data = result;
        render();
      }
    );

    [search, course, year, sort].forEach(input => {
      if (input) {
        input.addEventListener(
          input === search ? "input" : "change",
          () => {
            page = 1;
            render();
          }
        );
      }
    });

    if (prev) {
      prev.onclick = () => {
        if (page > 1) {
          page--;
          render();
        }
      };
    }

    if (next) {
      next.onclick = () => {
        page++;
        render();
      };
    }
  }
```

```
});