<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

$id = (int)($_GET['id'] ?? 0);

$order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id = $id"));

if (!$order) {
    echo "Order tidak ditemukan";
    exit;
}

$items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = $id");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Order</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Courier New", monospace;
            margin: 0;
            padding: 12px;
            background: #fff;
            color: #000;
        }

        .receipt {
            width: 58mm;
            max-width: 58mm;
            margin: 0 auto;
        }

        .center {
            text-align: center;
        }

        .title {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 11px;
            margin-bottom: 8px;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .meta {
            font-size: 11px;
            line-height: 1.5;
            word-break: break-word;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
            font-size: 11px;
            margin-bottom: 4px;
        }

        .item-name {
            flex: 1;
            word-break: break-word;
        }

        .item-price {
            white-space: nowrap;
            text-align: right;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: bold;
            margin-top: 4px;
        }

        .print-btn,
        .back-btn {
            display: block;
            width: 100%;
            border: none;
            padding: 10px;
            margin-top: 10px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
        }

        .print-btn {
            background: #000;
            color: #fff;
        }

        .back-btn {
            background: #e5e7eb;
            color: #111827;
        }

        @page {
            size: 58mm auto;
            margin: 4mm;
        }

        @media print {
            html, body {
                width: 58mm;
                background: #fff;
            }

            body {
                padding: 0;
                margin: 0;
            }

            .receipt {
                width: 100%;
                max-width: 100%;
                margin: 0;
            }

            .print-btn,
            .back-btn {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="center">
            <div class="title">
                Roumah Kitchen 
                x Coffeein Nusantara
            </div>
            <div class="subtitle">Struk Pesanan</div>
        </div>

        <div class="line"></div>

        <div class="meta">
            <div><strong>Kode:</strong> <?php echo htmlspecialchars($order['order_code']); ?></div>
            <div><strong>Tanggal:</strong> <?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?></div>
        </div>

        <div class="line"></div>

        <div class="meta">
            <div><strong>Nama:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></div>
            <div><strong>HP:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></div>
            <div><strong>Tipe:</strong> <?php echo htmlspecialchars($order['order_type']); ?></div>
            <div><strong>Pembayaran:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></div>
            <div><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></div>

            <?php if (!empty($order['address'])): ?>
                <div><strong>Alamat:</strong> <?php echo htmlspecialchars($order['address']); ?></div>
            <?php endif; ?>

            <?php if (!empty($order['note'])): ?>
                <div><strong>Catatan:</strong> <?php echo htmlspecialchars($order['note']); ?></div>
            <?php endif; ?>
        </div>

        <div class="line"></div>

        <?php while ($item = mysqli_fetch_assoc($items)): ?>
            <div class="item-row">
                <div class="item-name">
                    <?php echo htmlspecialchars($item['name']); ?> x<?php echo (int)$item['qty']; ?>

                    <?php if (!empty($item['spicy_level'])): ?>
                        <div style="font-size:10px;">
                            🌶 <?php echo htmlspecialchars($item['spicy_level']); ?>
                        </div>
                    <?php endif; ?>

                    <?php 
                    $toppings = json_decode($item['toppings'] ?? '[]', true);
                    if (!empty($toppings)):
                        foreach ($toppings as $t):
                    ?>
                        <div style="font-size:10px;">
                            + <?php echo htmlspecialchars($t['name']); ?>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
                <div class="item-price">
                    <?php echo number_format($item['price'] * $item['qty'], 0, ',', '.'); ?>
                </div>
            </div>
        <?php endwhile; ?>

        <div class="line"></div>

        <div class="total-row">
            <span>Total</span>
            <span>Rp <?php echo number_format($order['total'], 0, ',', '.'); ?></span>
        </div>

        <div class="line"></div>

        <div class="center subtitle">
            Terima kasih 🙏
        </div>
        <div class="center subtitle">
            www.coffeeinnusantara.com
        </div>

        <button class="print-btn" onclick="window.print()">Print Sekarang</button>
        <a href="orders.php" class="back-btn">Kembali ke Orders</a>
    </div>

    <script>
        window.onload = function () {
            window.print();
        };

        window.onafterprint = function () {
            window.location.href = "orders.php";
        };
    </script>
</body>
</html>