document.addEventListener("DOMContentLoaded", function () {
  const courseSelect = document.getElementById("course");

  if (!courseSelect) {
    return;
  }

  fetch("/StudentHUB/php/get_courses.php")
    .then(function (response) {
      if (!response.ok) {
        throw new Error("HTTP Error: " + response.status);
      }

      return response.json();
    })
    .then(function (data) {
      console.log("Registration courses:", data);

      if (!data.success || !Array.isArray(data.courses)) {
        throw new Error(data.message || "Invalid course data.");
      }

      courseSelect.innerHTML = '<option value="">Select Course</option>';

      data.courses.forEach(function (course) {
        const option = document.createElement("option");

        option.value = course.course_name;
        option.textContent = course.course_name;

        courseSelect.appendChild(option);
      });
    })
    .catch(function (error) {
      console.error("Registration course loading error:", error);

      courseSelect.innerHTML =
        '<option value="">Unable to load courses</option>';
    });
});
