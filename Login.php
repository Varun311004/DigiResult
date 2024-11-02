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

// Function to check if results are live
function areResultsLive()
{
    return isset($_SESSION['results_live']) && $_SESSION['results_live'];
}

// Handle OTP generation and sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ien_number']) && isset($_POST['email'])) {
    $ien_number = $_POST['ien_number'];
    $email = $_POST['email'];

    // Query the database to verify ien_number and email
    $sql = "SELECT * FROM students WHERE ien_number = '$ien_number' AND email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // If a match is found, generate an OTP
        $otp = rand(100000, 999999); // 6-digit OTP
        $_SESSION['otp'] = $otp;  // Store the OTP in session
        $_SESSION['email'] = $email;

        // Send OTP via email (assuming you have a mail server configured)
        $subject = "NHITM Result OTP";
        $message = "Your OTP for Student Result Portal is: " . $otp;
        $headers = "From: noreply@nhitm.ac.in";

        if (mail($email, $subject, $message, $headers)) {
            echo "<script>alert('OTP sent to your email. Please check your inbox.');</script>";
        } else {
            echo "<script>alert('Failed to send OTP.');</script>";
        }
    } else {
        // No match found for ien_number and email
        echo "<script>alert('IEN Number and Email does not match.');</script>";
    }
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['otp_verification'])) {
    $entered_otp = $_POST['otp_verification'];

    // Check if OTP matches the session OTP
    if (isset($_SESSION['otp']) && $entered_otp == $_SESSION['otp']) {
        unset($_SESSION['otp']);
        // OTP is correct, redirect to another page
        echo "<script> window.location.href = 'Certificate.php?live=" . (areResultsLive() ? '1' : '0') . "';</script>";
        header("Location: Certificate.php?batch=" . urlencode($batch) . "&semester=" . urlencode($semester) . "&department=" . urlencode($department));
        exit();
    } else {
        // OTP is incorrect
        echo "<script>alert('Incorrect OTP. Please try again.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Result Portal</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous" />
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="style.css" />
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f0f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            height: 100vh;
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #354f8e;
            width: 100%;
            padding: 20px 0;
        }

        .logo {
            height: 80px;
            margin-right: 20px;
        }

        .header h1 {
            color: white;
            font-size: 2rem;
        }

        /* Main container */
        .main-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 100px;
            gap: 100px;
            /* space between the forms */
        }

        /* Form containers */
        .form-container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            margin-top: 30px;
            margin-bottom: 50px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            width: 400px;
            transition: transform 0.3s ease;
        }

        .form-container:hover {
            transform: translateY(-5px);
        }

        .otp-form h2,
        .login-form h2 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #354f8e;
            box-shadow: 0 0 8px rgba(53, 79, 142, 0.2);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #354f8e;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin-top: 15px;
        }

        .btn-submit:hover {
            background-color: #2e4271;
            transform: scale(1.05);
        }

        /* Separator between forms */
        .separator {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            height: 300px;
            width: 5px;
            background: linear-gradient(180deg, #ff9943, #363767);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 0 30px;
        }

        .separator::before {
            content: 'OR';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #f0f0f5;
            color: #354f8e;
            padding: 5px 12px;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .clg_name {
            color: #ffffff;
            cursor: default;
            font-size: 24px;
            margin: 15px 10px 15px 15px;
            text-align: center;
            text-decoration: none;
            font-weight: 500;
        }

        .navbar {
            width: 100vw;
            background-color: #354f8e;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
        }

        .navbar-brand .logo {
            height: 62px;
        }

        .login-container {
            display: flex;
            justify-content: flex-end;
            margin-left: auto;
            /* Push the login form to the right */
        }

        /* Additional Styling for Aesthetics */
        .form-container:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        /* Smooth transitions for button clicks */
        .btn-submit:active {
            transform: translateY(2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                gap: 30px;
                margin-top: 50px;
            }

            .separator {
                display: none;
            }

            .form-container {
                width: 90%;
            }
        }
    </style>
    <script>
        // JavaScript to toggle OTP form visibility
        window.onload = function() {
            var otpForm = document.getElementById('otpForm');
            var otpSent = <?php echo isset($_SESSION['otp']) ? 'true' : 'false'; ?>;
            if (otpForm) {
                if (otpSent) {
                    otpForm.style.display = 'block';
                } else {
                    otpForm.style.display = 'none';
                }
            }
        };
    </script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container-fluid">
            <div class="navbar-brand">
                <a href="Admin.html">
                    <img src="Images/logo.webp" alt="Logo" class="logo" />
                </a>
                <span class="clg_name">New Horizon Institute of Technology and
                    Management</span>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="form-container">
            <form class="otp-form" method="POST" action="Login.php">
                <h2>Welcome to Student Result Portal</h2>
                <div class="form-group">
                    <label for="ien_number">IEN Number:</label>
                    <input type="text" id="ien_number" name="ien_number" required />
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required />
                </div>
                <button type="submit" class="btn btn-primary btn-submit">Send OTP</button>
            </form>

            <?php if (isset($_SESSION['otp'])): ?>
                <br>
                <form id="otpForm" method="POST" action="">
                    <div class="form-group">
                        <label for="otp_verification">Enter OTP:</label>
                        <input type="text" id="otp_verification" name="otp_verification" required />
                    </div>
                    <button type="submit" class="btn btn-success">Verify OTP</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="separator"></div>

        <div class="login-container">
            <div class="form-container">
                <form class="login-form" method="POST" action="Login_b.php">
                    <?php if (isset($_GET['error'])) { ?>
                        <div class="alert alert-primary" role="alert">
                            <?php echo $_GET['error']; ?>
                        </div>
                    <?php } ?>
                    <h2>Login</h2>
                    <div class="form-group">
                        <label for="login_email">Email:</label>
                        <input type="email" id="login_email" name="login_email"
                            value="<?php echo (isset($_GET['login_email'])) ? $_GET['login_email'] : "" ?>" required />
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required />
                    </div>
                    <button type="submit" class="btn-submit">Login</button>
                </form>
            </div>
        </div>
    </div>

</body>

</html>