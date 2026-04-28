<?php
include "../config/db.php";

header("Content-Type: application/json");

// =========================
// Ambil input
// =========================
$orderCode = trim($_POST["order_code"] ?? "");
$customerPhone = trim($_POST["customer_phone"] ?? "");

// =========================
// Validasi input
// =========================
if ($orderCode === "" || $customerPhone === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Kode order dan nomor WhatsApp wajib diisi."
    ]);
    exit;
}

// =========================
// Validasi file
// =========================
if (!isset($_FILES["payment_proof"])) {
    echo json_encode([
        "status" => "error",
        "message" => "File bukti pembayaran tidak ditemukan."
    ]);
    exit;
}

$file = $_FILES["payment_proof"];

if ($file["error"] !== UPLOAD_ERR_OK) {
    echo json_encode([
        "status" => "error",
        "message" => "Upload file gagal."
    ]);
    exit;
}

// =========================
// Validasi ekstensi
// =========================
$allowedExtensions = ["jpg", "jpeg", "png", "webp"];
$originalName = $file["name"];
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExtensions, true)) {
    echo json_encode([
        "status" => "error",
        "message" => "Format file harus jpg, jpeg, png, atau webp."
    ]);
    exit;
}

// =========================
// Validasi ukuran (3MB)
// =========================
if ($file["size"] > 3 * 1024 * 1024) {
    echo json_encode([
        "status" => "error",
        "message" => "Ukuran file maksimal 3MB."
    ]);
    exit;
}

// =========================
// Cek order di database
// =========================
$orderCodeEscaped = mysqli_real_escape_string($conn, $orderCode);
$customerPhoneEscaped = mysqli_real_escape_string($conn, $customerPhone);

$orderQuery = mysqli_query($conn, "
    SELECT id FROM orders
    WHERE order_code = '$orderCodeEscaped'
    AND customer_phone = '$customerPhoneEscaped'
    LIMIT 1
");

if (mysqli_num_rows($orderQuery) === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Pesanan tidak ditemukan."
    ]);
    exit;
}

$order = mysqli_fetch_assoc($orderQuery);
$orderId = (int)$order["id"];

// =========================
// Folder upload
// =========================
$uploadDir = "../uploads/payment_proofs/";

// buat folder jika belum ada
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// =========================
// Generate nama file
// =========================
$newFileName = "proof_" . $orderId . "_" . time() . "." . $ext;
$targetPath = $uploadDir . $newFileName;

// =========================
// Simpan file
// =========================
if (!move_uploaded_file($file["tmp_name"], $targetPath)) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal menyimpan file ke server."
    ]);
    exit;
}

// path untuk database
$filePathForDb = "uploads/payment_proofs/" . $newFileName;

// =========================
// Update database
// =========================
$update = mysqli_query($conn, "
    UPDATE orders
    SET payment_proof = '$filePathForDb',
        payment_status = 'menunggu_verifikasi'
    WHERE id = $orderId
");

if (!$update) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal update database."
    ]);
    exit;
}

// =========================
// SUCCESS
// =========================
echo json_encode([
    "status" => "success",
    "message" => "Bukti pembayaran berhasil diupload. Menunggu verifikasi admin."
]);