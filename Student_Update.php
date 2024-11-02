<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Details</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous" />
    <link rel="stylesheet" href="styles.css" />
    <style>
        body {
            background-color: #ffffff;
            font-family: Arial, sans-serif;
            color: #343a40;
            margin: 0;
            padding: 0;
        }

        /* Styling the form container */
        .student-form {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            display: inline-flex;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);
            width: 92%;
            margin-left: 40px;
            margin-bottom: 40px;
        }

        /* Form labels */
        form label {
            color: #343a40;
            font-weight: bold;
            margin-top: 20px;
        }

        /* Form input fields */
        form input[type="text"],
        form input[type="email"],
        form input[type="tel"],
        form input[type="date"],
        form select,
        form textarea {
            width: 100%;
            margin: 5px 0px;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-indent: 5px;
        }

        /* Submit button */
        form button {
            background-color: #198754;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #145e3e;
        }

        /* Media query for responsive design */
        @media (max-width: 768px) {
            #sidebar {
                width: 100%;
                position: relative;
            }

            .content {
                margin-left: 0;
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script>
        function GetDetail(str) {
            if (str.length == 0) {
                document.getElementById("first_name").value = "";
                document.getElementById("last_name").value = "";
                document.getElementById("dob").value = "";
                document.getElementById("gender").value = "";
                document.getElementById("email").value = "";
                document.getElementById("address").value = "";
                document.getElementById("phone").value = "";
                document.getElementById("year").value = "";
                document.getElementById("department").value = "";
                document.getElementById("batch").value = "";
                document.getElementById("semester").value = "";
                return;
            } else {
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        var myObj = JSON.parse(this.responseText);
                        document.getElementById("first_name").value = myObj[0];
                        document.getElementById("last_name").value = myObj[1];
                        document.getElementById("dob").value = myObj[2];
                        document.getElementById("gender").value = myObj[3];
                        document.getElementById("email").value = myObj[4];
                        document.getElementById("address").value = myObj[5];
                        document.getElementById("phone").value = myObj[6];
                        document.getElementById("year").value = myObj[7];
                        document.getElementById("department").value = myObj[8];
                        document.getElementById("batch").value = myObj[9];
                        document.getElementById("semester").value = myObj[10];
                    }
                };
                xhr.open(
                    "GET",
                    "fetch_student.php?student_ien_no=" + str,
                    true
                );
                xhr.send();
            }
        }
    </script>
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
                <a class="nav-link active dropdown-btn">Student Details
                    <img
                        src="Images/dropdown.png"
                        width="15px"
                        class="ms-3" />
                </a>
                <div class="dropdown-container">
                    <a class="nav-link" href="Student_Add.php">Add Student</a>
                    <a class="nav-link active" href="Student_Update.php">Update Student</a>
                    <a class="nav-link" href="Student_Delete.html">Delete Student</a>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Result_Page.php"> Show Results (Live) </a>
            </li>
        </ul>
    </div>

    <!-- Content -->
    <section class="content" style="width: auto">
        <h1 class="Result_heading">Update Student Details</h1>
        <div class="student-form">
            <form
                action="student.php"
                onsubmit="confirmSubmission()"
                method="post">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <label for="student_ien_no">Student IEN No:</label>
                        <br />
                        <input
                            type="text"
                            id="student_ien"
                            name="student_ien_no"
                            onkeyup="GetDetail(this.value)"
                            value=""
                            required />
                        <br />
                        <label for="first_name">First Name:</label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value=""
                            required />

                        <label for="last_name">Last Name:</label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value=""
                            required />

                        <label for="dob">Date of Birth:</label>
                        <input
                            type="date"
                            id="dob"
                            name="dob"
                            value=""
                            required />

                        <label for="gender">Gender:</label>
                        <select id="gender" name="gender" required>
                            <option hidden disabled selected></option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>

                        <label for="email">Email:</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value=""
                            required />

                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <label for="year">Year:</label>
                        <select id="year" name="year" required>
                            <option hidden disabled selected></option>
                            <?php
                            $conn = mysqli_connect("localhost", "root", "", "srms");
                            // Check connection
                            if (!$conn) {
                                die("Connection failed: " . mysqli_connect_error());
                            }
                            // Fetch departments
                            $deptSql = "SELECT Year FROM year ORDER BY year ASC";
                            $deptResult = $conn->query($deptSql);
                            if ($deptResult->num_rows > 0) {
                                while ($row =
                                    $deptResult->fetch_assoc()
                                ) {
                                    echo '<option value ="' . $row['Year'] . '">' . $row['Year'] . '</option>';
                                }
                            } else {
                                echo "No Years found.";
                            }
                            ?>
                        </select>

                        <label for="department">Department:</label>
                        <select id="department" name="department" required>
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
                                    echo '<option value ="' . $row['Department'] . '">' . $row['Department'] . '</option>';
                                }
                            } else {
                                echo "No departments found.";
                            }
                            ?>
                        </select>

                        <label for="batch">Batch:</label>
                        <select id="batch" name="batch" required>
                            <option hidden disabled selected></option>
                            <option value="SE">SE</option>
                            <option value="TE">TE</option>
                        </select>

                        <label for="semester">Semester:</label>
                        <select id="semester" name="semester" required>
                            <option hidden disabled selected></option>
                            <option>Sem 3</option>
                            <option>Sem 4</option>
                            <option>Sem 5</option>
                            <option>Sem 6</option>
                        </select>

                        <label for="phone">Phone Number:</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value=""
                            required />
                        <label for="address">Address:</label>
                        <textarea
                            id="address"
                            name="address"
                            rows="3"
                            required></textarea>
                    </div>
                </div>
                <button
                    type="submit"
                    class="mt-5"
                    name="action"
                    value="update">
                    Update Student
                </button>
            </form>
        </div>
    </section>

    <!-- JavaScript -->
    <script src="script.js"></script>
    <script>
        const batchSelect = document.getElementById('batch');
        const semesterSelect = document.getElementById('semester');

        batchSelect.addEventListener('change', () => {
            semesterSelect.innerHTML = ''; // Clear existing options
            const placeholderOption = new Option('', '', true, true);
            placeholderOption.style.display = 'none'; // Hide the placeholder option
            semesterSelect.appendChild(placeholderOption);

            if (batchSelect.value === 'SE') {
                semesterSelect.appendChild(new Option('Sem 3'));
                semesterSelect.appendChild(new Option('Sem 4'));
            } else if (batchSelect.value === 'TE') {
                semesterSelect.appendChild(new Option('Sem 5'));
                semesterSelect.appendChild(new Option('Sem 6'));
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

        function confirmSubmission() {
            var confirmationMessage =
                "Are you sure you want to update this student?";
            return confirm(confirmationMessage);
        }
    </script>
</body>

</html>