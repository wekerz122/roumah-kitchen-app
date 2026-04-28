<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

/* statistik utama */
$totalOrdersQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders");
$totalOrders = mysqli_fetch_assoc($totalOrdersQuery)['total'] ?? 0;

$pendingQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$totalPending = mysqli_fetch_assoc($pendingQuery)['total'] ?? 0;

$diprosesQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'diproses'");
$totalDiproses = mysqli_fetch_assoc($diprosesQuery)['total'] ?? 0;

$selesaiQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'selesai'");
$totalSelesai = mysqli_fetch_assoc($selesaiQuery)['total'] ?? 0;

$omzetQuery = mysqli_query($conn, "SELECT SUM(total) as total_omzet FROM orders");
$totalOmzet = mysqli_fetch_assoc($omzetQuery)['total_omzet'] ?? 0;

/* order terbaru */
$recentOrders = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Roumah Kitchen</title>
    <link rel="stylesheet" href="../roumahstyle.css">
</head>
<body>

    <div class="header-bar">
        <h1 class="page-title">Dashboard Admin</h1>
        <div class="dashboard-actions">
            <a href="orders.php" class="nav-btn">Lihat Orders</a>
            <a href="manage_menu.php" class="nav-btn">Kelola Menu</a>
            <a href="manage_menu_options.php" class="nav-btn">Atur Opsi Menu</a>
            <a href="manage_toppings.php" class="nav-btn">Kelola Topping</a>
            <a href="manage_spicy_levels.php" class="nav-btn">Kelola Level Pedas</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="welcome-box">
        <h2>Halo, <?php echo htmlspecialchars($_SESSION["admin_username"]); ?> 👋</h2>
        <p>Selamat datang di dashboard Roumah Kitchen.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Order</div>
            <div class="stat-value"><?php echo number_format($totalOrders, 0, ',', '.'); ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Pending</div>
            <div class="stat-value"><?php echo number_format($totalPending, 0, ',', '.'); ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Diproses</div>
            <div class="stat-value"><?php echo number_format($totalDiproses, 0, ',', '.'); ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Selesai</div>
            <div class="stat-value"><?php echo number_format($totalSelesai, 0, ',', '.'); ?></div>
        </div>

        <div class="stat-card stat-card-wide">
            <div class="stat-label">Total Omzet</div>
            <div class="stat-value">Rp <?php echo number_format((int)$totalOmzet, 0, ',', '.'); ?></div>
        </div>
    </div>

    <div class="card">
        <div class="section-head">
            <h2 class="section-title">Order Terbaru</h2>
            <a href="orders.php" class="link-arrow">Lihat semua</a>
        </div>

        <?php if (mysqli_num_rows($recentOrders) > 0): ?>
            <div class="recent-list">
                <?php while ($order = mysqli_fetch_assoc($recentOrders)): ?>
                    <div class="recent-item">
                        <div>
                            <div class="recent-code"><?php echo htmlspecialchars($order['order_code']); ?></div>
                            <div class="recent-meta">
                                <?php echo htmlspecialchars($order['customer_name']); ?> •
                                <?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?>
                            </div>
                        </div>

                        <div class="recent-right">
                            <div class="recent-total">Rp <?php echo number_format((int)$order['total'], 0, ',', '.'); ?></div>
                            <div class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty">Belum ada order masuk.</div>
        <?php endif; ?>
    </div>

</body>
</html>