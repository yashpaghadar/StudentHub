document.addEventListener("DOMContentLoaded", function () {
    const courseSelect = document.getElementById("course");

    if (!courseSelect) {
        console.error("Course dropdown #course was not found.");
        return;
    }

    courseSelect.innerHTML = '<option value="">Loading courses...</option>';

    fetch("/StudentHUB/php/get_courses.php", { cache: "no-store" })
        .then(function (response) {
            if (!response.ok) {
                throw new Error("HTTP Error: " + response.status);
            }
            return response.json();
        })
        .then(function (data) {
            if (!data.success || !Array.isArray(data.courses)) {
                throw new Error(data.message || "Invalid course response.");
            }

            courseSelect.innerHTML = '<option value="">Select Course</option>';

            data.courses.forEach(function (course) {
                const option = document.createElement("option");
                option.value = String(course.course_id);
                option.textContent = course.course_name;
                courseSelect.appendChild(option);
            });

            console.log("Courses loaded:", data.courses.length);
        })
        .catch(function (error) {
            console.error("Registration course loading error:", error);
            courseSelect.innerHTML = '<option value="">Unable to load courses</option>';
        });
});