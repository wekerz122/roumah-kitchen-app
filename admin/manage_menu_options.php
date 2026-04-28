<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

$message = "";
$messageType = "success";

$selectedMenuId = (int)($_GET["menu_id"] ?? $_POST["menu_id"] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $menuId = (int)($_POST["menu_id"] ?? 0);
    $selectedToppings = $_POST["toppings"] ?? [];
    $selectedSpicyLevels = $_POST["spicy_levels"] ?? [];

    if ($menuId <= 0) {
        $message = "Pilih menu terlebih dahulu.";
        $messageType = "error";
    } else {
        mysqli_query($conn, "DELETE FROM menu_toppings WHERE menu_id = $menuId");
        mysqli_query($conn, "DELETE FROM menu_spicy_levels WHERE menu_id = $menuId");

        if (!empty($selectedToppings) && is_array($selectedToppings)) {
            foreach ($selectedToppings as $toppingId) {
                $toppingId = (int)$toppingId;
                if ($toppingId > 0) {
                    mysqli_query($conn, "INSERT IGNORE INTO menu_toppings (menu_id, topping_id) VALUES ($menuId, $toppingId)");
                }
            }
        }

        if (!empty($selectedSpicyLevels) && is_array($selectedSpicyLevels)) {
            foreach ($selectedSpicyLevels as $spicyId) {
                $spicyId = (int)$spicyId;
                if ($spicyId > 0) {
                    mysqli_query($conn, "INSERT IGNORE INTO menu_spicy_levels (menu_id, spicy_level_id) VALUES ($menuId, $spicyId)");
                }
            }
        }

        $message = "Opsi menu berhasil disimpan.";
        $messageType = "success";
        $selectedMenuId = $menuId;
    }
}

$menus = mysqli_query($conn, "SELECT id, name, category FROM menu ORDER BY name ASC");
$toppings = mysqli_query($conn, "SELECT id, name, price, is_active FROM toppings ORDER BY name ASC");
$spicyLevels = mysqli_query($conn, "SELECT id, name, sort_order, is_active FROM spicy_levels ORDER BY sort_order ASC, id ASC");

$selectedToppingIds = [];
$selectedSpicyIds = [];

if ($selectedMenuId > 0) {
    $resToppings = mysqli_query($conn, "SELECT topping_id FROM menu_toppings WHERE menu_id = $selectedMenuId");
    while ($row = mysqli_fetch_assoc($resToppings)) {
        $selectedToppingIds[] = (int)$row["topping_id"];
    }

    $resSpicy = mysqli_query($conn, "SELECT spicy_level_id FROM menu_spicy_levels WHERE menu_id = $selectedMenuId");
    while ($row = mysqli_fetch_assoc($resSpicy)) {
        $selectedSpicyIds[] = (int)$row["spicy_level_id"];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Opsi Menu - Roumah Kitchen</title>
    <link rel="stylesheet" href="../roumahstyle.css">
    <style>
        .manage-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }

        .card-box {
            background: #fff;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
        }

        .section-title-local {
            margin: 0 0 16px;
            font-size: 22px;
        }

        .alert-box {
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 600;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .option-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .option-list {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px;
            background: #f8fafc;
        }

        .option-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .option-item:last-child {
            border-bottom: none;
        }

        .option-meta {
            font-size: 13px;
            color: #6b7280;
            margin-top: 2px;
        }

        @media (max-width: 800px) {
            .option-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="manage-wrapper">
    <div class="header-bar">
        <div class="header-left">
            <a href="dashboard.php" class="back-btn">← Dashboard</a>
            <h1 class="page-title">Atur Opsi Menu</h1>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <?php if ($message !== ""): ?>
        <div class="alert-box <?php echo $messageType === "success" ? "alert-success" : "alert-error"; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card-box">
        <h2 class="section-title-local">Pilih Menu</h2>
        <form method="GET">
            <div class="field">
                <label for="menu_id">Menu</label>
                <select name="menu_id" id="menu_id" onchange="this.form.submit()" required>
                    <option value="">-- Pilih Menu --</option>
                    <?php while ($menu = mysqli_fetch_assoc($menus)): ?>
                        <option value="<?php echo (int)$menu["id"]; ?>" <?php echo $selectedMenuId === (int)$menu["id"] ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($menu["name"]); ?> (<?php echo htmlspecialchars($menu["category"]); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>
    </div>

    <?php if ($selectedMenuId > 0): ?>
        <div class="card-box">
            <h2 class="section-title-local">Atur Topping & Level Pedas</h2>

            <form method="POST">
                <input type="hidden" name="menu_id" value="<?php echo $selectedMenuId; ?>">

                <div class="option-grid">
                    <div>
                        <label>Topping untuk menu ini</label>
                        <div class="option-list">
                            <?php if (mysqli_num_rows($toppings) > 0): ?>
                                <?php while ($topping = mysqli_fetch_assoc($toppings)): ?>
                                    <label class="option-item">
                                        <input
                                            type="checkbox"
                                            name="toppings[]"
                                            value="<?php echo (int)$topping["id"]; ?>"
                                            <?php echo in_array((int)$topping["id"], $selectedToppingIds, true) ? "checked" : ""; ?>
                                        >
                                        <div>
                                            <div><?php echo htmlspecialchars($topping["name"]); ?></div>
                                            <div class="option-meta">
                                                Rp <?php echo number_format((int)$topping["price"], 0, ',', '.'); ?> •
                                                <?php echo (int)$topping["is_active"] === 1 ? "Aktif" : "Nonaktif"; ?>
                                            </div>
                                        </div>
                                    </label>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty">Belum ada data topping.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <label>Level pedas untuk menu ini</label>
                        <div class="option-list">
                            <?php if (mysqli_num_rows($spicyLevels) > 0): ?>
                                <?php while ($level = mysqli_fetch_assoc($spicyLevels)): ?>
                                    <label class="option-item">
                                        <input
                                            type="checkbox"
                                            name="spicy_levels[]"
                                            value="<?php echo (int)$level["id"]; ?>"
                                            <?php echo in_array((int)$level["id"], $selectedSpicyIds, true) ? "checked" : ""; ?>
                                        >
                                        <div>
                                            <div><?php echo htmlspecialchars($level["name"]); ?></div>
                                            <div class="option-meta">
                                                Urutan: <?php echo (int)$level["sort_order"]; ?> •
                                                <?php echo (int)$level["is_active"] === 1 ? "Aktif" : "Nonaktif"; ?>
                                            </div>
                                        </div>
                                    </label>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty">Belum ada data level pedas.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <button type="submit" class="primary-btn">Simpan Opsi Menu</button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>