
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

$name = trim($data["name"] ?? "");
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Name, valid email, and password are required"
    ]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO users (name, email, password)
            VALUES (:name, :email, :password)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ":name" => $name,
        ":email" => $email,
        ":password" => $hashedPassword
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Signup successful",
        "user" => [
            "id" => $conn->lastInsertId(),
            "name" => $name,
            "email" => $email
        ]
    ]);

} catch (PDOException $e) {
    if ($e->getCode() === "23000") {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Email already exists"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Something went wrong"
        ]);
    }
}
?>
