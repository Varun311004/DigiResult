<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "srms";
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to check if results are live
function areResultsLive($batch, $semester, $department)
{
    $key = "{$batch}_{$semester}_{$department}";
    return isset($_SESSION['results_live'][$key]) && $_SESSION['results_live'][$key];
}

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: Login.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch student data
$studentQuery = "SELECT * FROM students WHERE email = '$email'";
$studentResult = $conn->query($studentQuery);

if ($studentResult->num_rows > 0) {
    $studentData = $studentResult->fetch_assoc();
    $ien_number = $studentData['ien_number'];
    $studentName = $studentData['first_name'] . ' ' . $studentData['last_name'];
    $department = $studentData['department'];
    $semester = $studentData['semester'];
    $batch = $studentData['batch'];

    // Check if results are live for this student's batch
    if (!areResultsLive($batch, $semester, $department)) {
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Results Not Available</title>
            <style>
                body {
                font-family: Arial, sans-serif;
                background-color: #f0f2f5;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .message {
                background-color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                padding: 40px;
                text-align: center;
                width: 400px;
            }
            h1 {
                color: #1a73e8;
                font-size: 24px;
                margin-bottom: 20px;
            }
            p {
                color: #5f6368;
                font-size: 16px;
                line-height: 1.5;
            }
            </style>
        </head>
        <body>
            <div class="message">
                <h1>Results Not Available</h1>
                <p>The results are currently not live.<br> Please check back later.</p>
            </div>
        </body>
        </html>';
        exit;
    }

    // Fetch results
    $resultsQuery = "SELECT * FROM results WHERE ien_number = '$ien_number'";
    $resultsResult = $conn->query($resultsQuery);

    $results = [];
    while ($row = $resultsResult->fetch_assoc()) {
        $results[] = $row;
    }
} else {
    die("Student not found");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Results</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f0f2f5;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h1,
        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }

        .student-info {
            background-color: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .student-info p {
            margin: 10px 0;
            font-size: 16px;
        }

        .student-info strong {
            font-weight: bold;
            color: #34495e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e0e0e0;
        }

        .total-row {
            font-weight: bold;
            background-color: #2ecc71;
            color: #34495e;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Student Results</h1>
        <div class="student-info">
            <p><strong>Name:</strong> <?= $studentName ?></p>
            <p><strong>IEN Number:</strong> <?= $ien_number ?></p>
            <p><strong>Department:</strong> <?= $department ?></p>
            <p><strong>Semester:</strong> <?= $semester ?></p>
            <p><strong>Batch:</strong> <?= $batch ?></p>
        </div>

        <h2>Results</h2>
        <table>
            <tr>
                <th>Subject</th>
                <th>TW Marks</th>
                <th>PR Marks</th>
                <th>ESE Marks</th>
                <th>Total</th>
            </tr>
            <?php $total = 0; ?>
            <?php foreach ($results as $result): ?>
                <tr>
                    <td><?= $result['subject'] ?></td>
                    <td><?= $result['TW_marks'] ?></td>
                    <td><?= $result['PR_marks'] ?></td>
                    <td><?= $result['ESE_marks'] ?></td>
                    <td><?= $result['TW_marks'] + $result['PR_marks'] + $result['ESE_marks'] ?></td>
                </tr>
                <?php $total += $result['TW_marks'] + $result['PR_marks'] + $result['ESE_marks']; ?>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="4">Total</td>
                <td><?= $total ?></td>
            </tr>
        </table>
    </div>
</body>

</html>