// ======================
// GET FORM ELEMENTS
// ======================

const registerForm = document.getElementById("registerForm");

const fullName = document.getElementById("fullName");
const enrollment = document.getElementById("enrollment");
const email = document.getElementById("email");
const mobile = document.getElementById("mobile");
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirmPassword");
const course = document.getElementById("course");
const year = document.getElementById("year");
const agree = document.getElementById("agree");


// ======================
// SHOW ERROR
// ======================

function showError(input, errorId, message) {

  input.classList.add("is-invalid");
  input.classList.remove("is-valid");

  document.getElementById(errorId).textContent = message;
}


// ======================
// SHOW SUCCESS
// ======================

function showSuccess(input, errorId) {

  input.classList.remove("is-invalid");
  input.classList.add("is-valid");

  document.getElementById(errorId).textContent = "";
}


// ======================
// FULL NAME VALIDATION
// ======================

function validateName() {

  const namePattern = /^[A-Za-z ]{3,50}$/;

  if (fullName.value.trim() === "") {

    showError(
      fullName,
      "nameError",
      "Full name is required."
    );

    return false;
  }

  if (!namePattern.test(fullName.value.trim())) {

    showError(
      fullName,
      "nameError",
      "Enter a valid name using letters and spaces only."
    );

    return false;
  }

  showSuccess(fullName, "nameError");

  return true;
}


// ======================
// ENROLLMENT VALIDATION
// ======================

function validateEnrollment() {

  const enrollmentPattern = /^[A-Za-z0-9]{5,20}$/;

  if (enrollment.value.trim() === "") {

    showError(
      enrollment,
      "enrollmentError",
      "Enrollment number is required."
    );

    return false;
  }

  if (!enrollmentPattern.test(enrollment.value.trim())) {

    showError(
      enrollment,
      "enrollmentError",
      "Enter a valid enrollment number."
    );

    return false;
  }

  showSuccess(
    enrollment,
    "enrollmentError"
  );

  return true;
}


// ======================
// EMAIL VALIDATION
// ======================

function validateEmail() {

  const emailPattern =
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (email.value.trim() === "") {

    showError(
      email,
      "emailError",
      "Email address is required."
    );

    return false;
  }

  if (!emailPattern.test(email.value.trim())) {

    showError(
      email,
      "emailError",
      "Enter a valid email address."
    );

    return false;
  }

  showSuccess(email, "emailError");

  return true;
}


// ======================
// MOBILE VALIDATION
// ======================

function validateMobile() {

  const mobilePattern = /^[6-9][0-9]{9}$/;

  if (mobile.value.trim() === "") {

    showError(
      mobile,
      "mobileError",
      "Mobile number is required."
    );

    return false;
  }

  if (!mobilePattern.test(mobile.value.trim())) {

    showError(
      mobile,
      "mobileError",
      "Enter a valid 10-digit mobile number."
    );

    return false;
  }

  showSuccess(mobile, "mobileError");

  return true;
}


// ======================
// PASSWORD VALIDATION
// ======================

