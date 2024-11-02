<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Update Batch</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous" />
    <link rel="stylesheet" href="styles.css" />
    <style>
        .content {
            width: fit-content;
            height: fit-content;
            margin-top: 89px;
            margin-left: 250px;
            padding-bottom: 50px;
            position: absolute;
        }

        .form-container {
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);
            display: flex;
            width: 58vw;
            margin-left: 45px;
            margin-top: 35px;
            height: 11vw;
        }

        .form-container h2 {
            position: absolute;
        }

        .form-container form {
            width: 55vw;
            padding-top: 55px;
        }

        form input,
        select {
            width: 200px;
            height: 30px;
            margin-left: 5px;
            text-indent: 10px;
            border-radius: 10px;
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
                <a class="nav-link active" href="Update_Batch_1.php">
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
        <h1 class="Result_heading">Batch & Subject Management</h1>

        <!-- Form to Add Year -->
        <div class="form-container">
            <h2>Update Year</h2>
            <form action="Batch_Details.php" method="post">
                <label for="year" style="font-size: large">Year:</label>
                <input type="number" id="year" name="year" required />
                <input
                    type="submit"
                    name="action"
                    value="Add"
                    style="
                            width: 5vw;
                            height: 7vh;
                            margin-left: 200px;
                            text-indent: 0;
                        "
                    class="btn btn-outline-primary" />
                <input
                    type="submit"
                    name="action"
                    value="Delete"
                    style="width: 5vw; height: 7vh; text-indent: 0"
                    class="btn btn-outline-danger" />
            </form>
        </div>

        <!-- Form to Add Department -->
        <div class="form-container">
            <h2>Update Department</h2>
            <form action="Batch_Details.php" method="post">
                <label for="department" style="font-size: large">Department:</label>
                <input type="text" id="department" name="department" />
                <input
                    type="submit"
                    name="action"
                    value="Add"
                    style="
                            width: 5vw;
                            height: 7vh;
                            margin-left: 150px;
                            text-indent: 0;
                        "
                    class="btn btn-outline-primary" />
                <input
                    type="submit"
                    name="action"
                    value="Delete"
                    style="width: 5vw; height: 7vh; text-indent: 0"
                    class="btn btn-outline-danger" />
            </form>
        </div>


        <!-- Form to Add Subjects -->
        <div
            class="form-container"
            style="height: fit-content;">
            <h2>Update Subject</h2>
            <form action="Batch_Details.php" method="post">
                <label for="subject" style="font-size: large">Subject:</label>
                <input
                    type="text"
                    id="subject"
                    name="subject"
                    style="margin-left: 40px"
                    required />
                <input
                    type="submit"
                    name="action"
                    value="Add"
                    style="
                            width: 5vw;
                            height: 7vh;
                            margin-left: 155px;
                            text-indent: 0;
                        "
                    class="btn btn-outline-primary" />
                <input
                    type="submit"
                    name="action"
                    value="Delete"
                    style="width: 5vw; height: 7vh; text-indent: 0"
                    class="btn btn-outline-danger" />
                <br />
                <label for="batch" style="font-size: large">Batch:</label>
                <select
                    id="batch"
                    name="batch"
                    style="
                            margin-left: 55px;
                            margin-top: 15px;
                            width: 200px;
                            height: 30px;
                            border: 2px solid black;
                            border-radius: 10px;
                        "
                    required>
                    <option hidden disabled selected></option>
                    <option value="SE">SE</option>
                    <option value="TE">TE</option>
                </select><br /><br />
                <label for="semester" style="font-size: large">Semester:</label>
                <select
                    id="semester"
                    name="semester"
                    style="
                            margin-left: 25px;
                            width: 200px;
                            height: 30px;
                            border: 2px solid black;
                            border-radius: 10px;
                        "
                    required>
                    <option hidden disabled selected></option>
                    <option>Sem 3</option>
                    <option>Sem 4</option>
                    <option>Sem 5</option>
                    <option>Sem 6</option>
                </select><br /><br>

                <label for="department" style="font-size: large">Department:</label>
                <select id="department" name="department" style="
                            margin-left: 10px;
                            width: 200px;
                            height: 30px;
                            border: 2px solid black;
                            border-radius: 10px;
                        " required>
                    <option hidden disabled selected></option>
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
                        while ($row =
                            $deptResult->fetch_assoc()
                        ) {
                            echo '
                        <option value="' . $row['Department'] . '">
                            ' . $row['Department'] . '
                        </option>
                        ';
                        }
                    } else {
                        echo "No departments found.";
                    } ?>
                </select><br><br>
                <label for="subjectType" style="font-size: large">Subject Type:</label>

                <!-- TW Checkbox -->
                TW <input type="checkbox" style="width: 19px;" name="subjectType" id="tw" value="TW">
                <input type="hidden" name="tw_checked" id="tw_checked" value="0">

                <!-- PR Checkbox -->
                PR <input type="checkbox" style="width: 19px;" name="subjectType" id="pr" value="PR">
                <input type="hidden" name="pr_checked" id="pr_checked" value="0">

                <!-- ESE Checkbox -->
                ESE <input type="checkbox" style="width: 19px;" name="subjectType" id="ese" value="ESE">
                <input type="hidden" name="ese_checked" id="ese_checked" value="0">


            </form>
        </div>
    </section>

    <!-- JavaScript -->

    <script src="script.js"></script>
    <script>
        // Function to handle checkbox state
        function handleCheckbox(checkboxId, hiddenInputId) {
            const checkbox = document.getElementById(checkboxId);
            const hiddenInput = document.getElementById(hiddenInputId);

            // Add event listener to update hidden input value based on checkbox state
            checkbox.addEventListener('change', function() {
                if (checkbox.checked) {
                    hiddenInput.value = 1; // Checkbox is checked
                } else {
                    hiddenInput.value = 0; // Checkbox is unchecked
                }
            });
        }

        // Attach the event listener for each checkbox
        handleCheckbox('tw', 'tw_checked');
        handleCheckbox('pr', 'pr_checked');
        handleCheckbox('ese', 'ese_checked');




        const batchSelect = document.getElementById("batch");
        const semesterSelect = document.getElementById("semester");

        batchSelect.addEventListener("change", () => {
            semesterSelect.innerHTML = ""; // Clear existing options
            const placeholderOption = new Option("", "", true, true);
            placeholderOption.style.display = "none"; // Hide the placeholder option
            semesterSelect.appendChild(placeholderOption);

            if (batchSelect.value === "SE") {
                semesterSelect.appendChild(new Option("Sem 3"));
                semesterSelect.appendChild(new Option("Sem 4"));
            } else if (batchSelect.value === "TE") {
                semesterSelect.appendChild(new Option("Sem 5"));
                semesterSelect.appendChild(new Option("Sem 6"));
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