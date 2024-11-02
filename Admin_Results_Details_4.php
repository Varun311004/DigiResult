<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "srms"; // Adjust this to your database name
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$selected_subject = $_POST['subject'];  // Subject selected from previous page
$ien_number = $_POST['ien_number'];     // Student's ien_number

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Page</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous" />
    <link rel="stylesheet" href="styles.css" />

    <style>
        .box {
            width: 78vw;
            height: 44vh;
            margin-top: 30px;
            margin-left: 30px;
            text-align: center;
            border: 2px solid #1a1b1c;
            overflow: auto;
        }

        .box h3 {
            margin-top: 10px;
            color: #363767;
            text-align: left;
            padding: 20px;
            font-weight: bold;
        }

        /* Dropdown for Student Details */
        .dropdown-container {
            display: none;
            background-color: #262626;
            padding: 5px;
        }

        .dropdown-btn {
            font-size: 18px;
            padding: 6px 8px 6px 16px;
            text-decoration: none;
            color: #f1f1f1;
            display: block;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            outline: none;
        }

        .dropdown-btn.active {
            background-color: #575757;
            color: white;
        }
    </style>
</head>

<body>
    <!-- Navbar -->

    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container-fluid">
            <div class="navbar-brand">
                <a href="#">
                    <img src="Images/logo.webp" alt="Logo" class="logo" />
                </a>
                <span class="clg_name">New Horizon Institute of Technology and
                    Management</span>
            </div>
            <div class="d-flex">
                <div class="navbar-profile">
                    <img
                        src="Images/ProfilePhoto.jpg"
                        alt="Profile"
                        class="profile-pic" />
                    <div class="profile-popup">
                        <p>Username</p>
                        <p>Email</p>
                        <button>Logout</button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->

    <div id="sidebar" class="text-white vh-100">
        <ul class="nav flex-column p-0">
            <li class="nav-item">
                <a class="nav-link" href="Admin.html"> Dashboard </a>
            </li>
            <li class="nav-item">
                <a
                    class="nav-link active"
                    href="Admin_Results_Details_1.php">
                    Result Details
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Update_Batch_1.php">
                    Batch & Subject Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-btn">Student Details
                    <img
                        src="Images/dropdown.png"
                        width="15px"
                        class="ms-3" />
                </a>
                <div class="dropdown-container">
                    <a class="nav-link" href="Student_Add.php">Add Student</a>
                    <a class="nav-link" href="Student_Update.php">Update Student</a>
                    <a class="nav-link" href="Student_Delete.html">Delete Student</a>
                    <a class="nav-link" href="Update_Student_Batch.php">Update Student Batch</a>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Result_Page.php"> Show Results (Live) </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"> Inbox </a>
            </li>
        </ul>
    </div>


    <!-- Content -->
    <section class="content" style="overflow:scroll; overflow-x: hidden;">
        <h1
            class="Result_heading text-center"
            style="word-spacing: 5px; text-decoration: underline">
            Enter Marks for <br><?php echo $selected_subject ?>
        </h1>

        <form action="" method="POST">
            <div class="container-fluid box">
                <h3>Theory:</h3>
            </div>
            <div class="container-fluid box">
                <h3>Practical:</h3>
            </div>
            <div class="container-fluid box">
                <h3>Viva:</h3>
            </div>
        </form>
    </section>

    <script src="script.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var dropdownBtn = document.querySelector(".dropdown-btn");
            var dropdownContainer = document.querySelector(
                ".dropdown-container"
            );

            dropdownBtn.addEventListener("mouseover", function() {
                this.classList.add("active");
                dropdownContainer.style.display = "block";
            });

            dropdownBtn.addEventListener("mouseleave", function() {
                this.classList.remove("active");
                dropdownContainer.style.display = "none";
            });

            // Toggle dropdown on button click
            dropdownBtn.addEventListener("click", function(event) {
                event.stopPropagation(); // Prevent event from bubbling up to the document
                dropdownContainer.style.display =
                    dropdownContainer.style.display === "block" ?
                    "none" :
                    "block";
                dropdownBtn.classList.toggle("active");
            });

            // Keep dropdown open when hovering over the container
            dropdownContainer.addEventListener("mouseenter", function() {
                dropdownContainer.style.display = "block";
            });

            // Hide dropdown when mouse leaves the container
            dropdownContainer.addEventListener("mouseleave", function() {
                dropdownContainer.style.display = "none";
                dropdownBtn.classList.remove("active");
            });

            // Close dropdown if clicked outside of it
            document.addEventListener("click", function(event) {
                if (
                    !dropdownBtn.contains(event.target) &&
                    !dropdownContainer.contains(event.target)
                ) {
                    dropdownContainer.style.display = "none";
                    dropdownBtn.classList.remove("active");
                }
            });
        });
    </script>
</body>

</html>