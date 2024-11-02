<?php 
session_start();

if (isset($_POST['login_email']) && isset($_POST['password'])) {

    // Database connection details
    $servername = "localhost";
    $username = "root";
    $dbpassword = "";  // Renaming this to avoid confusion with user password
    $dbname = "srms"; // Adjust this to your database name
    
    // Establish database connection
    $conn = mysqli_connect($servername, $username, $dbpassword, $dbname);

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Retrieve user inputs
    $login_email = $_POST['login_email'];
    $password = $_POST['password'];

    // Prepare and execute the query (adjust the WHERE clause as needed)
    $sql = "SELECT * FROM admincreds WHERE user_id = '$login_email'";
    $result = $conn->query($sql);

    // Check if a record was found
    if ($result && $result->num_rows > 0) {
        // Fetch result as an associative array
        $row = mysqli_fetch_assoc($result);
        
        $id = $row['id'];
        $user_id = $row['user_id'];
        $pass = $row['password'];

        // Verify the password
        if ($login_email === $user_id && $password === $pass) {
            // Store session variables
            $_SESSION['id'] = $id;
            $_SESSION['user_id'] = $user_id;

            // Redirect to the admin page
            header("Location: Admin.html");
            exit;
        } else {
            // Incorrect password
            $em = "Incorrect username or password";
            header("Location: Login.php?error=$em");
            exit;
        }
    } else {
        // No user found with that email
        $em = "Incorrect username or password";
        header("Location: Login.php?error=$em");
        exit;
    }

} else {
    // Redirect if POST data is not set
    header("Location: Login.php");
    exit;
}
?>