function validatePassword() {

  const passwordValue = password.value;

  const passwordStrength =
    document.getElementById("passwordStrength");


  if (passwordValue === "") {

    showError(
      password,
      "passwordError",
      "Password is required."
    );

    passwordStrength.textContent = "";

    return false;
  }


  if (passwordValue.length < 8) {

    showError(
      password,
      "passwordError",
      "Password must contain at least 8 characters."
    );

    passwordStrength.textContent =
      "Password strength: Weak";

    return false;
  }


  const hasUppercase =
    /[A-Z]/.test(passwordValue);

  const hasLowercase =
    /[a-z]/.test(passwordValue);

  const hasNumber =
    /[0-9]/.test(passwordValue);

  const hasSpecial =
    /[!@#$%^&*]/.test(passwordValue);


  let strength = 0;

  if (hasUppercase) strength++;
  if (hasLowercase) strength++;
  if (hasNumber) strength++;
  if (hasSpecial) strength++;


  if (strength < 3) {

    showError(
      password,
      "passwordError",
      "Use uppercase, lowercase, number and special character."
    );

    passwordStrength.textContent =
      "Password strength: Medium";

    return false;
  }


  showSuccess(
    password,
    "passwordError"
  );

  passwordStrength.textContent =
    "Password strength: Strong";

  return true;
}


// ======================
// CONFIRM PASSWORD
// ======================

function validateConfirmPassword() {

  if (confirmPassword.value === "") {

    showError(
      confirmPassword,
      "confirmPasswordError",
      "Please confirm your password."
    );

    return false;
  }


  if (
    confirmPassword.value !==
    password.value
  ) {

    showError(
      confirmPassword,
      "confirmPasswordError",
      "Passwords do not match."
    );

    return false;
  }


  showSuccess(
    confirmPassword,
    "confirmPasswordError"
  );

  return true;
}


// ======================
// COURSE VALIDATION
// ======================

function validateCourse() {

  if (course.value === "") {

    showError(
      course,
      "courseError",
      "Please select your course."
    );

    return false;
  }

  showSuccess(
    course,
    "courseError"
  );

  return true;
}


// ======================
// YEAR VALIDATION
// ======================

function validateYear() {

  if (year.value === "") {

    showError(
      year,
      "yearError",
      "Please select your year."
    );

    return false;
  }

  showSuccess(
    year,
    "yearError"
  );

  return true;
}


// ======================
// GENDER VALIDATION
// ======================

function validateGender() {

  const selectedGender =
    document.querySelector(
      'input[name="gender"]:checked'
    );

  const genderError =
    document.getElementById("genderError");


  if (!selectedGender) {

    genderError.textContent =
      "Please select your gender.";

    return false;
  }


  genderError.textContent = "";

  return true;
}


// ======================
// TERMS VALIDATION
// ======================

function validateTerms() {

  const termsError =
    document.getElementById("termsError");


  if (!agree.checked) {

    termsError.textContent =
      "You must accept the Terms & Conditions.";

    return false;
  }


  termsError.textContent = "";

  return true;
}


// ======================
// REAL-TIME VALIDATION
// ======================

fullName.addEventListener(
  "input",
  validateName
);

enrollment.addEventListener(
  "input",
  validateEnrollment
);

email.addEventListener(
  "input",
  validateEmail
);

mobile.addEventListener(
  "input",
  validateMobile
);


password.addEventListener(
  "input",
  function () {

    validatePassword();

    if (confirmPassword.value !== "") {
      validateConfirmPassword();
    }

  }
);


confirmPassword.addEventListener(
  "input",
  validateConfirmPassword
);


course.addEventListener(
  "change",
  validateCourse
);


year.addEventListener(
  "change",
  validateYear
);


document
  .querySelectorAll('input[name="gender"]')
  .forEach(function (radio) {

    radio.addEventListener(
      "change",
      validateGender
    );

  });


agree.addEventListener(
  "change",
  validateTerms
);


// ======================
// FORM SUBMIT
// ======================

registerForm.addEventListener(
  "submit",
  function (event) {

    event.preventDefault();


    const isNameValid =
      validateName();

    const isEnrollmentValid =
      validateEnrollment();

    const isEmailValid =
      validateEmail();

    const isMobileValid =
      validateMobile();

    const isPasswordValid =
      validatePassword();

    const isConfirmPasswordValid =
      validateConfirmPassword();

    const isCourseValid =
      validateCourse();

    const isYearValid =
      validateYear();

    const isGenderValid =
      validateGender();

    const isTermsValid =
      validateTerms();


    const formIsValid =
      isNameValid &&
      isEnrollmentValid &&
      isEmailValid &&
      isMobileValid &&
      isPasswordValid &&
      isConfirmPasswordValid &&
      isCourseValid &&
      isYearValid &&
      isGenderValid &&
      isTermsValid;


    if (formIsValid) {

      alert(
        "Registration successful! All inputs are valid."
      );

      registerForm.reset();


      document
        .querySelectorAll(".is-valid")
        .forEach(function (element) {

          element.classList.remove(
            "is-valid"
          );

        });


      document.getElementById(
        "passwordStrength"
      ).textContent = "";

    }

  }
);