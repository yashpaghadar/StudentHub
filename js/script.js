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
    closeBtn.onclick = () => (notification.style.display = "none");
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
      .then((response) => {
        if (!response.ok) throw new Error("Unable to load data.");
        return response.json();
      })
      .then((data) => {
        if (loading) loading.style.display = "none";
        callback(data);
      })
      .catch((err) => {
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
      totalPages,
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
        result = result.filter((e) => e.title.toLowerCase().includes(text));
      }

      // Category filter
      if (cat && cat.toLowerCase() !== "all") {
        result = result.filter((e) => e.category === cat);
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

      resultPage.items.forEach((event) => {
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

    loadJSON("../data/events.json", loading, error, (result) => {
      data = result;
      render();
    });

    [search, category, sort].forEach((input) => {
      if (input) {
        input.addEventListener(input === search ? "input" : "change", () => {
          page = 1;
          render();
        });
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
        result = result.filter((faq) =>
          faq.question.toLowerCase().includes(text),
        );
      }

      // Sort
      if (sortValue === "az")
        result.sort((a, b) => a.question.localeCompare(b.question));
      if (sortValue === "za")
        result.sort((a, b) => b.question.localeCompare(a.question));

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

      resultPage.items.forEach((faq) => {
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

    loadJSON("../data/faqs.json", loading, error, (result) => {
      data = result;
      render();
    });

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

    // =========================================
    // ESCAPE HTML
    // =========================================

    function escapeHTML(value) {
      if (value === null || value === undefined) {
        return "";
      }

      return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    // =========================================
    // CREATE UPDATE / DELETE MODALS
    // =========================================

    function createStudentModals() {
      if (document.getElementById("studentUpdateModal")) {
        return;
      }

      const modalHTML = `

        <!-- UPDATE STUDENT MODAL -->
        <div class="modal fade"
             id="studentUpdateModal"
             tabindex="-1"
             aria-hidden="true">

          <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

              <div class="modal-header">

                <h5 class="modal-title">
                  <i class="bi bi-pencil-square me-2"></i>
                  Update Student
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

              </div>

              <div class="modal-body">

                <input type="hidden" id="updateStudentId">

                <p class="mb-3">
                  <strong>Student:</strong>
                  <span id="updateStudentName"></span>
                </p>

                <label for="updateField"
                       class="form-label fw-semibold">
                  What do you want to update?
                </label>

                <select id="updateField"
                        class="form-select mb-3">

                  <option value="">
                    Select field
                  </option>

                  <option value="full_name">
                    Full Name
                  </option>

                  <option value="enrollment">
                    Enrollment Number
                  </option>

                  <option value="email">
                    Email
                  </option>

                  <option value="mobile">
                    Mobile Number
                  </option>

                  <option value="course">
                    Course
                  </option>

                  <option value="year">
                    Year
                  </option>

                  <option value="gender">
                    Gender
                  </option>

                  <option value="complete">
                    Complete Student Data
                  </option>

                </select>


                <div id="updateInputArea"></div>


                <div id="updateMessage"
                     class="alert d-none mt-3 mb-0">
                </div>

              </div>

              <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                  Cancel
                </button>

                <button type="button"
                        id="updateStudentButton"
                        class="btn btn-primary">

                  <i class="bi bi-check-circle me-1"></i>
                  Update Data

                </button>

              </div>

            </div>

          </div>

        </div>


        <!-- DELETE STUDENT MODAL -->
        <div class="modal fade"
             id="studentDeleteModal"
             tabindex="-1"
             aria-hidden="true">

          <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

              <div class="modal-header">

                <h5 class="modal-title text-danger">

                  <i class="bi bi-exclamation-triangle me-2"></i>
                  Delete Student

                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

              </div>

              <div class="modal-body">

                <input type="hidden" id="deleteStudentId">

                <p>
                  Are you sure you want to delete
                  <strong id="deleteStudentName"></strong>?
                </p>

                <div class="alert alert-warning mb-0">

                  <i class="bi bi-exclamation-triangle me-2"></i>

                  This action will permanently remove the
                  student's data from the database.

                </div>

                <div id="deleteMessage"
                     class="alert d-none mt-3 mb-0">
                </div>

              </div>

              <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                  Cancel
                </button>

                <button type="button"
                        id="deleteStudentButton"
                        class="btn btn-danger">

                  <i class="bi bi-trash me-1"></i>
                  Delete Permanently

                </button>

              </div>

            </div>

          </div>

        </div>

      `;

      document.body.insertAdjacentHTML("beforeend", modalHTML);
    }

    createStudentModals();

    async function loadUpdateCourses(selectElement, selectedCourse = "") {
      try {
        const response = await fetch("../php/get_courses.php");

        if (!response.ok) {
          throw new Error("Failed to load courses.");
        }

        const data = await response.json();

        selectElement.innerHTML = '<option value="">Select Course</option>';

        if (data.success && Array.isArray(data.courses)) {
          data.courses.forEach((course) => {
            const option = document.createElement("option");
            option.value = course.course_name;
            option.textContent = course.course_name;

            if (course.course_name === selectedCourse) {
              option.selected = true;
            }

            selectElement.appendChild(option);
          });
        }
      } catch (error) {
        console.error("Course loading error:", error);
        selectElement.innerHTML =
          '<option value="">Unable to load courses</option>';
      }
    }

    // =========================================
    // UPDATE INPUT GENERATOR
    // =========================================

    function createUpdateInput(field, student) {
      const area = document.getElementById("updateInputArea");
      if (!area) return;
      area.innerHTML = "";
      // Complete student update
      if (field === "complete") {
        area.innerHTML = `
          <div class="mb-3">
            <label class="form-label">
              Full Name
            </label>
            <input type="text"
                   id="completeFullName"
                   class="form-control"
                   value="${escapeHTML(student.full_name)}">
          </div>
          <div class="mb-3">
            <label class="form-label">
              Enrollment Number
            </label>
            <input type="text"
                   id="completeEnrollment"
                   class="form-control"
                   value="${escapeHTML(student.enrollment)}">
          </div>


          <div class="mb-3">

            <label class="form-label">
              Email
            </label>

            <input type="email"
                   id="completeEmail"
                   class="form-control"
                   value="${escapeHTML(student.email)}">

          </div>


          <div class="mb-3">

            <label class="form-label">
              Mobile Number
            </label>

            <input type="text"
                   id="completeMobile"
                   class="form-control"
                   value="${escapeHTML(student.mobile)}">

          </div>


          <div class="mb-3">

            <label class="form-label">
              Course
            </label>

            <select id="completeCourse"
                    class="form-select">

              <option value="B.Tech IT"
                ${student.course_name === "B.Tech IT" ? "selected" : ""}>
                B.Tech IT
              </option>

              <option value="B.Tech CSE"
                ${student.course_name === "B.Tech CSE" ? "selected" : ""}>
                B.Tech CSE
              </option>

              <option value="BCA"
                ${student.course_name === "BCA" ? "selected" : ""}>
                BCA
              </option>

              <option value="MCA"
                ${student.course_name === "MCA" ? "selected" : ""}>
                MCA
              </option>

              <option value="Diploma IT"
                ${student.course_name === "Diploma IT" ? "selected" : ""}>
                Diploma IT
              </option>

            </select>

          </div>


          <div class="mb-3">

            <label class="form-label">
              Year
            </label>

            <select id="completeYear"
                    class="form-select">

              <option value="1"
                ${student.year == 1 ? "selected" : ""}>
                Year 1
              </option>

              <option value="2"
                ${student.year == 2 ? "selected" : ""}>
                Year 2
              </option>

              <option value="3"
                ${student.year == 3 ? "selected" : ""}>
                Year 3
              </option>

              <option value="4"
                ${student.year == 4 ? "selected" : ""}>
                Year 4
              </option>

            </select>

          </div>


          <div class="mb-3">

            <label class="form-label">
              Gender
            </label>

            <select id="completeGender"
                    class="form-select">

              <option value="Male"
                ${student.gender === "Male" ? "selected" : ""}>
                Male
              </option>

              <option value="Female"
                ${student.gender === "Female" ? "selected" : ""}>
                Female
              </option>

              <option value="Other"
                ${student.gender === "Other" ? "selected" : ""}>
                Other
              </option>

            </select>

          </div>

        `;

        return;
      }

      // Course
      if (field === "course") {
        area.innerHTML = `

          <label class="form-label fw-semibold">
            New Course
          </label>

          <select id="updateValue"
                  class="form-select">

            <option value="B.Tech IT">
              B.Tech IT
            </option>

            <option value="B.Tech CSE">
              B.Tech CSE
            </option>

            <option value="BCA">
              BCA
            </option>

            <option value="MCA">
              MCA
            </option>

            <option value="Diploma IT">
              Diploma IT
            </option>

          </select>

        `;

        document.getElementById("updateValue").value = student.course_name;

        return;
      }

      // Year
      if (field === "year") {
        area.innerHTML = `

          <label class="form-label fw-semibold">
            New Year
          </label>

          <select id="updateValue"
                  class="form-select">

            <option value="1">Year 1</option>
            <option value="2">Year 2</option>
            <option value="3">Year 3</option>
            <option value="4">Year 4</option>

          </select>

        `;

        document.getElementById("updateValue").value = student.year;

        return;
      }

      // Gender
      if (field === "gender") {
        area.innerHTML = `

          <label class="form-label fw-semibold">
            New Gender
          </label>

          <select id="updateValue"
                  class="form-select">

            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>

          </select>

        `;

        document.getElementById("updateValue").value = student.gender;

        return;
      }

      // Normal text/email fields
      let inputType = "text";

      if (field === "email") {
        inputType = "email";
      }

      if (field === "mobile") {
        inputType = "tel";
      }

      area.innerHTML = `

        <label for="updateValue"
               class="form-label fw-semibold">

          New Value

        </label>

        <input type="${inputType}"
               id="updateValue"
               class="form-control"
               value="${escapeHTML(student[field])}">

      `;
    }

    // =========================================
    // SHOW UPDATE MODAL
    // =========================================

    function openUpdateModal(student) {
      const modalElement = document.getElementById("studentUpdateModal");

      const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

      document.getElementById("updateStudentId").value = student.id;

      document.getElementById("updateStudentName").textContent =
        student.full_name;

      document.getElementById("updateField").value = "";

      document.getElementById("updateInputArea").innerHTML = "";

      const message = document.getElementById("updateMessage");

      message.className = "alert d-none mt-3 mb-0";

      message.textContent = "";

      modal.show();
    }

    // =========================================
    // SHOW DELETE MODAL
    // =========================================

    function openDeleteModal(student) {
      const modalElement = document.getElementById("studentDeleteModal");

      const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

      document.getElementById("deleteStudentId").value = student.id;

      document.getElementById("deleteStudentName").textContent =
        student.full_name;

      const message = document.getElementById("deleteMessage");

      message.className = "alert d-none mt-3 mb-0";

      message.textContent = "";

      modal.show();
    }

    // =========================================
    // UPDATE FIELD CHANGE
    // =========================================

    document
      .getElementById("updateField")
      .addEventListener("change", function () {
        const studentId = parseInt(
          document.getElementById("updateStudentId").value,
        );

        const student = data.find((item) => Number(item.id) === studentId);

        if (!student) return;

        createUpdateInput(this.value, student);
      });

    // =========================================
    // UPDATE STUDENT
    // =========================================

    document
      .getElementById("updateStudentButton")
      .addEventListener("click", async function () {
        const studentId = document.getElementById("updateStudentId").value;

        const field = document.getElementById("updateField").value;

        const message = document.getElementById("updateMessage");

        if (!field) {
          message.className = "alert alert-warning mt-3 mb-0";

          message.textContent = "Please select what you want to update.";

          return;
        }

        let requestData = {
          id: studentId,
          field: field,
        };

        // Complete update
        if (field === "complete") {
          requestData.full_name = document
            .getElementById("completeFullName")
            .value.trim();

          requestData.enrollment = document
            .getElementById("completeEnrollment")
            .value.trim();

          requestData.email = document
            .getElementById("completeEmail")
            .value.trim();

          requestData.mobile = document
            .getElementById("completeMobile")
            .value.trim();

          requestData.course = document.getElementById("completeCourse").value;

          requestData.year = document.getElementById("completeYear").value;

          requestData.gender = document.getElementById("completeGender").value;
        } else {
          const input = document.getElementById("updateValue");

          if (!input) {
            message.className = "alert alert-warning mt-3 mb-0";

            message.textContent = "Please enter a value.";

            return;
          }

          requestData.value = input.value.trim();
        }

        this.disabled = true;

        this.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>
         Updating...`;

        try {
          const response = await fetch("../php/update_student.php", {
            method: "POST",

            headers: {
              "Content-Type": "application/json",
            },

            body: JSON.stringify(requestData),
          });

          const result = await response.json();

          if (!response.ok || !result.success) {
            throw new Error(result.message || "Unable to update student.");
          }

          message.className = "alert alert-success mt-3 mb-0";

          message.textContent =
            result.message || "Student updated successfully.";

          // Reload students
          await loadStudents();

          setTimeout(() => {
            const modalElement = document.getElementById("studentUpdateModal");

            bootstrap.Modal.getOrCreateInstance(modalElement).hide();
          }, 1000);
        } catch (err) {
          console.error(err);

          message.className = "alert alert-danger mt-3 mb-0";

          message.textContent = err.message || "Unable to update student.";
        }

        this.disabled = false;

        this.innerHTML = `<i class="bi bi-check-circle me-1"></i>
         Update Data`;
      });

    // =========================================
    // DELETE STUDENT
    // =========================================

    document
      .getElementById("deleteStudentButton")
      .addEventListener("click", async function () {
        const studentId = document.getElementById("deleteStudentId").value;

        const message = document.getElementById("deleteMessage");

        this.disabled = true;

        this.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>
         Deleting...`;

        try {
          const response = await fetch("../php/delete_student.php", {
            method: "POST",

            headers: {
              "Content-Type": "application/json",
            },

            body: JSON.stringify({
              id: studentId,
            }),
          });

          const result = await response.json();

          if (!response.ok || !result.success) {
            throw new Error(result.message || "Unable to delete student.");
          }

          message.className = "alert alert-success mt-3 mb-0";

          message.textContent =
            result.message || "Student deleted successfully.";

          await loadStudents();

          setTimeout(() => {
            const modalElement = document.getElementById("studentDeleteModal");

            bootstrap.Modal.getOrCreateInstance(modalElement).hide();
          }, 1000);
        } catch (err) {
          console.error(err);

          message.className = "alert alert-danger mt-3 mb-0";

          message.textContent = err.message || "Unable to delete student.";
        }

        this.disabled = false;

        this.innerHTML = `<i class="bi bi-trash me-1"></i>
         Delete Permanently`;
      });

    // =========================================
    // LOAD STUDENTS FROM MYSQL
    // =========================================

    async function loadStudents() {
      if (loading) {
        loading.style.display = "block";
      }

      if (error) {
        error.style.display = "none";
      }

      try {
        const response = await fetch("../php/get_students.php");

        if (!response.ok) {
          throw new Error("Unable to load students.");
        }

        const result = await response.json();

        if (!result.success) {
          throw new Error(result.message || "Unable to load students.");
        }

        data = Array.isArray(result.students) ? result.students : [];

        page = 1;

        render();
      } catch (err) {
        console.error(err);

        if (error) {
          error.textContent = err.message || "Failed to load student data.";

          error.style.display = "block";
        }
      } finally {
        if (loading) {
          loading.style.display = "none";
        }
      }
    }

    // =========================================
    // RENDER STUDENTS
    // =========================================

    function render() {
      let result = [...data];

      const text = search?.value.toLowerCase().trim() || "";

      const selectedCourse = course?.value || "All";

      const selectedYear = year?.value || "All";

      const sortValue = sort?.value || "default";

      // =========================================
      // SEARCH
      // =========================================

      if (text) {
        result = result.filter((student) => {
          const name = String(student.full_name || "").toLowerCase();

          const enrollment = String(student.enrollment || "").toLowerCase();

          const email = String(student.email || "").toLowerCase();

          const mobile = String(student.mobile || "").toLowerCase();

          return (
            name.includes(text) ||
            enrollment.includes(text) ||
            email.includes(text) ||
            mobile.includes(text)
          );
        });
      }

      // =========================================
      // COURSE FILTER
      // =========================================

      if (selectedCourse !== "All" && selectedCourse !== "") {
        result = result.filter(
          (student) => student.course_name === selectedCourse,
        );
      }

      // =========================================
      // YEAR FILTER
      // =========================================

      if (selectedYear !== "All" && selectedYear !== "") {
        result = result.filter(
          (student) => String(student.year) === String(selectedYear),
        );
      }

      // =========================================
      // SORT
      // =========================================

      if (sortValue === "name-asc") {
        result.sort((a, b) =>
          String(a.full_name).localeCompare(String(b.full_name)),
        );
      }

      if (sortValue === "name-desc") {
        result.sort((a, b) =>
          String(b.full_name).localeCompare(String(a.full_name)),
        );
      }

      if (sortValue === "year-asc") {
        result.sort((a, b) => Number(a.year) - Number(b.year));
      }

      if (sortValue === "year-desc") {
        result.sort((a, b) => Number(b.year) - Number(a.year));
      }

      // =========================================
      // PAGINATION
      // =========================================

      const resultPage = paginate(result, page, 5);

      if (page > resultPage.totalPages && resultPage.totalPages > 0) {
        page = resultPage.totalPages;

        return render();
      }

      studentList.innerHTML = "";

      // =========================================
      // NO STUDENTS
      // =========================================

      if (!resultPage.items.length) {
        studentList.innerHTML = `

          <div class="col-12">

            <div class="alert alert-info text-center">

              <i class="bi bi-info-circle me-2"></i>

              No students found.

            </div>

          </div>

        `;

        if (pageNo) {
          pageNo.textContent = "0";
        }

        if (prev) {
          prev.disabled = true;
        }

        if (next) {
          next.disabled = true;
        }

        return;
      }

      // =========================================
      // STUDENT CARDS
      // =========================================

      resultPage.items.forEach((student) => {
        studentList.innerHTML += `

          <div class="col-md-6 col-lg-4">

            <div class="card h-100 shadow-sm">

              <div class="card-body">

                <div class="text-center mb-3">

                  <i class="bi bi-person-circle fs-1"></i>

                </div>


                <h5 class="card-title text-center fw-bold">

                  ${escapeHTML(student.full_name)}

                </h5>


                <p class="card-text">

                  <strong>
                    Enrollment:
                  </strong>

                  ${escapeHTML(student.enrollment)}

                </p>


                <p class="card-text">

                  <strong>
                    Email:
                  </strong>

                  ${escapeHTML(student.email)}

                </p>


                <p class="card-text">

                  <strong>
                    Mobile:
                  </strong>

                  ${escapeHTML(student.mobile)}

                </p>


                <p class="card-text">

                  <strong>
                    Course:
                  </strong>

                  ${escapeHTML(student.course_name)}

                </p>


                <p class="card-text">

                  <strong>
                    Year:
                  </strong>

                  ${escapeHTML(student.year)}

                </p>


                <p class="card-text">

                  <strong>
                    Gender:
                  </strong>

                  ${escapeHTML(student.gender)}

                </p>


                <div class="d-flex gap-2 mt-3">

                  <button
                    type="button"
                    class="btn btn-primary btn-sm flex-fill update-student-btn"
                    data-student-id="${student.id}">

                    <i class="bi bi-pencil-square me-1"></i>
                    Update

                  </button>


                  <button
                    type="button"
                    class="btn btn-danger btn-sm flex-fill delete-student-btn"
                    data-student-id="${student.id}">

                    <i class="bi bi-trash me-1"></i>
                    Delete

                  </button>

                </div>

              </div>

            </div>

          </div>

        `;
      });

      // =========================================
      // UPDATE BUTTON EVENTS
      // =========================================

      document.querySelectorAll(".update-student-btn").forEach((button) => {
        button.addEventListener("click", () => {
          const studentId = Number(button.dataset.studentId);

          const student = data.find((item) => Number(item.id) === studentId);

          if (student) {
            openUpdateModal(student);
          }
        });
      });

      // =========================================
      // DELETE BUTTON EVENTS
      // =========================================

      document.querySelectorAll(".delete-student-btn").forEach((button) => {
        button.addEventListener("click", () => {
          const studentId = Number(button.dataset.studentId);

          const student = data.find((item) => Number(item.id) === studentId);

          if (student) {
            openDeleteModal(student);
          }
        });
      });

      // =========================================
      // PAGE NUMBER
      // =========================================

      if (pageNo) {
        pageNo.textContent = page;
      }

      // =========================================
      // PREVIOUS / NEXT
      // =========================================

      if (prev) {
        prev.disabled = page === 1;
      }

      if (next) {
        next.disabled = page === resultPage.totalPages;
      }
    }

    // =========================================
    // SEARCH / FILTER / SORT EVENTS
    // =========================================

    [search, course, year, sort].forEach((input) => {
      if (input) {
        input.addEventListener(
          input === search ? "input" : "change",

          () => {
            page = 1;

            render();
          },
        );
      }
    });

    // =========================================
    // PREVIOUS PAGE
    // =========================================

    if (prev) {
      prev.onclick = () => {
        if (page > 1) {
          page--;

          render();
        }
      };
    }

    // =========================================
    // NEXT PAGE
    // =========================================

    if (next) {
      next.onclick = () => {
        const totalPages = Math.ceil(data.length / 5);

        if (page < totalPages) {
          page++;

          render();
        }
      };
    }

    // =========================================
    // INITIAL LOAD
    // =========================================

    loadStudents();
  }
});
