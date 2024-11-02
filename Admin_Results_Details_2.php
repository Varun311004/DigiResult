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

// Retrieve data from the POST request
$year = $_POST['year'];
$batch = $_POST['batch'];
$semester = $_POST['semester'];
$department = $_POST['department'];

$searchquery = "SELECT ien_number FROM students WHERE year = '$year' AND batch = '$batch' AND semester = '$semester' AND department = '$department'";

// Initializing search variable
$search_query = "";

// If the search form is submitted, modify the search query
if (isset($_POST['search'])) {
    $search = $_POST['search'];
    // Modify query to filter by IEN number (assuming ien_number is in the database)
    $search_query = " AND ien_number LIKE '%$search%'";
}

// SQL query to fetch students based on the selected year, season, and department, and any search input
$sql = "SELECT id, first_name, last_name, ien_number FROM students 
        WHERE year = '$year' AND batch = '$batch' AND semester = '$semester' AND department = '$department' $search_query";

$result = $conn->query($sql);

// SQL query to fetch subjects based on the selected year, batch, semester, and department
$subjectSql = "SELECT Subject, tw_checked, pr_checked, ese_checked FROM subjects WHERE Batch = '$batch' AND Semester = '$semester' AND Department = '$department'";
$subjectResult = $conn->query($subjectSql);

$subjects = [];

// Store subjects in an array with their respective checked values
if ($subjectResult->num_rows > 0) {
    while ($row = $subjectResult->fetch_assoc()) {
        $subjects[] = $row;
    }
}


function sanitize_subject_name($subject)
{
    // Replace problematic characters with text representations
    $subject = str_replace('&', 'and', $subject); // Replace & with "and"
    $subject = str_replace(':', 'colon', $subject); // Replace : with "colon"
    $subject = str_replace('.', 'dot', $subject);  // Replace . with "dot"
    // Replace any remaining non-alphanumeric characters with underscores
    return preg_replace('/[^a-zA-Z0-9]+/', '_', $subject);
}

