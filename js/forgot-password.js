document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("forgotPasswordForm");
  const modal = document.getElementById("forgotPasswordModal");
  const detailsStep = document.getElementById("resetDetailsStep");
  const passwordStep = document.getElementById("resetPasswordStep");
  const message = document.getElementById("resetMessage");
  const verifyButton = document.getElementById("verifyResetDetails");
  const resetButton = document.getElementById("submitNewPassword");
  const enrollment = document.getElementById("resetEnrollment");
  const email = document.getElementById("resetEmail");
  const mobile = document.getElementById("resetMobile");
  const newPassword = document.getElementById("newPassword");
  const confirmPassword = document.getElementById("confirmNewPassword");
  let verified = false;

  function showMessage(text, type = "danger") {
    message.textContent = text;
    message.className = "alert alert-" + type;
  }

  function clearMessage() {
    message.textContent = "";
    message.className = "alert d-none";
  }

  function setBusy(button, busy, text) {
    button.disabled = busy;
    if (busy) {
      button.dataset.originalText = button.innerHTML;
      button.textContent = text;
    } else if (button.dataset.originalText) {
      button.innerHTML = button.dataset.originalText;
      delete button.dataset.originalText;
    }
  }

  function resetForm() {
    form.reset();
    verified = false;
    clearMessage();
    detailsStep.hidden = false;
    passwordStep.hidden = true;
    verifyButton.hidden = false;
    verifyButton.disabled = false;
    resetButton.hidden = true;
    resetButton.disabled = true;
    newPassword.disabled = true;
    confirmPassword.disabled = true;
  }

  async function sendRequest(url, data) {
    const response = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json", "Accept": "application/json" },
      credentials: "same-origin",
      cache: "no-store",
      body: JSON.stringify(data)
    });

    const result = await response.json();
    if (!response.ok && !result.message) {
      throw new Error("The request failed. Please try again.");
    }
    return result;
  }

  verifyButton.addEventListener("click", async function () {
    clearMessage();

    if (!enrollment.value.trim() || !email.value.trim() || !mobile.value.trim()) {
      showMessage("Please enter your enrollment/employee ID, email and mobile number.");
      return;
    }

    if (!/^[0-9]{10}$/.test(mobile.value.trim())) {
      showMessage("Please enter a valid 10-digit mobile number.");
      return;
    }

    setBusy(verifyButton, true, "Verifying...");

    try {
      const result = await sendRequest("../php/verify_reset_details.php", {
        enrollment: enrollment.value.trim(),
        email: email.value.trim(),
        mobile: mobile.value.trim()
      });

      if (!result.success) {
        showMessage(result.message || "The supplied details could not be verified.");
        return;
      }

        verified = true;
        detailsStep.hidden = true;
        passwordStep.hidden = false;
        verifyButton.hidden = true;
        resetButton.hidden = false;
        resetButton.disabled = false;
        newPassword.disabled = false;
        confirmPassword.disabled = false;
        newPassword.required = true;
        confirmPassword.required = true;
        newPassword.focus();
        showMessage("Details verified. Enter your new password.", "success");

    } catch (error) {
      showMessage(error.message || "Unable to verify details. Check that XAMPP is running.");
    } finally {
      setBusy(verifyButton, false, "Verify Details");
    }
  });

  form.addEventListener("submit", async function (event) {
    event.preventDefault();
    clearMessage();

    if (!verified) {
      showMessage("Please verify your account details first.");
      return;
    }

    const password = newPassword.value;
    const confirmation = confirmPassword.value;
    const strongPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,100}$/;

    if (!strongPassword.test(password)) {
      showMessage("Password must be 8–100 characters and include uppercase, lowercase, a number and a special character.");
      return;
    }

    if (password !== confirmation) {
      showMessage("New password and confirm password do not match.");
      return;
    }

    setBusy(resetButton, true, "Updating...");

    try {
      const result = await sendRequest("../php/reset_password.php", {
        new_password: password,
        confirm_new_password: confirmation
      });

      if (!result.success) {
        if (result.code === "verification_expired") {
          verified = false;
          detailsStep.hidden = false;
          passwordStep.hidden = true;
          verifyButton.hidden = false;
          resetButton.hidden = true;
          newPassword.disabled = true;
          confirmPassword.disabled = true;
        }
        showMessage(result.message || "Unable to reset your password.");
        return;
      }

      showMessage("Password reset successfully. You can now log in with your new password.", "success");
      form.reset();
      verified = false;
      detailsStep.hidden = false;
      passwordStep.hidden = true;
      verifyButton.hidden = false;
      resetButton.hidden = true;
      newPassword.disabled = true;
      confirmPassword.disabled = true;
      window.setTimeout(function () {
        bootstrap.Modal.getOrCreateInstance(modal).hide();
        window.location.href = "login.html?reset=success";
      }, 1800);
    } catch (error) {
      showMessage(error.message || "Unable to reset password. Please try again.");
    } finally {
      setBusy(resetButton, false, "Reset Password");
    }
  });

  modal.addEventListener("hidden.bs.modal", resetForm);
});