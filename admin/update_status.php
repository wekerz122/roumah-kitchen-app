<?php
include "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)($_POST["id"] ?? 0);
    $status = $_POST["status"] ?? "";

    $allowed = ["pending", "diproses", "selesai"];

    if ($id > 0 && in_array($status, $allowed, true)) {
        $status = mysqli_real_escape_string($conn, $status);
        mysqli_query($conn, "UPDATE orders SET status = '$status' WHERE id = $id");
    }
}

header("Location: orders.php");
exit;