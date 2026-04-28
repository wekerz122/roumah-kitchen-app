<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

$query = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders - Roumah Kitchen</title>
    <link rel="stylesheet" href="../roumahstyle.css">
</head>
<body>

    <div class="header-bar">
        <div class="header-left">
            <a href="dashboard.php" class="back-btn">← Dashboard</a>
            <h1 class="page-title">Daftar Order Masuk</h1>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <?php if (mysqli_num_rows($query) > 0): ?>
        <?php while ($order = mysqli_fetch_assoc($query)): ?>
            <?php
            $phone = preg_replace('/[^0-9]/', '', $order['customer_phone']);
            if (substr($phone, 0, 1) === '0') {
                $phone = '62' . substr($phone, 1);
            }

            $message = "Halo " . $order['customer_name'] . ", pesanan Anda sedang diproses oleh Roumah Kitchen 🙏";
            $wa_link = "https://wa.me/" . $phone . "?text=" . urlencode($message);
            ?>

            <div class="card">
                <div class="order-layout">

                    <!-- KIRI -->
                    <div class="order-left">
                        <div class="order-code"><?php echo htmlspecialchars($order['order_code']); ?></div>

                        <div class="meta-block"><strong>Nama:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></div>
                        <div class="meta-block"><strong>WhatsApp:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></div>
                        <div class="meta-block"><strong>Tipe:</strong> <?php echo htmlspecialchars($order['order_type']); ?></div>
                        <div class="meta-block"><strong>Pembayaran:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></div>
                        <div class="meta-block"><strong>Status Pembayaran:</strong> <?php echo htmlspecialchars($order['payment_status'] ?? '-'); ?></div>

                        <?php if (!empty($order['address'])): ?>
                            <div class="meta-block"><strong>Alamat:</strong> <?php echo htmlspecialchars($order['address']); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($order['note'])): ?>
                            <div class="meta-block"><strong>Catatan:</strong> <?php echo htmlspecialchars($order['note']); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($order['payment_proof'])): ?>
                            <div class="meta-block">
                                <strong>Bukti Bayar:</strong><br>
                                <a href="../<?php echo htmlspecialchars($order['payment_proof']); ?>" target="_blank">
                                    <img src="../<?php echo htmlspecialchars($order['payment_proof']); ?>" alt="Bukti Pembayaran" class="payment-proof-preview">
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- KANAN -->
                    <div class="order-right">
                        <div class="order-date">
                            <?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?>
                        </div>

                        <div class="status-box">
                            <div class="status-label"><strong>Status Order:</strong></div>
                            <form action="update_status.php" method="POST" class="status-form">
                                <input type="hidden" name="id" value="<?php echo (int)$order['id']; ?>">
                                <select name="status" class="status-select">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>pending</option>
                                    <option value="diproses" <?php echo $order['status'] === 'diproses' ? 'selected' : ''; ?>>diproses</option>
                                    <option value="selesai" <?php echo $order['status'] === 'selesai' ? 'selected' : ''; ?>>selesai</option>
                                </select>
                                <button type="submit" class="save-btn">Simpan</button>
                            </form>
                        </div>

                        <div class="status-box">
                            <div class="status-label"><strong>Status Pembayaran:</strong></div>

                            <div class="payment-badge payment-<?php echo htmlspecialchars($order['payment_status'] ?? 'belum_bayar'); ?>">
                                <?php echo htmlspecialchars($order['payment_status'] ?? 'belum_bayar'); ?>
                            </div>

                            <form action="update_payment_status.php" method="POST" class="payment-action-form">
                             <input type="hidden" name="id" value="<?php echo (int)$order['id']; ?>">
                             <input type="hidden" name="payment_status" value="paid">
                             <button type="submit" class="approve-btn">ACC Pembayaran</button>
                            </form>

                            <form action="update_payment_status.php" method="POST" class="payment-action-form">
                                <input type="hidden" name="id" value="<?php echo (int)$order['id']; ?>">
                                <input type="hidden" name="payment_status" value="belum_bayar">
                                <button type="submit" class="reject-btn">Tolak</button>
                            </form>
                        </div>

                        <a href="<?php echo $wa_link; ?>" target="_blank" class="wa-btn">Chat WhatsApp</a>
                    </div>
                </div>

                <div class="order-footer">
                    <div class="total-text">
                        <strong>Total:</strong> Rp <?php echo number_format($order['total'], 0, ',', '.'); ?>
                    </div>

                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <a href="print_order.php?id=<?php echo (int)$order['id']; ?>" target="_blank" class="print-btn">
                            Print
                        </a>
                    </div>
                        </div>

                <div class="items">
                    <strong>Item Pesanan:</strong>
                    <?php
                    $orderId = (int)$order['id'];
                    $items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = $orderId");
                    while ($item = mysqli_fetch_assoc($items)):
                    ?>
                        <div class="item-row">
                            <div>
                                <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo (int)$item['qty']; ?></span>

                                <?php if (!empty($item['spicy_level'])): ?>
                                    <div style="font-size:12px;color:#666;">
                                        🌶 <?php echo htmlspecialchars($item['spicy_level']); ?>
                                    </div>
                                <?php endif; ?>

                                <?php 
                                $toppings = json_decode($item['toppings'] ?? '[]', true);
                                if (!empty($toppings)):
                                    foreach ($toppings as $t):
                                ?>
                                    <div style="font-size:12px;color:#666;">
                                        + <?php echo htmlspecialchars($t['name']); ?>
                                        <?php if ((int)$t['price'] > 0): ?>
                                            (Rp <?php echo number_format($t['price'], 0, ',', '.'); ?>)
                                        <?php endif; ?>
                                    </div>
                                <?php 
                                    endforeach;
                                endif;
                                ?>
                            </div>

                                <span>Rp <?php echo number_format($item['price'] * $item['qty'], 0, ',', '.'); ?></span>
                            </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty">Belum ada order masuk.</div>
    <?php endif; ?>

</body>
</html>