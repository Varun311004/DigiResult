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

session_start();

// Initialize $_SESSION['results_live'] as an array if it doesn't exist
if (!isset($_SESSION['results_live']) || !is_array($_SESSION['results_live'])) {
    $_SESSION['results_live'] = array();
}

// Function to check if results are live for a specific batch
function areResultsLive($batch, $semester, $department)
{
    $key = "{$batch}_{$semester}_{$department}";
    return isset($_SESSION['results_live'][$key]) && $_SESSION['results_live'][$key];
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
            margin-bottom: 30px;
            font-weight: bold;
            font-size: 2.5rem;
        }

        .content-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .content-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .batch-card {
            width: 280px;
            height: 220px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .batch-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .batch-card h3 {
            margin-bottom: 15px;
            font-size: 1.2rem;
            font-weight: bold;
            color: #363767;
        }

        .batch-card button {
            background-color: #4a90e2;
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }

        .batch-card button:hover {
            background-color: #357abd;
        }

        .live-status {
            margin-top: 10px;
            font-weight: bold;
            text-align: center;
        }

        .live {
            color: #28a745;
        }

        .not-live {
            color: #dc3545;
        }

        .search-box {
            width: 100%;
            max-width: 500px;
            margin: 0 auto 20px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            background-color: #ffffff;
        }

        .search-box input[type="text"] {
            width: 70%;
            height: 40px;
            padding: 0 15px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            font-size: 1rem;
        }

        .search-box input[type="submit"] {
            width: 25%;
            height: 40px;
            border: none;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .search-box input[type="submit"]:hover {
            background-color: #218838;
        }

        @media (max-width: 768px) {
            .content {
                width: 100%;
                margin-left: 0;
            }

            .batch-card {
                width: 100%;
                margin-bottom: 20px;
            }
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
        <div class="container">
            <h1 class="Result_heading text-center">Batch-wise Results</h1>
            <div class="content-wrapper">
                <div class="content-container">
                    <?php
                    if ($batchResult->num_rows > 0) {
                        while ($row = $batchResult->fetch_assoc()) {
                            $batch = $row['batch'];
                            $semester = $row['semester'];
                            $department = $row['department'];
                            $batchName = formatBatchName($batch, $semester, $department);
                            $isLive = areResultsLive($batch, $semester, $department);
                    ?>
                            <div class="batch-card">
                                <h3><?= $batchName ?></h3>
                                <form method="POST" action="view_results.php">
                                    <input type="hidden" name="batch" value="<?php echo $batch; ?>">
                                    <input type="hidden" name="semester" value="<?php echo $semester; ?>">
                                    <input type="hidden" name="department" value="<?php echo $department; ?>">
                                    <button type="submit">View Results</button>
                                </form>
                                <button class="toggle-live"
                                    data-batch="<?= $batch ?>"
                                    data-semester="<?= $semester ?>"
                                    data-department="<?= $department ?>"
                                    data-is-live="<?= $isLive ? '1' : '0' ?>">
                                    <?= $isLive ? 'Make Not Live' : 'Make Live' ?>
                                </button>
                                <p class="live-status <?= $isLive ? 'live' : 'not-live' ?>">
                                    <?= $isLive ? 'Live' : 'Not Live' ?>
                                </p>
                            </div>
                    <?php
                        }
                    } else {
                        echo '<p>No batches found.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>

    <!-- JavaScript -->
    <script src="script.js"></script>
    <script>
        $(document).ready(function() {
            $('.toggle-live').click(function() {
                var button = $(this);
                var isCurrentlyLive = button.attr('data-is-live') === '1';
                var newLiveStatus = !isCurrentlyLive;

                // Update UI immediately
                updateUI(button, newLiveStatus);

                // Send update to server
                $.ajax({
                    url: 'toggle_live.php',
                    type: 'POST',
                    data: {
                        batch: button.data('batch'),
                        semester: button.data('semester'),
                        department: button.data('department'),
                        new_status: newLiveStatus ? 1 : 0
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status !== 'success') {
                            // Revert UI if server update failed
                            updateUI(button, isCurrentlyLive);
                            alert('Failed to update status: ' + response.message);
                        }
                    },
                    error: function() {
                        // Revert UI on error
                        updateUI(button, isCurrentlyLive);
                        alert('Error communicating with server');
                    }
                });
            });

            function updateUI(button, isLive) {
                button.text(isLive ? 'Make Not Live' : 'Make Live');
                button.attr('data-is-live', isLive ? '1' : '0');

                var statusElement = button.siblings('.live-status');
                statusElement.text(isLive ? 'Live' : 'Not Live');
                statusElement.removeClass('live not-live').addClass(isLive ? 'live' : 'not-live');
            }
        });


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