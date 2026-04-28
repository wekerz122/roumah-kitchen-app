<?php
include "../config/db.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "Data JSON tidak terbaca"
    ]);
    exit;
}

$code = mysqli_real_escape_string($conn, $data["code"] ?? "");
$name = mysqli_real_escape_string($conn, $data["name"] ?? "");
$phone = mysqli_real_escape_string($conn, $data["phone"] ?? "");
$orderType = mysqli_real_escape_string($conn, $data["orderType"] ?? "");
$address = mysqli_real_escape_string($conn, $data["address"] ?? "");
$note = mysqli_real_escape_string($conn, $data["note"] ?? "");
$payment = mysqli_real_escape_string($conn, $data["payment"] ?? "");
$total = (int)($data["total"] ?? 0);
$items = $data["items"] ?? [];

if ($name === "" || $phone === "" || $total <= 0 || empty($items)) {
    echo json_encode([
        "status" => "error",
        "message" => "Data pesanan belum lengkap"
    ]);
    exit;
}

$sqlOrder = "INSERT INTO orders 
(order_code, customer_name, customer_phone, order_type, address, note, payment_method, total)
VALUES
('$code', '$name', '$phone', '$orderType', '$address', '$note', '$payment', $total)";

if (!mysqli_query($conn, $sqlOrder)) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal simpan ke tabel orders: " . mysqli_error($conn)
    ]);
    exit;
}

$orderId = mysqli_insert_id($conn);

foreach ($items as $item) {
    $menuId = (int)($item["id"] ?? 0);
    $itemName = mysqli_real_escape_string($conn, $item["name"] ?? "");
    $price = (int)($item["price"] ?? 0);
    $qty = (int)($item["qty"] ?? 0);

    $spicyLevel = mysqli_real_escape_string($conn, $item["spicy_level"] ?? "");
    $toppingsJson = mysqli_real_escape_string(
        $conn,
        json_encode($item["toppings"] ?? [], JSON_UNESCAPED_UNICODE)
    );

    $sqlItem = "INSERT INTO order_items 
    (order_id, menu_id, name, price, qty, spicy_level, toppings)
    VALUES 
    ($orderId, $menuId, '$itemName', $price, $qty, '$spicyLevel', '$toppingsJson')";

    if (!mysqli_query($conn, $sqlItem)) {
        echo json_encode([
            "status" => "error",
            "message" => "Gagal simpan item pesanan: " . mysqli_error($conn)
        ]);
        exit;
    }
}

echo json_encode([
    "status" => "success",
    "message" => "Pesanan berhasil disimpan"
]);
?>