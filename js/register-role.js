document.addEventListener("DOMContentLoaded", function () {
  const roleSelect = document.getElementById("role");
  const enrollmentLabel = document.getElementById("enrollmentLabel");
  const enrollmentInput = document.getElementById("enrollment");
  const courseContainer = document.getElementById("courseContainer");
  const yearContainer = document.getElementById("yearContainer");
  const courseSelect = document.getElementById("course");
  const yearSelect = document.getElementById("year");
  const invitationContainer = document.getElementById("adminInvitationContainer");
  const invitationInput = document.getElementById("adminInvitationCode");

  function updateRegistrationForm() {
    const isAdmin = roleSelect.value === "admin";

    enrollmentLabel.textContent = isAdmin ? "Employee ID" : "Enrollment Number";
    enrollmentInput.placeholder = isAdmin ? "Enter your employee ID" : "Enter your enrollment number";

    courseContainer.hidden = isAdmin;
    yearContainer.hidden = isAdmin;
    invitationContainer.hidden = !isAdmin;

    courseSelect.required = !isAdmin;
    yearSelect.required = !isAdmin;
    invitationInput.required = isAdmin;

    if (isAdmin) {
      courseSelect.value = "";
      yearSelect.value = "";
    } else {
      invitationInput.value = "";
    }
  }

  roleSelect.addEventListener("change", updateRegistrationForm);
  updateRegistrationForm();
});