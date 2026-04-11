<?php
include "../config/db.php";

$query = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders - Roumah Kitchen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
        }

        h1 {
            margin-bottom: 20px;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        }

        .order-head {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .order-code {
            font-weight: bold;
            color: #ea580c;
        }

        .meta {
            color: #475569;
            font-size: 14px;
            line-height: 1.6;
        }

        .items {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .empty {
            background: #fff;
            padding: 20px;
            border-radius: 14px;
        }
    </style>
</head>
<body>

    <h1>Daftar Order Masuk</h1>

    <?php if (mysqli_num_rows($query) > 0): ?>
        <?php while ($order = mysqli_fetch_assoc($query)): ?>
            <div class="card">
                <div class="order-head">
                    <div class="order-code"><?php echo htmlspecialchars($order['order_code']); ?></div>
                    <div><?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?></div>
                </div>

                <div class="meta">
                    <div><strong>Nama:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></div>
                    <div>
                        <strong>WhatsApp:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?><br>

                        <?php
                        $phone = preg_replace('/[^0-9]/', '', $order['customer_phone']);
                        if (substr($phone, 0, 1) === '0') {
                            $phone = '62' . substr($phone, 1);
                        }

                        $message = "Halo " . $order['customer_name'] . ", pesanan Anda sedang diproses oleh Roumah Kitchen 🙏";
                        $wa_link = "https://wa.me/" . $phone . "?text=" . urlencode($message);
                        ?>

                        <a href="<?php echo $wa_link; ?>" target="_blank" style="
                            display:inline-block;
                            margin-top:6px;
                            padding:6px 12px;
                            background:#25D366;
                            color:white;
                            border-radius:8px;
                            text-decoration:none;
                            font-size:13px;
                            ">
                            Chat WhatsApp
                        </a>
                    </div>
                    <div><strong>Tipe:</strong> <?php echo htmlspecialchars($order['order_type']); ?></div>
                    <div><strong>Pembayaran:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></div>
                    <div><strong>Status:</strong><br>
                     <form action="update_status.php" method="POST" style="display:inline-block; margin-top:8px;">
                         <input type="hidden" name="id" value="<?php echo (int)$order['id']; ?>">
                         <select name="status">
                             <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>pending</option>
                             <option value="diproses" <?php echo $order['status'] === 'diproses' ? 'selected' : ''; ?>>diproses</option>
                             <option value="selesai" <?php echo $order['status'] === 'selesai' ? 'selected' : ''; ?>>selesai</option>
                         </select>
                         <button type="submit">Simpan</button>
                     </form>
                    </div>
                    <div><strong>Total:</strong> Rp <?php echo number_format($order['total'], 0, ',', '.'); ?></div>
                    <?php if (!empty($order['address'])): ?>
                        <div><strong>Alamat:</strong> <?php echo htmlspecialchars($order['address']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($order['note'])): ?>
                        <div><strong>Catatan:</strong> <?php echo htmlspecialchars($order['note']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="items">
                    <strong>Item Pesanan:</strong>
                    <?php
                    $orderId = (int)$order['id'];
                    $items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = $orderId");
                    while ($item = mysqli_fetch_assoc($items)):
                    ?>
                        <div class="item-row">
                            <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo (int)$item['qty']; ?></span>
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