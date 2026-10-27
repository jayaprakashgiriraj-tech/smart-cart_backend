
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

$email = trim($data["email"] ?? "");
$newPassword = $data["new_password"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($newPassword) < 8) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Enter a valid email and a password of at least 8 characters"
    ]);
    exit;
}

try {
    $sql = "SELECT id FROM users WHERE email = :email LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([":email" => $email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "User not found"
        ]);
        exit;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $sql = "UPDATE users SET password = :password WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ":password" => $hashedPassword,
        ":id" => $user["id"]
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Password reset successfully"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Something went wrong"
    ]);
}
?>
