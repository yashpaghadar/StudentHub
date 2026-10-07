# 🎓 StudentHub – Practical 7

StudentHub is a student registration and record-management module developed for the **WEB DEVELOPMENT FRAMEWORKS (ITUE203)** practical. This practical demonstrates the integration of **HTML5, Bootstrap, JavaScript, PHP, server-side form validation, password hashing, CSV file handling, and dynamic PHP data display**.

---

## 📌 Practical Objective

The main objective of this practical is to develop a student registration system that:

- Collects student information through an HTML form.
- Performs server-side validation using PHP.
- Checks duplicate enrollment numbers, email addresses, and mobile numbers.
- Stores registered student data in a CSV file.
- Secures passwords using PHP `password_hash()`.
- Displays registered students dynamically using PHP.
- Provides clear success and error messages to the user.

---

## 🛠️ Technologies Used

| Technology | Purpose |
|---|---|
| HTML5 | Registration form and page structure |
| CSS3 | Custom styling |
| Bootstrap 5.3 | Responsive UI and components |
| Bootstrap Icons | Form and navigation icons |
| JavaScript | Displays validation/success messages |
| PHP | Server-side validation and data processing |
| CSV | Lightweight student data storage |
| Google Fonts | Poppins font |

---

## 📂 Project Structure

```text
StudentHub-PR7/
│
├── img/
│   └── logo.png
│
└── pages/
    ├── register.html
    ├── register.js
    ├── register.php
    ├── student.php
    └── students.csv
```

### File Description

- **`register.html`** – Contains the StudentHub registration form.
- **`register.js`** – Reads PHP success/error responses from the URL and displays them on the registration page.
- **`register.php`** – Processes the submitted form, validates input, checks duplicate records, hashes the password, and saves the student data.
- **`student.php`** – Reads student records from the CSV file and displays them in a responsive table.
- **`students.csv`** – Stores registered student records.
- **`logo.png`** – StudentHub logo used in the interface.

---

## 📝 Registration Form

The registration form collects the following information:

- Full Name
- Enrollment Number
- Email Address
- Mobile Number
- Password
- Confirm Password
- Course
- Year
- Gender
- Terms & Conditions agreement

The form uses the `POST` method to securely send registration data to `register.php`.

---

## 🔐 Server-Side Validation

PHP validates all submitted data before storing it.

### Full Name
- Required field.
- Minimum 2 characters.
- Maximum 100 characters.
- Allows letters, spaces, dots, apostrophes, and hyphens.

### Enrollment Number
- Required field.
- Minimum 3 characters.
- Maximum 50 characters.
- Allows letters, numbers, `/`, `_`, and `-`.

### Email
- Required field.
- Validated using PHP `FILTER_VALIDATE_EMAIL`.
- Maximum 150 characters.

### Mobile Number
- Must contain exactly 10 digits.
- Must contain only numbers.
- Cannot start with `0`.

### Password
The password must:
- Contain at least 8 characters.
- Include an uppercase letter.
- Include a lowercase letter.
- Include a number.
- Include a special character.

### Confirm Password
- Must match the original password.

### Course, Year and Gender
Only predefined values are accepted by the server.

### Terms & Conditions
The user must agree before registration is completed.

---

## 🔄 Registration Workflow

```text
User fills registration form
          ↓
       POST request
          ↓
     register.php
          ↓
 Server-side validation
          ↓
Duplicate record checking
          ↓
 Password hashing
          ↓
   Save data to CSV
          ↓
 Redirect to register.html
          ↓
 Display success/error message
```

---

## 🔒 Password Security

Passwords are **not stored as plain text**.

PHP's `password_hash()` function is used:

```php
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
```

The generated hash is stored in `students.csv` instead of the original password.

---

## ♻️ Duplicate Record Checking

Before inserting a new student, the system checks the existing CSV records for:

- Duplicate Enrollment Number
- Duplicate Email Address
- Duplicate Mobile Number

If a duplicate is found, the user is redirected back to the registration page with an appropriate error message.

---

## 📊 Student Records

The `student.php` page reads `students.csv` using PHP and displays the registered records in a responsive Bootstrap table.

The table displays:

- ID
- Full Name
- Enrollment Number
- Email
- Mobile Number
- Course
- Year
- Gender
- Registration Date

The password is intentionally not displayed in the student table.

---

## 💾 CSV Data Storage

Student information is stored in:

```text
pages/students.csv
```

The CSV file contains the following fields:

```text
id
full_name
enrollment
email
mobile
password
course
year
gender
created_at
```

A unique numeric ID is automatically generated for each new student.

---

## ▶️ How to Run

This practical requires a PHP-enabled local server such as **XAMPP**.

### 1. Start XAMPP

Start:

- Apache

MySQL is **not required** because this practical uses a CSV file for storage.

### 2. Place the Project

Copy the `StudentHub-PR7` folder into:

```text
C:\xampp\htdocs\
```

The structure should be:

```text
C:\xampp\htdocs\StudentHub-PR7\
```

### 3. Open Registration Page

Open the following URL in a browser:

```text
http://localhost/StudentHub-PR7/pages/register.html
```

### 4. View Student Records

After registering students, open:

```text
http://localhost/StudentHub-PR7/pages/student.php
```

---

## 🧪 Testing Performed

The following cases can be tested:

| Test Case | Expected Result |
|---|---|
| Submit empty form | Validation errors are displayed |
| Invalid email | Email validation error |
| Invalid mobile number | Mobile validation error |
| Weak password | Password validation error |
| Password mismatch | Confirmation error |
| Invalid course/year/gender | Validation error |
| Duplicate enrollment | Duplicate enrollment error |
| Duplicate email | Duplicate email error |
| Duplicate mobile | Duplicate mobile error |
| Valid registration | Student is saved successfully |
| Open student page | Registered students are displayed |

---

## 🔍 Key Concepts Demonstrated

This practical demonstrates:

1. HTML form creation.
2. Bootstrap responsive design.
3. HTTP `POST` form submission.
4. PHP server-side validation.
5. Regular expressions in PHP.
6. Duplicate record detection.
7. Password hashing.
8. CSV file creation and reading.
9. CSV file writing using `fputcsv()`.
10. Dynamic PHP table generation.
11. PHP-to-JavaScript error message handling.
12. Secure HTML output using `htmlspecialchars()`.

---

## 📸 Main Pages

### Student Registration

The registration page provides a responsive form for entering student information.

### Student Records

The student records page displays all successfully registered students in a Bootstrap table.

---

## 🎯 Learning Outcome

After completing this practical, the student is able to:

- Develop a complete web form using HTML5 and Bootstrap.
- Perform reliable server-side validation using PHP.
- Handle form submission using the HTTP `POST` method.
- Store and retrieve data using CSV files.
- Apply password hashing for better security.
- Detect duplicate user information.
- Generate dynamic web content using PHP.
- Integrate JavaScript with server-side PHP responses.
- Build a basic student registration and record-management system.

---

## 👨‍💻 Project

**Project Name:** StudentHub  
**Practical:** 7  
**Subject:** WEB DEVELOPMENT FRAMEWORKS (ITUE203)  
**Year:** 2026

---

## 📄 Conclusion

Practical 7 successfully implements a **StudentHub registration and student-record system using PHP and CSV storage**. The practical demonstrates server-side validation, secure password hashing, duplicate checking, file handling, and dynamic data presentation. It provides a practical understanding of how frontend forms can be connected with backend PHP processing without requiring a database server.