if (isset($_POST['save_results'])) {
    $subject = $_POST['subject'];  // The selected subject name

    if ($subject) {
        $sanitized_subject = sanitize_subject_name($subject);

        // Check if the arrays for this subject exist in POST data
        if (
            isset($_POST["{$sanitized_subject}_TW"]) ||
            isset($_POST["{$sanitized_subject}_PR"]) ||
            isset($_POST["{$sanitized_subject}_ESE"])
        ) {

            // Get all IEN numbers for which we have data
            $ien_numbers = array_unique(array_merge(
                array_keys($_POST["{$sanitized_subject}_TW"] ?? []),
                array_keys($_POST["{$sanitized_subject}_PR"] ?? []),
                array_keys($_POST["{$sanitized_subject}_ESE"] ?? [])
            ));

            foreach ($ien_numbers as $ien_number) {
                $TW_marks = $_POST["{$sanitized_subject}_TW"][$ien_number] ?? null;
                $PR_marks = $_POST["{$sanitized_subject}_PR"][$ien_number] ?? null;
                $ESE_marks = $_POST["{$sanitized_subject}_ESE"][$ien_number] ?? null;

                // Only process if at least one type of mark is entered and not empty
                if (($TW_marks !== '' && $TW_marks !== null) ||
                    ($PR_marks !== '' && $PR_marks !== null) ||
                    ($ESE_marks !== '' && $ESE_marks !== null)
                ) {

                    // Prepare the query
                    $query = "INSERT INTO results (ien_number, subject, year, batch, semester, department, TW_marks, PR_marks, ESE_marks) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                              ON DUPLICATE KEY UPDATE 
                              TW_marks = VALUES(TW_marks), 
                              PR_marks = VALUES(PR_marks), 
                              ESE_marks = VALUES(ESE_marks)";

                    // Use prepared statement to prevent SQL injection
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param(
                        "sssssssss",
                        $ien_number,
                        $subject,
                        $year,
                        $batch,
                        $semester,
                        $department,
                        $TW_marks,
                        $PR_marks,
                        $ESE_marks
                    );

                    $stmt->execute();
                    $stmt->close();
                }
            }

            echo "<script>alert('Results saved successfully for $subject');</script>";
        } else {
            echo "<script>alert('No data submitted for $subject');</script>";
        }
    } else {
        echo "<script>alert('No subject selected');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Result Details</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous" />
    <link rel="stylesheet" href="styles.css" />
    <style>
        .box {
            width: 75vw;
            height: 35vh;
            text-align: center;
            border: none;
        }

        .box h3 {
            margin-top: 10px;
            color: #363767;
        }


        /* From Uiverse.io by yaasiinaxmed */
        .box button {
            --color: #363767;
            font-family: inherit;
            display: inline-block;
            width: fit-content;
            height: 2.6em;
            line-height: 2.5em;
            overflow: hidden;
            cursor: pointer;
            margin: 20px 10px auto;
            font-size: 17px;
            z-index: 1;
            color: var(--color);
            border: 2px solid var(--color);
            border-radius: 6px;
            position: relative;
        }

        .box button::before {
            position: absolute;
            content: "";
            background: var(--color);
            width: 550px;
            height: 200px;
            z-index: -1;
            border-radius: 50%;
        }

        .box button:hover {
            color: white;
        }

        .box button:before {
            top: 100%;
            left: 100%;
            transition: 0.3s all;
        }

        button:hover::before {
            top: -55px;
            left: -30px;
        }

        .box button.selected {
            color: white;
            background-color: #363767;
        }

        .box button.selected::before {
            top: -30px;
            left: -30px;
        }

        .content {
            width: fit-content;
            height: fit-content;
            margin-top: 89px;
            margin-left: 250px;
            padding-bottom: 50px;
            position: absolute;
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

        table {
            width: auto;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);
            margin-left: 20px;
            table-layout: fixed;
        }

        table th,
        table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }

        th.fixed,
        td.fixed {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 1;
        }

        th.fixed-id,
        td.fixed-id {
            width: 50px;
            text-align: center;
        }

        th.fixed-name,
        td.fixed-name {
            width: 300px;
        }

        th.fixed-ien,
        td.fixed-ien {
            width: 150px;
            text-align: center;
        }

        table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        table .edit-button {
            all: unset;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 15px;
            cursor: pointer;
            width: 60px;
            text-align: center;
            margin-left: 15px;
        }

        /* Hide subject columns initially */
        .subject-column {
            display: none;
        }

        /* Toggle buttons styling */
        .toggle-buttons {
            margin-bottom: 15px;
        }

        /* Styling Tabs at the end of the table */
        .tab-container {
            margin-left: 20px;
            margin-top: 10px;
            text-align: right;
            width: 94%;
        }

        .tab {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px;
            margin-left: 5px;
            cursor: pointer;
            border-radius: 5px;
        }

        .tab:hover {
            background-color: #0056b3;
        }

        .Result_heading {
            color: #1a1b1c;
            font-size: 34px;
            font-weight: bold;
            font-family: "Robota", "sans-serif";
            margin: 30px 30px 20px 30px;
            position: sticky;
        }

        .search-box {
            width: 500px;
            margin-bottom: 20px;
            margin-left: 20px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);
            border-radius: 5px;
            float: left;
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
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Result_Page.php"> Show Results (Live) </a>
            </li>
        </ul>
    </div>

    <!-- Content -->

    <section class="content">
        <h1
            class="Result_heading text-center"
            style="word-spacing: 5px; text-decoration: underline">
            List of Students
        </h1>

        <!-- Search Box -->
        <div class="search-box">
            <form method="POST" action="Admin_Results_Details_2.php">
                <!-- Hidden inputs to pass the year, season, department to the search form -->
                <input type="hidden" name="year" value="<?php echo $year; ?>" />
                <input type="hidden" name="batch" value="<?php echo $batch; ?>" />
                <input type="hidden" name="semester" value="<?php echo $semester; ?>" />
                <input type="hidden" name="department" value="<?php echo $department; ?>" />
                <input
                    type="text"
                    name="search"
                    placeholder="Enter Student IEN..." />
                <input type="submit" value="Search" />
            </form>
        </div>

        <form action="Admin_Results_Details_2.php" method="POST">
            <div class="box">
                <input type="hidden" id="subject" name="subject" />
                <input type="hidden" name="ien_number" value="<?php echo $ien_number; ?>">
                <input type="hidden" name="year" value="<?php echo $year; ?>" />
                <input type="hidden" name="batch" value="<?php echo $batch; ?>" />
                <input type="hidden" name="semester" value="<?php echo $semester; ?>" />
                <input type="hidden" name="department" value="<?php echo $department; ?>" />
                <?php
                if ($subjectResult->num_rows > 0) {
                    foreach ($subjects as $subject) {
                        $sanitized_subject = sanitize_subject_name($subject['Subject']);
                        echo '<button type="button" onclick="toggleSubjectColumn(\'' . $sanitized_subject . '\')" class="btn-subject">' . $subject['Subject'] . '</button>';
                    }
                } else {
                    echo "No subjects found for your batch and semester.";
                }
                ?>
            </div>
            <!-- Save Button to Submit Marks -->
            <button type="submit" name="save_results" class="btn btn-primary mt-3 ms-4">Save Results</button>

            <table>
                <thead>
                    <tr>
                        <th class="fixed-id">ID</th>
                        <th class="fixed-name">Student Name</th>
                        <th class="fixed-ien">IEN No.</th>

                        <?php foreach ($subjects as $subject) : ?>
                            <?php
                            $sanitized_subject = sanitize_subject_name($subject['Subject']);
                            $colspan = (int)$subject['tw_checked'] + (int)$subject['pr_checked'] + (int)$subject['ese_checked'];
                            ?>
                            <th colspan="<?= $colspan ?>" class="subject-column subject-<?= $sanitized_subject ?>"><?= $subject['Subject'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th class="fixed-id"></th>
                        <th class="fixed-name"></th>
                        <th class="fixed-ien"></th>
                        <?php foreach ($subjects as $subject) : ?>
                            <?php $sanitized_subject = sanitize_subject_name($subject['Subject']); ?>
                            <?php if ($subject['tw_checked']) : ?><th class="subject-column subject-<?= $sanitized_subject ?>">TW</th><?php endif; ?>
                            <?php if ($subject['pr_checked']) : ?><th class="subject-column subject-<?= $sanitized_subject ?>">PR</th><?php endif; ?>
                            <?php if ($subject['ese_checked']) : ?><th class="subject-column subject-<?= $sanitized_subject ?>">ESE</th><?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    // Fetch and display student rows
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['id']}</td>";
                            echo "<td>{$row['first_name']} {$row['last_name']}</td>";
                            echo "<td>{$row['ien_number']}</td>";

                            foreach ($subjects as $subject) {
                                $sanitized_subject = sanitize_subject_name($subject['Subject']);
                                if ($subject['tw_checked']) echo "<td class='subject-column subject-$sanitized_subject'><input type='number' name='{$sanitized_subject}_TW[{$row['ien_number']}]'></td>";
                                if ($subject['pr_checked']) echo "<td class='subject-column subject-$sanitized_subject'><input type='number' name='{$sanitized_subject}_PR[{$row['ien_number']}]'></td>";
                                if ($subject['ese_checked']) echo "<td class='subject-column subject-$sanitized_subject'><input type='number' name='{$sanitized_subject}_ESE[{$row['ien_number']}]'></td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No students found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

        </form>
    </section>

    <script src="script.js"></script>
    <script>
        let activeSubject = null;

        function toggleSubjectColumn(subject) {
            if (activeSubject === subject) {
                // Hide and reset if the same subject is clicked
                activeSubject = null;
                document.querySelectorAll('.subject-column').forEach(col => col.style.display = 'none');
            } else {
                // Hide all and show only the selected subject
                activeSubject = subject;
                document.querySelectorAll('.subject-column').forEach(col => col.style.display = 'none');
                document.querySelectorAll('.subject-' + subject).forEach(col => col.style.display = 'table-cell');
            }
            document.getElementById('subject').value = activeSubject || '';
        }

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