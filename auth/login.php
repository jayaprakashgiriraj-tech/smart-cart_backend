
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
$password = $data["password"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Valid email and password are required"
    ]);
    exit;
}

try {
    $sql = "SELECT id, name, email, password, created_at
            FROM users
            WHERE email = :email
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute([":email" => $email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user["password"])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password"
        ]);
        exit;
    }

    unset($user["password"]);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Login successful",
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
