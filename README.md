# 📘 DigiResult – Student Result Management System

DigiResult is a secure and efficient web-based platform designed to simplify academic result distribution. It enables students to access their results online through a verification-based OTP system, while providing administrators with a dedicated dashboard to manage student records and publish results seamlessly.

---

## 📌 Table of Contents
- [About the System](#about-the-system)
- [Key Features](#key-features)
- [Technology Stack](#technology-stack)
- [System Workflow](#system-workflow)
- [Project Structure](#project-structure)
- [Setup & Installation](#setup--installation)
- [SMTP & OTP Email Configuration](#smtp--otp-email-configuration)
- [How to Use](#how-to-use)
- [Troubleshooting](#troubleshooting)
- [Future Enhancements](#future-enhancements)
- [Support](#support)

---

## ✅ About the System

DigiResult provides a reliable and paperless approach to result management. Students can securely verify their identity using their registered details and access their academic results instantly. Administrators are equipped with tools to manage student data, upload results, and oversee batches through a secure dashboard.

---

## ✅ Key Features

### 🎓 Student Result Access
- Simple and user-friendly interface
- OTP-based email verification
- Instant result display
- Secure identity validation

### 🛠 Administrator Panel
- Secure login access
- Result publishing and updating
- Student record management
- Batch and academic data organization

### 🔐 Security & Reliability
- Email-based OTP verification
- Backend validation and authorization
- Session-based admin control

---

## ✅ Technology Stack

| Component | Technology |
|----------|------------|
| Frontend | HTML, CSS, JavaScript |
| Backend  | PHP |
| Database | MySQL (phpMyAdmin) |
| Server   | XAMPP (Apache & MySQL) |
| Email Service | Gmail SMTP |

---

## ✅ System Workflow

### 🎓 Student Result Flow
1. Student visits the main page.
2. Enters IEN/Seat Number and registered email.
3. System validates the details.
4. OTP is sent to the student's email.
5. Student enters OTP.
6. Result is displayed upon successful verification.

### 🛠 Administrator Flow
1. Admin logs in using authorized credentials.
2. Accesses the dashboard.
3. Manages student data and uploads results.
4. Publishes and updates results as needed.

---

## ✅ Project Structure (Sample)

DigiResult/
├── index.php # Entry page for OTP & admin login
├── Student/
│ ├── SendOTP.php
│ ├── VerifyOTP.php
│ └── Result.php
├── Admin/
│ ├── Login.php
│ ├── Dashboard.php
│ ├── ManageStudents.php
│ └── ManageResults.php
├── Database/
│ └── digires_db.sql
├── Assets/
│ └── CSS / Images / JS
└── README.md

---

## ✅ Setup & Installation

### 1️⃣ Install XAMPP
- Download from: https://www.apachefriends.org
- Install and launch XAMPP Control Panel
- Start:
  - Apache
  - MySQL

### 2️⃣ Add Project Files
- Clone or download the project
- Place it inside:
  C:\xampp\htdocs\DigiResult

### 3️⃣ Create the Database
- Open browser and visit:
  http://localhost/phpmyadmin
- Create a new database:
  srms
- Open this database from the left side and from the top click on the import tab.
- Then select the file from path DigiResult/SQL/srms.
- Scroll down to the end and hit import button.

### 5️⃣ Admin Credentials
Admin credentials are stored in the database.
Use them to access the admin dashboard.

## ✅ SMTP & OTP Email Configuration
To enable OTP delivery via email:

Step 1: Create Google App Password
  Go to Google Account → Security
  Enable 2-Step Verification
  Generate an App Password
  Copy the 16-character password
Step 2: Configure php.ini
  Enable OpenSSL
  Set sendmail path
Step 3: Configure sendmail.ini
  Set Gmail SMTP details:
    Server: smtp.gmail.com
    Port: 587
    Security: TLS
    Username: your Gmail
    Password: App Password
Step 4: Restart Apache

##  ✅ How to Use

🎓 Students

Visit:
  http://localhost/DigiResult/Login.php
Enter IEN/Seat Number and registered email
Click Send OTP
Enter the OTP received on email
View result instantly

🛠 Administrators

Visit the same link
Enter admin credentials
Access dashboard for management tasks

## ✅ Troubleshooting
| Issue            | Check                                 |
| ---------------- | ------------------------------------- |
| OTP not received | SMTP setup, App Password, Spam folder |
| Database error   | SQL import and DB config              |
| Page not loading | Apache and MySQL running              |

## ✅ Future Enhancements

Printable PDF results
Analytics dashboard
Email notifications
Multi-semester result tracking

## ✅ Support

For queries or assistance, please reach out through the project repository or issue tracker.

## ✅ DigiResult is ready for secure, efficient, and paperless result management.
