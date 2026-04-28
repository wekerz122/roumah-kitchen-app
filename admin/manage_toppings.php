<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

function clean($conn, $value) {
    return mysqli_real_escape_string($conn, trim($value));
}

$message = "";
$messageType = "success";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $name = clean($conn, $_POST["name"] ?? "");
        $price = (int)($_POST["price"] ?? 0);
        $isActive = isset($_POST["is_active"]) ? 1 : 0;

        if ($name === "") {
            $message = "Nama topping wajib diisi.";
            $messageType = "error";
        } else {
            $sql = "INSERT INTO toppings (name, price, is_active, created_at)
                    VALUES ('$name', $price, $isActive, NOW())";

            if (mysqli_query($conn, $sql)) {
                $message = "Topping berhasil ditambahkan.";
                $messageType = "success";
            } else {
                $message = "Gagal menambah topping: " . mysqli_error($conn);
                $messageType = "error";
            }
        }
    }

    if ($action === "update") {
        $id = (int)($_POST["id"] ?? 0);
        $name = clean($conn, $_POST["name"] ?? "");
        $price = (int)($_POST["price"] ?? 0);
        $isActive = isset($_POST["is_active"]) ? 1 : 0;

        if ($id <= 0 || $name === "") {
            $message = "Data topping belum lengkap.";
            $messageType = "error";
        } else {
            $sql = "UPDATE toppings
                    SET name = '$name',
                        price = $price,
                        is_active = $isActive
                    WHERE id = $id
                    LIMIT 1";

            if (mysqli_query($conn, $sql)) {
                $message = "Topping berhasil diupdate.";
                $messageType = "success";
            } else {
                $message = "Gagal update topping: " . mysqli_error($conn);
                $messageType = "error";
            }
        }
    }

    if ($action === "delete") {
        $id = (int)($_POST["id"] ?? 0);

        if ($id > 0) {
            if (mysqli_query($conn, "DELETE FROM toppings WHERE id = $id LIMIT 1")) {
                $message = "Topping berhasil dihapus.";
                $messageType = "success";
            } else {
                $message = "Gagal hapus topping: " . mysqli_error($conn);
                $messageType = "error";
            }
        }
    }
}

$editData = null;
if (isset($_GET["edit"])) {
    $editId = (int)$_GET["edit"];
    if ($editId > 0) {
        $editQuery = mysqli_query($conn, "SELECT * FROM toppings WHERE id = $editId LIMIT 1");
        if ($editQuery && mysqli_num_rows($editQuery) > 0) {
            $editData = mysqli_fetch_assoc($editQuery);
        }
    }
}

$toppings = mysqli_query($conn, "SELECT * FROM toppings ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Topping - Roumah Kitchen</title>
    <link rel="stylesheet" href="../roumahstyle.css">
    <style>
        .manage-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }

        .manage-grid {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 20px;
        }

        .form-card, .table-card {
            background: #fff;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        }

        .manage-title {
            margin: 0 0 18px;
            font-size: 24px;
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

        .topping-table {
            width: 100%;
            border-collapse: collapse;
        }

        .topping-table th,
        .topping-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: top;
            font-size: 14px;
        }

        .mini-btn {
            display: inline-block;
            text-decoration: none;
            border: none;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            margin-right: 6px;
            margin-bottom: 6px;
        }

        .edit-btn {
            background: #2563eb;
            color: #fff;
        }

        .delete-btn {
            background: #dc2626;
            color: #fff;
        }

        .status-on,
        .status-off {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
        }

        .status-on {
            background: #16a34a;
        }

        .status-off {
            background: #6b7280;
        }

        .action-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .cancel-btn {
            background: #e5e7eb;
            color: #111827;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 8px;
        }

        @media (max-width: 900px) {
            .manage-grid {
                grid-template-columns: 1fr;
            }

            .topping-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>

<div class="manage-wrapper">
    <div class="header-bar">
        <div class="header-left">
            <a href="dashboard.php" class="back-btn">← Dashboard</a>
            <h1 class="page-title">Kelola Topping</h1>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <?php if ($message !== ""): ?>
        <div class="alert-box <?php echo $messageType === "success" ? "alert-success" : "alert-error"; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="manage-grid">
        <div class="form-card">
            <h2 class="manage-title">
                <?php echo $editData ? "Edit Topping" : "Tambah Topping"; ?>
            </h2>

            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $editData ? "update" : "add"; ?>">

                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$editData["id"]; ?>">
                <?php endif; ?>

                <div class="field">
                    <label for="name">Nama Topping</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        required
                        value="<?php echo htmlspecialchars($editData["name"] ?? ""); ?>"
                        placeholder="Contoh: Extra Ayam"
                    >
                </div>

                <div class="field">
                    <label for="price">Harga Tambahan</label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0"
                        required
                        value="<?php echo htmlspecialchars($editData["price"] ?? "0"); ?>"
                        placeholder="Contoh: 5000"
                    >
                </div>

                <div class="field">
                    <label>Status</label>
                    <div class="checkbox-row">
                        <input
                            type="checkbox"
                            id="is_active"
                            name="is_active"
                            <?php echo (!isset($editData) || (int)($editData["is_active"] ?? 1) === 1) ? "checked" : ""; ?>
                        >
                        <label for="is_active" style="margin:0;">Aktif</label>
                    </div>
                </div>

                <div class="action-row">
                    <button type="submit" class="primary-btn" style="margin-top:0;">
                        <?php echo $editData ? "Update Topping" : "Tambah Topping"; ?>
                    </button>

                    <?php if ($editData): ?>
                        <a href="manage_toppings.php" class="secondary-btn cancel-btn" style="margin-top:0;">
                            Batal Edit
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-card">
            <h2 class="manage-title">Daftar Topping</h2>

            <?php if ($toppings && mysqli_num_rows($toppings) > 0): ?>
                <table class="topping-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($topping = mysqli_fetch_assoc($toppings)): ?>
                            <tr>
                                <td><?php echo (int)$topping["id"]; ?></td>
                                <td><?php echo htmlspecialchars($topping["name"]); ?></td>
                                <td>Rp <?php echo number_format((int)$topping["price"], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ((int)$topping["is_active"] === 1): ?>
                                        <span class="status-on">Aktif</span>
                                    <?php else: ?>
                                        <span class="status-off">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($topping["created_at"]); ?></td>
                                <td>
                                    <a href="manage_toppings.php?edit=<?php echo (int)$topping["id"]; ?>" class="mini-btn edit-btn">
                                        Edit
                                    </a>

                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus topping ini?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$topping["id"]; ?>">
                                        <button type="submit" class="mini-btn delete-btn">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty">Belum ada topping.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>