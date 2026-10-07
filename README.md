# 🎓 StudentHub

StudentHub is a responsive **Student Management and Information Portal** developed for **WEB DEVELOPMENT FRAMEWORKS (ITUE203)**. The project demonstrates modern web development concepts including **HTML5, CSS3, Bootstrap, JavaScript, JSON, DOM manipulation, LocalStorage, PHP, MySQL, server-side validation, and database connectivity**.

The portal provides a centralized platform for students to register, log in, view events, access student information, submit feedback/contact forms, and interact with dynamic web components.

---

## 📌 Project Objective

The main objective of StudentHub is to develop a responsive, accessible, and user-friendly student portal using frontend and backend web technologies.

The project focuses on:

- Semantic HTML5 web structure
- Responsive and accessible user interfaces
- Bootstrap-based component design
- Client-side form validation
- Server-side form validation
- Regular expression validation
- Password strength checking
- DOM manipulation
- Dynamic data loading using JSON
- Search, filtering, and sorting
- LocalStorage-based theme persistence
- PHP form processing
- MySQL database integration
- Student registration and record management
- Clear success and error handling

---

## ✨ Main Features

### 🌐 Frontend Features

- HTML5 semantic structure
- Responsive website design
- Bootstrap 5 components
- Bootstrap Icons
- Google Fonts
- Responsive navigation bar
- Breadcrumb navigation
- Skip-to-main-content accessibility link
- Responsive cards, tables, forms, and sections
- Mobile-friendly layout
- Dark mode
- Persistent theme using LocalStorage

### 📝 Student Registration

- Student registration form
- Client-side form validation
- Server-side validation
- Required field validation
- Mobile number validation
- Email validation
- Password validation
- Confirm password validation
- Regular Expression validation
- Password strength checking
- Duplicate mobile number checking
- User-friendly validation and error messages
- PHP-based form processing
- MySQL database storage

### 🔐 Login System

- Student login page
- Login form validation
- Server-side form processing
- Database-based credential verification
- Success and error messages
- Redirect handling after form submission

### 📊 Dashboard

- Student dashboard
- Navigation to major portal sections
- Student-related information
- Quick access to portal functionality
- Responsive dashboard layout

### 📅 Events Management

- Dynamic event data using JSON
- Event listing
- Event search
- Event filtering
- Event sorting
- Dynamic DOM rendering
- Responsive event cards

### 👥 Student Directory

- Student information page
- Student records display
- Student search
- Student filtering
- Dynamic student data
- Responsive student information table/cards

### ❓ FAQ

- Frequently Asked Questions page
- Bootstrap accordion
- Expand/collapse functionality
- User-friendly FAQ presentation

### 📞 Contact & Feedback

- Contact form
- Feedback form
- Form validation
- User-friendly input fields
- Success/error handling

### ⚙️ Admin Panel

- Admin page
- Student information management interface
- Student records display
- Database-based student information
- Administrative view of registered students

### 🗄️ Backend & Database

- PHP backend processing
- MySQL database integration
- Server-side validation
- Secure form processing
- Duplicate record checking
- Database insertion and retrieval
- Success and error response handling
- PHP-to-MySQL connectivity

### 🧩 JavaScript Functionality

- DOM manipulation
- Event listeners
- Form validation
- Regular expressions
- Dynamic content generation
- Search functionality
- Filtering
- Sorting
- JSON data processing
- Password strength checking
- Confirm password validation
- LocalStorage
- Dark mode handling
- Interactive UI components

---

## 📄 Pages

The StudentHub portal contains the following major pages:

- 🏠 Home
- ℹ️ About
- 📝 Register
- 🔐 Login
- 📊 Dashboard
- 📅 Events
- 👤 Profile
- 👥 Student Directory
- 📞 Contact
- ⚙️ Admin
- ❓ FAQ
- 💬 Feedback

---

## 📁 Project Structure

```text
StudentHub/
│
│
├── css/
│   └── style.css
│
├── data/
│   ├── events.json
│   ├── faqs.json
│   └── students.json
│
├── docs/
│   ├── Sitemap.png
│   └── wireframe/
│
├── img/
│   ├── Annual_tech_fest.jpg
│   ├── banner1.jpg
│   ├── banner2.jpg
│   ├── banner3.jpg
│   ├── Community_meetup.jpg
│   ├── logo.png
│   └── Workshop_on_ai.jpg
│
├── js/
│   ├── script.js
│   └── register.js
│
├── pages/
│   ├── about.html
│   ├── register.html
│   ├── login.html
│   ├── dashboard.html
│   ├── events.html
│   ├── profile.html
│   ├── contact.html
│   ├── admin.html
│   ├── faq.html
│   ├── feedback.html
│   └── student.html
│
├── php/
│       ├── register.php
|       └── ....
│
├── index.html
└── README.md
```

---

# 🛠️ Technologies Used

| Technology | Purpose |
|---|---|
| **HTML5** | Web page structure |
| **CSS3** | Styling and responsive design |
| **Bootstrap 5** | Responsive UI components |
| **JavaScript ES5/ES6** | Client-side functionality |
| **JSON** | Dynamic data storage |
| **DOM API** | Dynamic page manipulation |
| **Regular Expressions** | Input validation |
| **LocalStorage API** | Theme persistence |
| **PHP** | Server-side processing |
| **MySQL** | Database management |
| **Bootstrap Icons** | UI icons |
| **Google Fonts** | Typography |
| **Visual Studio Code** | Development environment |
| **XAMPP** | Local PHP/MySQL server |

---

# 🧩 HTML5 Semantic Elements

The project uses semantic HTML5 elements to create a meaningful, accessible, and well-structured website.

