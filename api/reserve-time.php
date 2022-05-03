<?php
session_start();
$mysqli = new mysqli('localhost', 'root', '', 'laundry_machine_schedule');

// Reading the "body" (data) of the request.
// start
// end
// $_SESSION["currentUserEmail"]
$data = json_decode(file_get_contents("php://input"));


// Is the user not logged in?
if (empty($_SESSION["currentUserEmail"])) {
    http_response_code(403);
    echo json_encode([
        "message" => "Login to reserve a time."
    ]);
    exit();
}

// Has the user already reserved a time this week or in the future?
$statement = $mysqli->prepare("
    SELECT 
        *
    FROM
        reservations
    WHERE
        user_id = (SELECT id FROM users WHERE email = ?)
    AND start >= DATE_ADD(CURDATE(), INTERVAL(1-DAYOFWEEK(CURDATE())) DAY)
");


if (!$statement->execute([$_SESSION["currentUserEmail"]])) {
    http_response_code(500);
    echo json_encode([
        "message" => "Something went wrong."
    ]);
    exit();
}

// Stores the query results as an array of associative arrays.
$rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);

if (count($rows) > 0) {
    http_response_code(402);
    echo json_encode([
        "message" => "You already have a reservation on the schedule.",
        "reservation" => $rows[0]
    ]);
    exit();
}


// TODO: Reserve a time
$statement = $mysqli->prepare("
    INSERT INTO
        reservations(start, end, user_id)
    VALUES(?, ?, (SELECT id FROM users WHERE email = ?))
");

if (!$statement->execute([$data->start, $data->end, $_SESSION["currentUserEmail"]])) {
    http_response_code(500);
    echo json_encode([
        "message" => "Something went wrong."
    ]);
    exit();
}

http_response_code(200);
echo json_encode([
    "message" => "Time reserved successfully."
]);
exit();
