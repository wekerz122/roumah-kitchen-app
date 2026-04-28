<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)($_POST["id"] ?? 0);
    $status = trim($_POST["status"] ?? "");

    $allowed = ["pending", "diproses", "selesai"];

    if ($id > 0 && in_array($status, $allowed, true)) {
        $statusEscaped = mysqli_real_escape_string($conn, $status);

        mysqli_query($conn, "
            UPDATE orders
            SET status = '$statusEscaped'
            WHERE id = $id
        ");
    }
}

header("Location: orders.php");
exit;