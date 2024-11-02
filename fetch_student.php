<?php
// Database connection
$servername = "localhost";  // Usually localhost
$username = "root";         // Your MySQL username
$password = "";             // Your MySQL password
$dbname = "srms";           // Your database name

$student_ien_no = $_REQUEST['student_ien_no'];

$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($student_ien_no !== "") {
    $query = mysqli_query($conn, "SELECT first_name, last_name, date_of_birth, gender, email, address, student_number, year, department, batch, semester FROM students WHERE ien_number = '$student_ien_no'");
    $row = mysqli_fetch_array($query);
    $first_name = $row["first_name"];
    $last_name = $row["last_name"];
    $dob = $row["date_of_birth"];
    $gender = $row['gender'];
    $email = $row['email'];
    $address = $row['address'];
    $phone = $row['student_number'];
    $year = $row['year'];
    $department = $row['department'];
    $batch = $row['batch'];
    $semester = $row['semester'];
}

$result = array("$first_name", "$last_name", "$dob", "$gender", "$email", "$address", "$phone", "$year", "$department", "$batch", "$semester");

$myJSON = json_encode($result);
echo $myJSON;

// Close connection
$conn->close();
