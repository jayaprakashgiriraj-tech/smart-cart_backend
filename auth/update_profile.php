<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) {
    $data = $_POST;
}

$email = trim($data["email"] ?? "");
$name = trim($data["name"] ?? "");

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Valid email and name are required"
    ]);
    exit;
}

try {
    // Check if user exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $checkStmt->execute([":email" => $email]);
    $userExists = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$userExists) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "User not found"
        ]);
        exit;
    }

    $sql = "UPDATE users SET name = :name WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ":name" => $name,
        ":email" => $email
    ]);

    $fetchStmt = $conn->prepare("SELECT id, name, email, profile_photo, created_at FROM users WHERE email = :email LIMIT 1");
    $fetchStmt->execute([":email" => $email]);
    $user = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "message" => "Profile updated successfully",
        "user" => $user
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Something went wrong"
    ]);
}
?>
