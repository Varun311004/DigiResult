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

// Fetch unique batches and semesters from the database to display as divs
$batchQuery = "SELECT DISTINCT batch, semester, department FROM students";
$batchResult = $conn->query($batchQuery);

// Function to sanitize and format batch names
function formatBatchName($batch, $semester, $department)
{
    return strtoupper($department) . " | " . strtoupper($semester) . " | " . strtoupper($batch);
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
        /* Styling for batch display divs */
        .batch-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin: 20px;
        }

        .batch-card {
            width: 250px;
            height: 180px;
            max-height: 240px;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            background-color: #f9f9f9;
        }

        .batch-card h3 {
            margin-bottom: 10px;
            font-size: 18px;
            font-weight: bold;
            color: #363767;
            word-spacing: 7px;
        }

        .batch-card button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 15px;
        }

        .batch-card button:hover {
            background-color: #0056b3;
        }

        .content-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        /* Center the content in the page */
        .content-wrapper {
            margin: 0 auto;
            padding: 30px;
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


        .navbar-profile {
            display: flex;
            align-items: center;
        }

        .navbar-profile .profile-pic {
            height: 40px;
            width: 40px;
            border-radius: 50%;
            cursor: pointer;
            margin-right: 20px;
        }

        .profile-popup {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.3);
            z-index: 1;
            width: 300px;
            height: 150px;
            border-radius: 5%;
            padding: 20px;
            margin-top: -15px;
            margin-right: 30px;
            opacity: 0;
            /* Hidden by opacity initially */
            transform: translateY(40px);
            /* Slightly above its final position */
            transition: opacity 0.4s ease, transform 0.8s ease;
            /* Smooth transition */
        }

        .profile-popup.show {
            opacity: 1;
            /* Fully visible */
            transform: translateY(0);
            /* At its final position */
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
                    <a class="nav-link" href="Update_Student_Batch.php">Update Student Batch</a>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="Result_Page.php"> Show Results (Live) </a>
            </li>
        </ul>
    </div>

    <!-- Content -->
    <section class="content">
        <div class="container">
            <h1 class="Result_heading text-center">Batch-wise Results</h1>
            <div class="content-wrapper">
                <div class="content-container">
                    <!-- Fetch and display each batch -->
                    <?php
                    if ($batchResult->num_rows > 0) {
                        while ($row = $batchResult->fetch_assoc()) {
                            $batch = $row['batch'];
                            $semester = $row['semester'];
                            $department = $row['department'];
                            $batchName = formatBatchName($batch, $semester, $department);
                    ?>
                            <!-- Batch display card -->
                            <div class="batch-card">
                                <h3>
                                    <?php echo $batchName; ?>
                                </h3>
                                <!-- Button to view the results of the selected batch -->
                                <form method="POST" action="view_results.php">
                                    <input type="hidden" name="batch" value="<?php echo $batch; ?>">
                                    <input type="hidden" name="semester" value="<?php echo $semester; ?>">
                                    <input type="hidden" name="department" value="<?php echo $department; ?>">
                                    <button type="submit">View Results</button>
                                </form>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<p>No batches found</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
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