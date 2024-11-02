<?php
// Database connection
$servername = "localhost"; // Usually localhost
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$dbname = "srms"; // Your database name

$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to check if year exists
function checkYear($year, $conn)
{
    $sql = "SELECT * FROM year WHERE Year = '$year'";
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}

// Function to check if department exists
function checkDepartment($department, $conn)
{
    $sql = "SELECT * FROM department WHERE Department = '$department'";
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}

// Function to check if subject exists based on name, subject types, batch, and semester
function checkSubject($subject, $tw_checked, $pr_checked, $ese_checked, $batch, $semester, $department, $conn)
{
    $sql = "SELECT * FROM subjects WHERE Subject = '$subject' AND tw_checked = '$tw_checked' AND pr_checked = '$pr_checked' AND ese_checked = '$ese_checked' AND Batch = '$batch' AND Semester = '$semester' AND Department = '$department'";
    $result = $conn->query($sql);

    return $result->num_rows > 0;
}

// Check which form was submitted and insert data accordingly
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    // Handle Add operation
    if ($action == 'Add') {
        if (isset($_POST['year'])) {
            // Handle Year
            $year = $_POST['year'];
            if (checkYear($year, $conn)) {
                echo "<script>alert('Year already exists in the database!'); window.location.href='Update_Batch_1.php';</script>";
            } else {
                $sql = "INSERT INTO year (Year) VALUES ('$year')";
                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('Year has been successfully added to the database!'); window.location.href='Update_Batch_1.php';</script>";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }
        } elseif (isset($_POST['subject']) || isset($_POST['tw_checked']) || isset($_POST['pr_checked']) || isset($_POST['ese_checked']) || isset($_POST['batch']) || isset($_POST['semester']) || isset($_POST['department']) && !empty($_POST['subject'])) {
            // Handle Subject
            $subject = $_POST['subject'];
            $tw_checked = $_POST['tw_checked']; // Will be 1 if TW is checked, 0 otherwise
            $pr_checked = $_POST['pr_checked']; // Will be 1 if PR is checked, 0 otherwise
            $ese_checked = $_POST['ese_checked']; // Will be 1 if ESE is checked, 0 otherwise
            $batch = $_POST['batch'];
            $semester = $_POST['semester'];
            $department = $_POST['department'];

            // Check if subject already exists with any of the selected types
            if (checkSubject($subject, $tw_checked, $pr_checked, $ese_checked, $batch, $semester, $department, $conn)) {
                echo "<script>alert('Subject already exists in the database!'); window.location.href='Update_Batch_1.php';</script>";
            } else {
                // Insert subject with multiple subject types

                $sql = "INSERT INTO subjects (Subject, tw_checked, pr_checked, ese_checked, Batch, Semester, Department) VALUES ('$subject', '$tw_checked', '$pr_checked', '$ese_checked', '$batch', '$semester', '$department')";
                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('Subject has been successfully added to the database!'); window.location.href='Update_Batch_1.php';</script>";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }
        } elseif (isset($_POST['department'])) {
            // Handle Department
            $department = $_POST['department'];
            if (checkDepartment($department, $conn)) {
                echo "<script>alert('Department already exists in the database!'); window.location.href='Update_Batch_1.php';</script>";
            } else {
                $sql = "INSERT INTO department (Department) VALUES ('$department')";
                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('Department has been successfully added to the database!'); window.location.href='Update_Batch_1.php';</script>";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }
        }
    }

    // Handle Delete operation
    elseif ($action == 'Delete') {
        if (isset($_POST['year'])) {
            // Handle Year
            $year = $_POST['year'];
            if (checkYear($year, $conn)) {
                $sql = "DELETE FROM year WHERE Year = '$year'";
                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('Year deleted successfully!'); window.location.href='Update_Batch_1.php';</script>";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            } else {
                echo "<script>alert('Year not found in the database!'); window.location.href='Update_Batch_1.php';</script>";
            }
        } elseif (isset($_POST['subject']) || isset($_POST['tw-checked']) || isset($_POST['pr_checked']) || isset($_POST['ese_checked']) || isset($_POST['batch']) || isset($_POST['semester']) || isset($_POST['department']) && !empty($_POST['subject'])) {
            // Handle Subject
            $subject = $_POST['subject'];
            $tw_checked = $_POST['tw_checked']; // Will be 1 if TW is checked, 0 otherwise
            $pr_checked = $_POST['pr_checked']; // Will be 1 if PR is checked, 0 otherwise
            $ese_checked = $_POST['ese_checked']; // Will be 1 if ESE is checked, 0 otherwise
            $batch = $_POST['batch'];
            $semester = $_POST['semester'];
            $department = $_POST['department'];

            if (checkSubject($subject, $tw_checked, $pr_checked, $ese_checked, $batch, $semester, $department, $conn)) {
                // Delete subject with all associated subject types
                $sql = "DELETE FROM subjects WHERE Subject = '$subject' AND Batch = '$batch' AND Semester = '$semester' AND Department = '$department'";
                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('Subject deleted successfully!'); window.location.href='Update_Batch_1.php';</script>";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            } else {
                echo "<script>alert('Subject not found in the database!'); window.location.href='Update_Batch_1.php';</script>";
            }
        } elseif (isset($_POST['department'])) {
            // Handle Department
            $department = $_POST['department'];
            if (checkDepartment($department, $conn)) {
                $sql = "DELETE FROM department WHERE Department = '$department'";
                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('Department deleted successfully!'); window.location.href='Update_Batch_1.php';</script>";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            } else {
                echo "<script>alert('Department not found in the database!'); window.location.href='Update_Batch_1.php';</script>";
            }
        }
    }
}

$conn->close();
