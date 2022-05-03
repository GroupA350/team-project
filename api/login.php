<?php
session_start();
$mysqli = new mysqli('localhost', 'root', '', 'laundry_machine_schedule');

//     JSON -> object
$data = json_decode(file_get_contents("php://input"));

// Input validation:

// Is the email invalid?
if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "message" => "Invalid email address."
    ]);
    exit();
}

// Is the password invalid?
if (strlen($data->password) < 8) {
    http_response_code(400);
    echo json_encode([
        "message" => "Password must be 8+ characters."
    ]);
    exit();
}

// Is the email already in use?
$statement = $mysqli->prepare("
    SELECT 
        *
    FROM
        users
    WHERE
        email = ?
");

if (!$statement->execute([$data->email])) {
    http_response_code(500);
    echo json_encode([
        "message" => "Something went wrong."
    ]);
    exit();
}

$rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);

if (count($rows) == 0) {
    http_response_code(400);
    echo json_encode([
        "message" => "Email/password incorrect."
    ]);
    exit();
}

$user = $rows[0];

if (!password_verify($data->password, $user["password_hash"])) {
    http_response_code(400);
    echo json_encode([
        "message" => "Email/password incorrect."
    ]);
    exit();
}

// Log in
$_SESSION["currentUserEmail"] = $data->email;

http_response_code(200);
echo json_encode([
    "message" => "Login successful!"
]);
exit();
