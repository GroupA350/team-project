<?php
session_start();
$mysqli = new mysqli('localhost', 'root', '', 'laundry_machine_schedule');

$statement = $mysqli->prepare("
    SELECT 
        id,
        email,
        apartment_number
    FROM
        users
    WHERE
        email = ?
");

if (!$statement->execute([$_SESSION["currentUserEmail"]])) {
    http_response_code(500);
    echo json_encode([
        "message" => "Something went wrong."
    ]);
    exit();
}

$rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);

if (count($rows) == 0) {
    http_response_code(200);
    echo json_encode(null);
    exit();
}

http_response_code(200);
// The database data converted to a JSON string
echo json_encode($rows[0]);
exit();
