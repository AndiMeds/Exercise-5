<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "validation";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]));
}

// Get form data
$first_name = $_POST['firstName'];
$last_name = $_POST['lastName'];
$user_username = $_POST['username'];
$user_email = $_POST['email'];
$user_password = $_POST['password'];

// Validate if email or username already exists
$sql_check = "SELECT * FROM infos WHERE email = ? OR username = ?";
$stmt_check = $conn->prepare($sql_check);

if ($stmt_check) {
    $stmt_check->bind_param("ss", $user_email, $user_username);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    // Check if the email or username already exists
    if ($result_check->num_rows > 0) {
        $row = $result_check->fetch_assoc();
        
        if ($row['email'] === $user_email) {
            echo json_encode(["success" => false, "error" => "Email is already registered."]);
        } elseif ($row['username'] === $user_username) {
            echo json_encode(["success" => false, "error" => "Username is already taken."]);
        }
        
        $stmt_check->close();
        $conn->close();
        exit(); // Exit to prevent further execution
    }

    $stmt_check->close();
} else {
    echo json_encode(["success" => false, "error" => "Database query error."]);
    $conn->close();
    exit();
}

// Insert into database if email and username are not registered
$sql = "INSERT INTO infos (first_name, last_name, username, email, password) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("sssss", $first_name, $last_name, $user_username, $user_email, $user_password);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to register."]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "Database query error."]);
}

$conn->close();
?>
