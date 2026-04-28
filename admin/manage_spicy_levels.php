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
        $sortOrder = (int)($_POST["sort_order"] ?? 0);
        $isActive = isset($_POST["is_active"]) ? 1 : 0;

        if ($name === "") {
            $message = "Nama level pedas wajib diisi.";
            $messageType = "error";
        } else {
            $sql = "INSERT INTO spicy_levels (name, sort_order, is_active, created_at)
                    VALUES ('$name', $sortOrder, $isActive, NOW())";

            if (mysqli_query($conn, $sql)) {
                $message = "Level pedas berhasil ditambahkan.";
                $messageType = "success";
            } else {
                $message = "Gagal menambah level pedas: " . mysqli_error($conn);
                $messageType = "error";
            }
        }
    }

    if ($action === "update") {
        $id = (int)($_POST["id"] ?? 0);
        $name = clean($conn, $_POST["name"] ?? "");
        $sortOrder = (int)($_POST["sort_order"] ?? 0);
        $isActive = isset($_POST["is_active"]) ? 1 : 0;

        if ($id <= 0 || $name === "") {
            $message = "Data level pedas belum lengkap.";
            $messageType = "error";
        } else {
            $sql = "UPDATE spicy_levels
                    SET name = '$name',
                        sort_order = $sortOrder,
                        is_active = $isActive
                    WHERE id = $id
                    LIMIT 1";

            if (mysqli_query($conn, $sql)) {
                $message = "Level pedas berhasil diupdate.";
                $messageType = "success";
            } else {
                $message = "Gagal update level pedas: " . mysqli_error($conn);
                $messageType = "error";
            }
        }
    }

    if ($action === "delete") {
        $id = (int)($_POST["id"] ?? 0);

        if ($id > 0) {
            if (mysqli_query($conn, "DELETE FROM spicy_levels WHERE id = $id LIMIT 1")) {
                $message = "Level pedas berhasil dihapus.";
                $messageType = "success";
            } else {
                $message = "Gagal hapus level pedas: " . mysqli_error($conn);
                $messageType = "error";
            }
        }
    }
}

$editData = null;
if (isset($_GET["edit"])) {
    $editId = (int)$_GET["edit"];
    if ($editId > 0) {
        $editQuery = mysqli_query($conn, "SELECT * FROM spicy_levels WHERE id = $editId LIMIT 1");
        if ($editQuery && mysqli_num_rows($editQuery) > 0) {
            $editData = mysqli_fetch_assoc($editQuery);
        }
    }
}

$spicyLevels = mysqli_query($conn, "SELECT * FROM spicy_levels ORDER BY sort_order ASC, id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Level Pedas - Roumah Kitchen</title>
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

        .level-table {
            width: 100%;
            border-collapse: collapse;
        }

        .level-table th,
        .level-table td {
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

            .level-table {
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
            <h1 class="page-title">Kelola Level Pedas</h1>
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
                <?php echo $editData ? "Edit Level Pedas" : "Tambah Level Pedas"; ?>
            </h2>

            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $editData ? "update" : "add"; ?>">

                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$editData["id"]; ?>">
                <?php endif; ?>

                <div class="field">
                    <label for="name">Nama Level Pedas</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        required
                        value="<?php echo htmlspecialchars($editData["name"] ?? ""); ?>"
                        placeholder="Contoh: Level 1"
                    >
                </div>

                <div class="field">
                    <label for="sort_order">Urutan</label>
                    <input
                        type="number"
                        id="sort_order"
                        name="sort_order"
                        min="0"
                        required
                        value="<?php echo htmlspecialchars($editData["sort_order"] ?? "0"); ?>"
                        placeholder="Contoh: 1"
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
                        <?php echo $editData ? "Update Level" : "Tambah Level"; ?>
                    </button>

                    <?php if ($editData): ?>
                        <a href="manage_spicy_levels.php" class="secondary-btn cancel-btn" style="margin-top:0;">
                            Batal Edit
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-card">
            <h2 class="manage-title">Daftar Level Pedas</h2>

            <?php if ($spicyLevels && mysqli_num_rows($spicyLevels) > 0): ?>
                <table class="level-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($level = mysqli_fetch_assoc($spicyLevels)): ?>
                            <tr>
                                <td><?php echo (int)$level["id"]; ?></td>
                                <td><?php echo htmlspecialchars($level["name"]); ?></td>
                                <td><?php echo (int)$level["sort_order"]; ?></td>
                                <td>
                                    <?php if ((int)$level["is_active"] === 1): ?>
                                        <span class="status-on">Aktif</span>
                                    <?php else: ?>
                                        <span class="status-off">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($level["created_at"]); ?></td>
                                <td>
                                    <a href="manage_spicy_levels.php?edit=<?php echo (int)$level["id"]; ?>" class="mini-btn edit-btn">
                                        Edit
                                    </a>

                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus level pedas ini?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$level["id"]; ?>">
                                        <button type="submit" class="mini-btn delete-btn">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty">Belum ada level pedas.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>