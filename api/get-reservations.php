<?php
session_start();
$mysqli = new mysqli('localhost', 'root', '', 'laundry_machine_schedule');

// Associative array -> object
$data = (object)$_GET;

// NOTE: The format of these dates MUST match the format that MySQL expects

// $data->rangeStart
// Example: "2022-05-01 00:00:00"

// $data->rangeEnd
// Example: "2022-05-01 23:59:59"

// TODO: Return all reservations in that range
$statement = $mysqli->prepare("
    SELECT 
        *
    FROM
        reservations
    WHERE
        start >= ? AND end >= ?
    AND start <= ? AND end <= ?
");

if (!$statement->execute([$data->rangeStart, $data->rangeStart, $data->rangeEnd, $data->rangeEnd])) {
    http_response_code(500);
    echo json_encode([
        "message" => "Something went wrong."
    ]);
    exit();
}

$rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);

http_response_code(200);
echo json_encode($rows);
exit();
