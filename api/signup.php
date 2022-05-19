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

// Do the passwords mismatch?
if ($data->password != $data->passwordRepeat) {
    http_response_code(400);
    echo json_encode([
        "message" => "Passwords must match."
    ]);
    exit();
}

// Is the apartment number invalid?
if (strlen($data->apartmentNumber) < 2) {
    http_response_code(400);
    echo json_encode([
        "message" => "Invalid apartment number."
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

$rows = $statement->get_result()->fetch_all();

if (count($rows) > 0) {
    http_response_code(400);
    echo json_encode([
        "message" => "Email is already in use."
    ]);
    exit();
}


// Is the apartment number already in use?
$statement = $mysqli->prepare("
    SELECT 
        *
    FROM
        users
    WHERE
        apartment_number = ?
");

if (!$statement->execute([$data->apartmentNumber])) {
    http_response_code(500);
    echo json_encode([
        "message" => "Something went wrong."
    ]);
    exit();
}

$rows = $statement->get_result()->fetch_all();

if (count($rows) > 0) {
    http_response_code(400);
    echo json_encode([
        "message" => "Apartment Number is already registered."
    ]);
    exit();
}

// TODO:
// 1. Hash the password
//    NOTE: You should NEVER store raw passwords in the database. 
//          Instead, you should always make a "hash" of the password 
//          and store the hash in the database.

//                                      strongest hashing algorithm
//                                                    v
$passwordHash = password_hash($data->password, PASSWORD_DEFAULT);

// 2. Insert the user record

$statement = $mysqli->prepare("
    INSERT INTO
        users(email, password_hash, apartment_number)
    VALUES(?, ?, ?)
");

if (!$statement->execute([$data->email, $passwordHash, $data->apartmentNumber])) {
    http_response_code(500);
    echo json_encode([
        "message" => "Something went wrong."
    ]);
    exit();
}

// 3. Automatically log the user in

$_SESSION["currentUserEmail"] = $data->email;

http_response_code(200);
echo json_encode([
    "message" => "Signup successful!"
]);
exit();