```html
<header>
<nav>
<main>
<section>
<article>
<footer>
```

These elements improve page structure, readability, accessibility, and maintainability.

---

# 🔐 Form Validation

StudentHub implements both **client-side and server-side validation**.

### Client-Side Validation

JavaScript is used for:

- Required field validation
- Email validation
- Mobile number validation
- Password validation
- Confirm password validation
- Password strength checking
- Regular expression validation
- User-friendly error messages

### Server-Side Validation

PHP is used to:

- Receive form data using POST
- Validate submitted data
- Sanitize input
- Check duplicate records
- Insert valid records into MySQL
- Return success/error responses

Server-side validation ensures that validation is still performed even when browser-side JavaScript validation is bypassed.

---

# 🗄️ Database Integration

StudentHub uses **MySQL** for storing student registration information.

The PHP backend communicates with the MySQL database to:

- Store student registration details
- Retrieve student information
- Check existing records
- Prevent duplicate registrations
- Display database records in the portal

The project is designed to run locally using **XAMPP Apache and MySQL services**.

---

# 🔄 Data Flow

The basic registration workflow is:

```text
Student
   │
   ▼
Registration Form
   │
   ▼
JavaScript Validation
   │
   ▼
PHP Backend
   │
   ▼
Server-Side Validation
   │
   ▼
MySQL Database
   │
   ▼
Success / Error Response
   │
   ▼
StudentHub Page
```

---

# 📊 Dynamic Data Handling

StudentHub uses JSON files for dynamic frontend data.

### Events

```text
data/events.json
```

Used for:

- Event information
- Event listing
- Event search
- Event filtering
- Event sorting

### FAQs

```text
data/faqs.json
```

Used for dynamically displaying frequently asked questions.

### Students

```text
data/students.json
```

Used for student directory information and dynamic student display.

---

# 💾 LocalStorage

The project uses the **LocalStorage API** to persist selected user preferences.

The main implementation includes:

- Dark mode preference
- Theme persistence between page refreshes
- Client-side browser storage

---

# 🌙 Dark Mode

StudentHub provides a dark mode feature that allows users to switch between light and dark themes.

The selected theme is stored using LocalStorage so that the preference remains available after refreshing or reopening the website.

---

# ♿ Accessibility Features

The project includes several accessibility-friendly features:

- Semantic HTML5 elements
- Skip-to-main-content link
- Proper form labels
- Accessible navigation structure
- Breadcrumb navigation
- Keyboard-friendly interactive elements
- Meaningful headings
- Responsive layout
- Clear validation messages

---

# 📱 Responsive Design

StudentHub is designed to work across different screen sizes.

The project uses:

- Bootstrap responsive grid
- Responsive navigation
- Responsive cards
- Responsive forms
- Responsive tables
- CSS media queries
- Mobile-friendly layouts

The website can be accessed from:

- 💻 Desktop
- 💻 Laptop
- 📱 Mobile
- 📟 Tablet

---

# 📷 Documentation

The `docs` directory contains project documentation and design references.

```text
docs/
├── Sitemap.png
└── wireframe/
```

### Documentation Includes

- Website sitemap
- Page navigation structure
- Wireframe designs
- UI planning references

---

# 🚀 How to Run the Project

## 1. Clone or Download the Repository

Download or clone the StudentHub repository.

## 2. Install XAMPP

Install XAMPP with:

- Apache
- MySQL
- PHP

## 3. Place the Project in XAMPP

Copy the project into:

```text
C:\xampp\htdocs\
```

For example:

```text
C:\xampp\htdocs\StudentHUB\
```

## 4. Start XAMPP

Open XAMPP Control Panel and start:

```text
Apache
MySQL
```

## 5. Configure the Database

Open:

```text
http://localhost/phpmyadmin/
```

Create the required StudentHub database and table structure.

## 6. Run StudentHub

Open the project through the local Apache server:

```text
http://localhost/StudentHUB/
```

The PHP functionality should be accessed through the XAMPP server rather than opening the PHP files directly in the browser.

---

# 🔗 Project Repository

**GitHub Repository:**

`https://github.com/yashpaghadar/StudentHub`

---

# 📚 Practical Concepts Covered

StudentHub demonstrates concepts covered throughout the Web Development Frameworks practicals:

- HTML5
- Semantic HTML
- Accessibility
- CSS3
- Responsive Web Design
- Bootstrap
- JavaScript
- JavaScript Events
- DOM Manipulation
- JSON
- Fetching and processing JSON data
- Search
- Filtering
- Sorting
- Regular Expressions
- Form Validation
- Password Validation
- LocalStorage
- PHP
- HTTP POST
- Server-Side Validation
- MySQL
- Database Connectivity
- CRUD-related database operations
- Error Handling
- Success/Error Responses

---

# 🎯 Learning Outcomes

After completing the StudentHub project, the following skills were developed:

- Understanding of modern HTML5 page structure
- Ability to create responsive websites
- Understanding of Bootstrap components
- Implementation of client-side validation
- Implementation of server-side validation
- Working with JavaScript DOM APIs
- Handling JSON data dynamically
- Implementing search, filtering, and sorting
- Using LocalStorage for persistent client-side data
- Creating PHP backend functionality
- Connecting PHP applications with MySQL
- Handling form submissions using POST
- Performing database operations
- Understanding frontend-backend communication
- Developing an integrated full-stack web application

---

# 👨‍💻 Developed By

**Yash K. Paghadar**

**D26IT111**  
**Information Technology Department**  
**CSPIT, CHARUSAT University**

---

# 📜 License

This project is developed for **educational and practical learning purposes only**.

© 2026 Yash K. Paghadar. All rights reserved.