<?php
// Database connection
$servername = "localhost";  // Usually localhost
$username = "root";         // Your MySQL username
$password = "";             // Your MySQL password
$dbname = "srms";           // Your database name

$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch form data
    $student_ien_no = $_POST['student_ien_no'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $year = $_POST['year'];
    $department = $_POST['department'];
    $batch = $_POST['batch'];
    $semester = $_POST['semester'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $action = $_POST['action']; // Determine whether to add or update

    // Check if the student already exists in the database
    $checkQuery = "SELECT * FROM students WHERE ien_number='$student_ien_no'";
    $result = $conn->query($checkQuery);

    if ($action == "add") {
        if ($result->num_rows > 0) {
            // If student exists, do not insert, send a message
            echo "<script>
                    alert('Student already exists! Please use the Update option.');
                    window.location.href = 'Student_Add.php';  // Redirect back to the form page
                  </script>";
        } else {
            // If the student does not exist, insert new data
            $insertQuery = "INSERT INTO students (ien_number, first_name, last_name, date_of_birth, gender, email, address, student_number, year, department, batch, semester)
                            VALUES ('$student_ien_no', '$first_name', '$last_name', '$dob', '$gender', '$email', '$address', '$phone', '$year', '$department', '$batch', '$semester')";

            if ($conn->query($insertQuery) === TRUE) {
                echo "<script>
                        alert('Student data has been successfully added to the database!');
                        window.location.href = 'Student_Add.php';  // Redirect back to the form page
                      </script>";
            } else {
                echo "Error: " . $insertQuery . "<br>" . $conn->error;
            }
        }
    } elseif ($action == "update") {
        if ($result->num_rows > 0) {
            // If the student exists, update the existing record
            $updateQuery = "UPDATE students SET 
                            first_name='$first_name', 
                            last_name='$last_name', 
                            date_of_birth='$dob', 
                            gender='$gender', 
                            email='$email', 
                            address='$address', 
                            student_number='$phone', 
                            year='$year', 
                            department='$department',
                            batch='$batch',
                            semester='$semester'
                            WHERE ien_number='$student_ien_no'";

            if ($conn->query($updateQuery) === TRUE) {
                echo "<script>
                        alert('Student data has been successfully updated!');
                        window.location.href = 'Student_Update.php';  // Redirect back to the form page
                      </script>";
            } else {
                echo "Error: " . $updateQuery . "<br>" . $conn->error;
            }
        } else {
            // If the student does not exist, send a message
            echo "<script>
                    alert('Student does not exist! Please use the Add option.');
                    window.location.href = 'Student_Update.php';  // Redirect back to the form page
                  </script>";
        }
    } elseif ($action == "delete") {
        if ($result->num_rows > 0) {
            $deleteQuery = "DELETE FROM students WHERE ien_number = '$student_ien_no'";
            if ($conn->query($deleteQuery) === TRUE) {
                echo "<script>
                        alert('Student data has been successfully deleted!');
                        window.location.href = 'Student_Delete.html';  // Redirect back to the form page
                      </script>";
            } else {
                echo "Error: " . $deleteQuery . "<br>" . $conn->error;
            }
        } else {
            echo "<script>
                    alert('Student does not exist!');
                    window.location.href = 'Student_Delete.html';  // Redirect back to the form page
                  </script>";
        }
    }


    
    // Close connection
    $conn->close();
}
