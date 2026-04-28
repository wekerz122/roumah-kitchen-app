<?php
include "../config/db.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$orderCode = mysqli_real_escape_string($conn, trim($data["order_code"] ?? ""));
$customerPhone = mysqli_real_escape_string($conn, trim($data["customer_phone"] ?? ""));

if ($orderCode === "" || $customerPhone === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Kode order dan nomor WhatsApp wajib diisi."
    ]);
    exit;
}

$query = mysqli_query($conn, "
    SELECT * FROM orders
    WHERE order_code = '$orderCode'
    AND customer_phone = '$customerPhone'
    LIMIT 1
");

if (mysqli_num_rows($query) === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Pesanan tidak ditemukan. Periksa kembali kode order dan nomor WhatsApp."
    ]);
    exit;
}

$order = mysqli_fetch_assoc($query);
$orderId = (int)$order["id"];

$itemQuery = mysqli_query($conn, "
    SELECT name, price, qty, spicy_level, toppings
    FROM order_items
    WHERE order_id = $orderId
");

$items = [];
while ($item = mysqli_fetch_assoc($itemQuery)) {
    $decodedToppings = json_decode($item["toppings"] ?? "[]", true);
    $item["toppings"] = is_array($decodedToppings) ? $decodedToppings : [];
    $items[] = $item;
}

$order["items"] = $items;

echo json_encode([
    "status" => "success",
    "order" => $order
]);
?>