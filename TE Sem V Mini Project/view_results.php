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

// Retrieve batch, semester, and department details from the request
$batch = $_POST['batch'];
$semester = $_POST['semester'];
$department = $_POST['department'];

// SQL query to fetch the results for the specific batch, semester, and department
$resultQuery = "
    SELECT students.first_name, students.last_name, students.ien_number, results.tw_marks, results.pr_marks, results.ese_marks, subjects.Subject 
    FROM students 
    JOIN results ON students.ien_number = results.ien_number 
    JOIN subjects ON results.subject_id = subjects.id
    WHERE students.batch = '$batch' AND students.semester = '$semester' AND students.department = '$department'";

$resultData = $conn->query($resultQuery);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous" />
    <link rel="stylesheet" href="styles.css" />
    <style>
        .result-table {
            margin: 30px;
            width: 95%;
            text-align: left;
            border-collapse: collapse;
        }

        .result-table th,
        .result-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .result-table th {
            background-color: #f2f2f2;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            color: #363767;
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

        .search-box {
            width: 50%;
            margin-bottom: 20px;
            margin-left: 20px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);
            border-radius: 5px;
            float: left;
            position: absolute;
        }

        .search-box h4 {
            padding: 5px 0 10px 10px;
        }

        .search-box input[type="text"] {
            width: 60%;
            height: 30px;
            padding-left: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .search-box input[type="submit"] {
            all: unset;
            height: 33px;
            border: none;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
            text-align: center;
            width: 140px;
            align-self: first baseline;
        }
    </style>
</head>

<body>
    <!-- Nav bar -->

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

    <!-- Side Bar -->

    <div id="sidebar" class="text-white vh-100">
        <ul class="nav flex-column p-0">
            <li class="nav-item">
                <a class="nav-link" href="Admin.html"> Dashboard </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Admin_Results_Details_1.php">
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
                <a class="nav-link active" href="Result_Page.php"> Show Results (Live) </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"> Inbox </a>
            </li>
        </ul>
    </div>

    <!-- Content -->
    <section class="content">
        <h1>Results for Batch:
            <?php echo strtoupper($department) . " Sem " . $semester . " Batch " . $batch; ?>
        </h1>

        <table class="result-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>IEN Number</th>
                    <th>Subject</th>
                    <th>TW Marks</th>
                    <th>PR Marks</th>
                    <th>ESE Marks</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($resultData->num_rows > 0) {
                    while ($row = $resultData->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['first_name']} {$row['last_name']}</td>";
                        echo "<td>{$row['ien_number']}</td>";
                        echo "<td>{$row['Subject']}</td>";
                        echo "<td>{$row['tw_marks']}</td>";
                        echo "<td>{$row['pr_marks']}</td>";
                        echo "<td>{$row['ese_marks']}</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No results found for this batch</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </section>

    <!-- JavaScript -->
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