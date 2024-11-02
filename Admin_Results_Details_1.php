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
        .content {
            width: calc(100vw - 250px);
            height: calc(100vh - 89px);
            margin-top: 89px;
            margin-left: 250px;
            position: fixed;
        }

        .box {
            width: 250px;
            height: 340px;
            margin: auto;
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
            width: 5.37em;
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
            width: 157px;
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
            top: -30px;
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

        .sub-content {
            display: flex;
            margin: auto;
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

        /* Next Button */
        .next-btn {
            align-items: center;
            display: flex;
            float: right;
            margin-top: 35px;
            margin-right: 82px;
            width: 100px;
            justify-content: center;
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
                <a
                    class="nav-link active"
                    href="Admin_Results_Details_1.php">
                    Result Details
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Update_Batch_1.php"> Batch & Subject Management </a>
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
        <h1 class="Result_heading">Result Details</h1>
        <form action="Admin_Results_Details_2.php" method="POST" id="resultForm">
            <div class="sub-content">
                <div class="container-fluid box">
                    <h3>Select Year</h3>
                    <input type="hidden" id="year" name="year" />
                    <div id="yearButtons">
                        <?php
                        $conn = mysqli_connect("localhost", "root", "", "srms");
                        if (!$conn) {
                            die("Connection failed: " . mysqli_connect_error());
                        }
                        $yearSql = "SELECT Year FROM year ORDER BY year ASC";
                        $yearResult = $conn->query($yearSql);

                        if ($yearResult->num_rows > 0) {
                            while ($row = $yearResult->fetch_assoc()) {
                                echo '<button type="button" class="btn-year" data-value="' . $row['Year'] . '">' . $row['Year'] . '</button>';
                            }
                        } else {
                            echo "No years found.";
                        }
                        ?>
                    </div>
                </div>
                <div class="container-fluid box">
                    <h3>Select Batch</h3>
                    <input type="hidden" id="batch" name="batch" />
                    <div id="batchButtons">
                        <?php
                        $conn = mysqli_connect("localhost", "root", "", "srms");
                        if (!$conn) {
                            die("Connection failed: " . mysqli_connect_error());
                        }
                        $batchSql = "SELECT Batch FROM batch ORDER BY Batch ASC";
                        $batchResult = $conn->query($batchSql);

                        if ($batchResult->num_rows > 0) {
                            while ($row = $batchResult->fetch_assoc()) {
                                echo '<button type="button" class="btn-batch" data-value="' . $row['Batch'] . '">' . $row['Batch'] . '</button>';
                            }
                        } else {
                            echo "No Batch found.";
                        }
                        ?>
                    </div>
                </div>
                <div class="container-fluid box">
                    <h3>Select Semester</h3>
                    <input type="hidden" id="semester" name="semester" />
                    <div id="semesterButtons">
                        <?php
                        $conn = mysqli_connect("localhost", "root", "", "srms");
                        // Check connection
                        if (!$conn) {
                            die("Connection failed: " . mysqli_connect_error());
                        }
                        // Fetch seasons
                        $semesterSql = "SELECT Semester FROM semester ORDER BY Semester ASC";
                        $semesterResult = $conn->query($semesterSql);

                        if ($semesterResult->num_rows > 0) {
                            while ($row = $semesterResult->fetch_assoc()) {
                                echo '<button type="button" class="btn-semester" data-value="' . $row['Semester'] . '">' . $row['Semester'] . '</button>';
                            }
                        } else {
                            echo "No semesters found.";
                        }
                        ?>
                    </div>
                </div>
                <div class="container-fluid box">
                    <h3 style="font-size: 26px;">Select Department</h3>
                    <input type="hidden" id="department" name="department" />
                    <div id="deptButtons">
                        <?php
                        $conn = mysqli_connect("localhost", "root", "", "srms");
                        // Check connection
                        if (!$conn) {
                            die("Connection failed: " . mysqli_connect_error());
                        }
                        // Fetch departments
                        $deptSql = "SELECT Department FROM department ORDER BY department ASC";
                        $deptResult = $conn->query($deptSql);

                        if ($deptResult->num_rows > 0) {
                            while ($row = $deptResult->fetch_assoc()) {
                                echo '<button type="button" class="btn-department" data-value="' . $row['Department'] . '">' . $row['Department'] . '</button>';
                            }
                        } else {
                            echo "No departments found.";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div>
                <button type="submit" class="btn btn-outline-success next-btn" id="nextButton" disabled>
                    Next
                </button>
            </div>
        </form>

    </section>
    </div>
    </section>

    <script src="script.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const yearButtons = document.querySelectorAll(".btn-year");
            const batchButtons = document.querySelectorAll(".btn-batch");
            const semesterButtons = document.querySelectorAll(".btn-semester");
            const deptButtons = document.querySelectorAll(".btn-department");

            const yearInput = document.getElementById("year");
            const batchInput = document.getElementById("batch");
            const semesterInput = document.getElementById("semester");
            const deptInput = document.getElementById("department");

            const nextButton = document.getElementById("nextButton");

            // Function to enable/disable the Next button based on selections
            function checkSelections() {
                if (yearInput.value && batchInput.value && semesterInput.value && deptInput.value) {
                    nextButton.disabled = false;
                } else {
                    nextButton.disabled = true;
                }
            }

            // Year Selection
            yearButtons.forEach(button => {
                button.addEventListener("click", function() {
                    yearInput.value = this.dataset.value;
                    yearButtons.forEach(btn => btn.classList.remove("selected"));
                    this.classList.add("selected");
                    checkSelections();
                });
            });

            // Batch Selection
            batchButtons.forEach(button => {
                button.addEventListener("click", function() {
                    batchInput.value = this.dataset.value;
                    batchButtons.forEach(btn => btn.classList.remove("selected"));
                    this.classList.add("selected");
                    checkSelections();
                });
            });

            //Semester Selection
            semesterButtons.forEach(button => {
                button.addEventListener("click", function() {
                    semesterInput.value = this.dataset.value;
                    semesterButtons.forEach(btn => btn.classList.remove("selected"));
                    this.classList.add("selected");
                    checkSelections();
                });
            });

            // Department Selection
            deptButtons.forEach(button => {
                button.addEventListener("click", function() {
                    deptInput.value = this.dataset.value;
                    deptButtons.forEach(btn => btn.classList.remove("selected"));
                    this.classList.add("selected");
                    checkSelections();
                });
            });


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