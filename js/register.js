document.addEventListener("DOMContentLoaded", function () {
    const params = new URLSearchParams(window.location.search);
    const errors = params.get("errors");
    const success = params.get("success");

    if (success) {
        const messageBox = document.getElementById("message");
        messageBox.innerHTML = `
            <div class="container mt-4">
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Success!</strong> ${escapeHtml(success)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        `;
    }

    if (errors) {
        try {
            const errorData = JSON.parse(decodeURIComponent(errors));

            if (errorData.fullName) {
                document.getElementById("nameError").textContent = errorData.fullName;
                markInvalid("fullName");
            }

            if (errorData.enrollment) {
                document.getElementById("enrollmentError").textContent = errorData.enrollment;
                markInvalid("enrollment");
            }

            if (errorData.email) {
                document.getElementById("emailError").textContent = errorData.email;
                markInvalid("email");
            }

            if (errorData.mobile) {
                document.getElementById("mobileError").textContent = errorData.mobile;
                markInvalid("mobile");
            }

            if (errorData.password) {
                document.getElementById("passwordError").textContent = errorData.password;
                markInvalid("password");
            }

            if (errorData.confirmPassword) {
                document.getElementById("confirmPasswordError").textContent = errorData.confirmPassword;
                markInvalid("confirmPassword");
            }

            if (errorData.course) {
                document.getElementById("courseError").textContent = errorData.course;
                markInvalid("course");
            }

            if (errorData.year) {
                document.getElementById("yearError").textContent = errorData.year;
                markInvalid("year");
            }

            if (errorData.gender) {
                document.getElementById("genderError").textContent = errorData.gender;
            }

            if (errorData.terms) {
                document.getElementById("termsError").textContent = errorData.terms;
                markInvalid("agree");
            }

            if (errorData.general) {
                const messageBox = document.getElementById("message");
                messageBox.innerHTML = `
                    <div class="container mt-4">
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Error!</strong> ${escapeHtml(errorData.general)}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                `;
            }

            const firstError = document.querySelector(".is-invalid");
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: "smooth",
                    block: "center"
                });
            }
        } catch (error) {
            console.error("Unable to read validation errors:", error);
        }
    }

    if (errors || success) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    function markInvalid(id) {
        const element = document.getElementById(id);
        if (element) {
            element.classList.add("is-invalid");
        }
    }

    function escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }
});
