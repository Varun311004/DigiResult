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
    SELECT students.first_name, students.last_name, students.ien_number, 
           results.subject, results.tw_marks, results.pr_marks, results.ese_marks
    FROM students 
    LEFT JOIN results ON students.ien_number = results.ien_number 
    WHERE students.batch = '$batch' AND students.semester = '$semester' AND students.department = '$department'
    ORDER BY students.ien_number, results.subject";

$resultData = $conn->query($resultQuery);

// Process the results
$students = [];
while ($row = $resultData->fetch_assoc()) {
    $ien_number = $row['ien_number'];
    if (!isset($students[$ien_number])) {
        $students[$ien_number] = [
            'name' => $row['first_name'] . " " . $row['last_name'],
            'ien_number' => $ien_number,
            'subjects' => []
        ];
    }
    if ($row['subject']) {
        $students[$ien_number]['subjects'][] = [
            'subject' => $row['subject'],
            'tw_marks' => $row['tw_marks'],
            'pr_marks' => $row['pr_marks'],
            'ese_marks' => $row['ese_marks']
        ];
    }
}

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
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .content {
            width: calc(100vw - 250px);
            height: calc(100vh - 89px);
            margin-top: 89px;
            margin-left: 250px;
            position: absolute;
            overflow-y: auto;
            padding: 20px;
        }

        h1 {
            color: #363767;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .student-card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .student-header {
            background-color: #007bff;
            color: #ffffff;
            padding: 10px 15px;
        }

        .student-header h2 {
            margin: 0;
            font-size: 1.2rem;
        }

        .result-table-container {
            padding: 10px 30px 35px 15px;
        }

        .result-table {
            width: calc(100vw - 375px);
            margin-bottom: 0;
            table-layout: fixed;
        }

        .result-table th {
            background-color: #f1f3f5;
            color: #495057;
            font-weight: 600;
        }

        .result-table td,
        .result-table th {
            vertical-align: middle;
            padding: 8px 10px;
            border: 1px solid #dee2e6;
            text-align: center;
            font-size: 0.9rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .result-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .no-results {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }

        .back-button {
            margin-top: 20px;
            text-align: center;
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
                <a href="Admin.html">
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
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="Result_Page.php"> Show Results (Live) </a>
            </li>
        </ul>
    </div>

    <!-- Content -->
    <section class="content">
        <h1>
            <i class="fas fa-graduation-cap"></i> 
            Results for <?php echo strtoupper($department) . " " . $semester . " " . $batch; ?>
        </h1>
        
        <?php if (count($students) > 0): ?>
            <?php foreach ($students as $student): ?>
                <div class="student-card">
                    <div class="student-header">
                        <h2>
                            <i class="fas fa-user-graduate"></i> 
                            <?php echo $student['name']; ?> 
                            <small>(IEN: <?php echo $student['ien_number']; ?>)</small>
                        </h2>
                    </div>
                    <?php if (count($student['subjects']) > 0): ?>
                        <div class="result-table-container"><!-- Added a container for the table -->
                            <table class="table result-table">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Theory Marks</th>
                                        <th>Practical Marks</th>
                                        <th>ESE Marks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($student['subjects'] as $subject): ?>
                                        <tr>
                                            <td><?php echo $subject['subject']; ?></td>
                                            <td><?php echo $subject['tw_marks']; ?></td>
                                            <td><?php echo $subject['pr_marks']; ?></td>
                                            <td><?php echo $subject['ese_marks']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-results">
                            <i class="fas fa-exclamation-circle"></i> 
                            No results found for this student.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-exclamation-circle"></i> 
                No results found for this batch.
            </div>
        <?php endif; ?>
        
        <div class="back-button">
            <a href="Result_Page.php" class="btn btn-success">
                <i class="fas fa-arrow-left"></i> 
                Back to Home
            </a>
        </div>
    </section>


    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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