
<?php
header("Content-Type: application/json; charset=UTF-8");

mysqli_report(MYSQLI_REPORT_OFF);

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

// Read email from Postman form-data
$email = trim($_POST["email"] ?? "");

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Valid email is required"
    ]);
    exit;
}

// Match the field name used in Postman: profile_image
if (!isset($_FILES["profile_image"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Select a profile image"
    ]);
    exit;
}

$file = $_FILES["profile_image"];

if ($file["error"] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "File upload failed"
    ]);
    exit;
}

// Maximum image size: 5 MB
if ($file["size"] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Maximum file size is 5 MB"
    ]);
    exit;
}

// Validate actual image content
$imageInfo = getimagesize($file["tmp_name"]);

$allowedTypes = [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
    "image/webp" => "webp"
];

$mime = $imageInfo["mime"] ?? "";

if (!$imageInfo || !isset($allowedTypes[$mime])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Only JPG, PNG and WEBP images are allowed"
    ]);
    exit;
}

// Check whether user exists
$check = $conn->prepare(
    "SELECT email FROM users WHERE email = ? LIMIT 1"
);
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    $check->close();
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);
    exit;
}
$check->close();

// Create uploads directory
$uploadDir = dirname(__DIR__) . "/uploads/";

if (!is_dir($uploadDir) &&
    !mkdir($uploadDir, 0755, true) &&
    !is_dir($uploadDir)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Could not create uploads directory"
    ]);
    exit;
}

// Generate unique filename
$filename = bin2hex(random_bytes(16))
          . "." . $allowedTypes[$mime];

$destination = $uploadDir . $filename;
$photoPath = "uploads/" . $filename;

// Save image
if (!move_uploaded_file($file["tmp_name"], $destination)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Could not save image"
    ]);
    exit;
}

// Update profile_photo in database
$stmt = $conn->prepare(
    "UPDATE users SET profile_photo = ? WHERE email = ?"
);

if (!$stmt) {
    unlink($destination);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database query failed"
    ]);
    exit;
}

$stmt->bind_param("ss", $photoPath, $email);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Profile photo uploaded successfully",
        "email" => $email,
        "profile_photo" => $photoPath,
        "photo_url" =>
            "http://localhost/smartcart_backend/" . $photoPath
    ]);
} else {
    unlink($destination);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to update profile photo"
    ]);
}

$stmt->close();
$conn->close();
?>
