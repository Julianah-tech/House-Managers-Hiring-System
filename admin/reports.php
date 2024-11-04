<?php
$host = 'localhost';
$db = 'recruitment';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<?php
function fetchCandidates($conn, $status = null) {
    $query = "SELECT * FROM candidates";
    if ($status) {
        $query .= " WHERE status = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $status);
    } else {
        $stmt = $conn->prepare($query);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>
<?php
function generateHTMLReport($candidates) {
    $html = "<h2>Candidates Report</h2>";
    $html .= "<table border='1' cellpadding='5'>";
    $html .= "<tr><th>ID</th><th>Name</th><th>Email</th><th>Applied Position</th><th>Application Date</th><th>Status</th></tr>";
    
    foreach ($candidates as $candidate) {
        $html .= "<tr>
                    <td>{$candidate['id']}</td>
                    <td>{$candidate['name']}</td>
                    <td>{$candidate['email']}</td>
                    <td>{$candidate['applied_position']}</td>
                    <td>{$candidate['application_date']}</td>
                    <td>{$candidate['status']}</td>
                  </tr>";
    }
    $html .= "</table>";
    
    return $html;
}
?>
<?php
function generateCSVReport($candidates) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=candidates_report.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Email', 'Applied Position', 'Application Date', 'Status']);

    foreach ($candidates as $candidate) {
        fputcsv($output, $candidate);
    }

    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
</head>
<body>

<h1>Generate Candidate Reports</h1>
<form method="POST">
    <label for="reportType">Select Report Type:</label>
    <select name="reportType" id="reportType">
        <option value="html">HTML</option>
        <option value="csv">CSV</option>
    </select>
    <button type="submit">Generate Report</button>
</form>

<?php
// Include the database connection and functions
include 'db_connection.php';  // Your database connection file
include 'functions.php';  // Your functions file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $candidates = fetchCandidates($conn);

    if ($_POST['reportType'] == 'html') {
        echo generateHTMLReport($candidates);
    } elseif ($_POST['reportType'] == 'csv') {
        generateCSVReport($candidates);
    }
}
?>

</body>
</html>
