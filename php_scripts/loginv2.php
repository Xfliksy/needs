<?php
function getDatabaseConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $database = "rza_db";
    $port = 8889; // MySQL port in MAMP

    $conn = new mysqli($servername, $username, $password, $database, $port);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function authenticateUser($conn, $username, $password) {
    $sql = "SELECT user_id, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed: " . $conn->error;
        return false;
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        return false; // User not found
    }

    $row = $result->fetch_assoc();
    if (!verifyPassword($password, $row['password'])) {
        return false; // Password does not match
    }

    return $row['user_id']; // Return user ID on successful authentication
}


function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}


function user_check($userId) {
    if (!$userId) {
        echo "Invalid username or password<br>";
        return;
    }

    $_SESSION['user_id'] = $userId;
    header("Location: dashboard.php");
    exit();
}


session_start();
// Get the database connection
$conn = getDatabaseConnection();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $conn->real_escape_string($_POST["username"]);
    $password = $conn->real_escape_string($_POST["password"]);

    $userId = authenticateUser($conn, $username, $password);

   user_check($userId);
}


$conn->close();


?>
