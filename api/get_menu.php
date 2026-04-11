<?php
include "../config/db.php";

$query = mysqli_query($conn, "SELECT * FROM menu ORDER BY id ASC");

$data = [];

while ($row = mysqli_fetch_assoc($query)) {
    $data[] = $row;
}

header("Content-Type: application/json");
echo json_encode($data);
?>