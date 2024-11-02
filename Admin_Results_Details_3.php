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
            width: 79vw;
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
    <section class="content">

        <h1
            class="Result_heading text-center"
            style="word-spacing: 5px; text-decoration: underline">
            List of Subjects
        </h1>

        <form action="Admin_Results_Details_4.php" method="POST">
            <div class="sub-content">
                <div class="container-fluid box">
                    <h3>Select Subject </h3>
                    <input type="hidden" id="subject" name="subject" />
                    <input type="hidden" name="ien_number" value="<?php echo $ien_number; ?>">
                    <?php
                    $conn = mysqli_connect("localhost", "root", "", "srms");
                    if (!$conn) {
                        die("Connection failed: " . mysqli_connect_error());
                    }
                    $ien_number = $_POST['ien_number'];  

                    // Prepare the SQL query
                    $sql = "SELECT s.subject 
                            FROM subjects s 
                            JOIN students st ON s.batch = st.batch AND s.semester = st.semester AND s.department = st.department
                            WHERE st.ien_number = $ien_number
                            ORDER BY Subject ASC";

                    $result = $conn->query($sql);
                    // Fetch and display the subjects
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<button type="button" class="btn-subject" data-value="' . $row['subject'] . '">' . $row['subject'] . '</button>';
                        }
                    } else {
                        echo "No subjects found for your batch and semester.";
                    }
                    ?>
                </div>
            </div>
            <div>
                <button type="submit" class="btn btn-outline-success next-btn" id="nextButton" disabled>
                    Next
                </button>
            </div>
        </form>
    </section>

    <script src="script.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const subjectButtons = document.querySelectorAll(".btn-subject");
            const subjectInput = document.getElementById("subject");
            const nextButton = document.getElementById("nextButton");

            // Function to enable/disable the Next button based on selections
            function checkSelections() {
                if (subjectInput.value) {
                    nextButton.disabled = false;
                } else {
                    nextButton.disabled = true;
                }
            }

            subjectButtons.forEach(button => {
                button.addEventListener("click", function() {
                    subjectInput.value = this.dataset.value;
                    subjectButtons.forEach(btn => btn.classList.remove("selected"));
                    this.classList.add("selected");
                    checkSelections();
                });
            });


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