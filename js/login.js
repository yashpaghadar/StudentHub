document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  const error = params.get("error");
  const logout = params.get("logout");
  const message = document.getElementById("loginMessage");

  if (!message) return;

  const errors = {
    empty: "Please enter your enrollment number and password.",
    invalid: "Invalid enrollment number or password.",
    timeout: "Your session expired. Please log in again.",
    unauthorized: "Please log in to access the dashboard."
  };

  if (error && errors[error]) {
    message.textContent = errors[error];
    message.className = "alert alert-danger";
  } else if (logout === "success") {
    message.textContent = "You have logged out successfully.";
    message.className = "alert alert-success";
  }
});