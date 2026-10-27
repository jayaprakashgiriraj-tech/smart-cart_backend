
<?php
header("Content-Type: application/json; charset=UTF-8");

// Database connection
$conn = new mysqli(
    "localhost",
    "root",
    "",
    "smart_cart"
);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

$conn->set_charset("utf8mb4");

// Get email from GET request
$email = trim($_GET["email"] ?? "");

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Valid email is required"
    ]);
    $conn->close();
    exit;
}

// Fetch profile details
$stmt = $conn->prepare(
    "SELECT name, email, profile_photo
     FROM users
     WHERE email = ?
     LIMIT 1"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {

    $photoPath = $user["profile_photo"];

    $photoUrl = null;

    if (!empty($photoPath)) {
        $photoUrl =
            "http://localhost/smartcart_backend/" .
            ltrim($photoPath, "/");
    }

    echo json_encode([
        "success" => true,
        "profile" => [
            "name" => $user["name"],
            "email" => $user["email"],
            "profile_photo" => $photoPath,
            "photo_url" => $photoUrl
        ]
    ]);

} else {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);
}

$stmt->close();
$conn->close();
?>
