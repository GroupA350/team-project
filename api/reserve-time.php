<?php
session_start();
$mysqli = new mysqli('localhost', 'root', '', 'laundry_machine_schedule');

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


// Has the user already reserved a time this week?
$statement = $mysqli->prepare("
    SELECT 
        *
    FROM
        reservations
    WHERE
        user_id = (SELECT id FROM users WHERE email = ?)
    AND (YEARWEEK(start) = YEARWEEK(?) OR YEARWEEK(end) = YEARWEEK(?))
");


if (!$statement->execute([$_SESSION["currentUserEmail"], $data->start, $data->end])) {
    http_response_code(500);
    echo json_encode([
        "message" => "Something went wrong."
    ]);
    exit();
}

$rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);

if (count($rows) > 0) {
    http_response_code(400);
    echo json_encode([
        "message" => "You already have a reservation this week.",
        "reservation" => json_encode($rows[0])
    ]);
    exit();
}


if (empty($_SESSION["currentUserEmail"])) {
    http_response_code(403);
    echo json_encode([
        "message" => "Login to reserve a time."
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
