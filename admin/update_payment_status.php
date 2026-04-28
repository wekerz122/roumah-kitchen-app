<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)($_POST["id"] ?? 0);
    $paymentStatus = trim($_POST["payment_status"] ?? "");

    $allowed = ["belum_bayar", "menunggu_pembayaran", "menunggu_verifikasi", "paid"];

    if ($id > 0 && in_array($paymentStatus, $allowed, true)) {
        $paymentStatusEscaped = mysqli_real_escape_string($conn, $paymentStatus);

        mysqli_query($conn, "
            UPDATE orders 
            SET payment_status = '$paymentStatusEscaped'
            WHERE id = $id
        ");

        if ($paymentStatus === "paid") {
            mysqli_query($conn, "
                UPDATE orders
                SET status = 'diproses'
                WHERE id = $id AND status = 'pending'
            ");
        }

        if ($paymentStatus === "belum_bayar") {
            mysqli_query($conn, "
                UPDATE orders
                SET status = 'pending'
                WHERE id = $id AND status = 'diproses'
            ");
        }
    }
}

header("Location: orders.php");
exit;