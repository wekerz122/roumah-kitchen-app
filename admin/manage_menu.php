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

function uploadMenuImage($fileInputName, $oldImage = null) {
    if (
        !isset($_FILES[$fileInputName]) ||
        !is_array($_FILES[$fileInputName]) ||
        $_FILES[$fileInputName]["error"] === UPLOAD_ERR_NO_FILE
    ) {
        return $oldImage;
    }

    if ($_FILES[$fileInputName]["error"] !== UPLOAD_ERR_OK) {
        return false;
    }

    $tmpName = $_FILES[$fileInputName]["tmp_name"];
    $originalName = $_FILES[$fileInputName]["name"];
    $fileSize = (int)$_FILES[$fileInputName]["size"];

    if ($fileSize > 2 * 1024 * 1024) {
        return false;
    }

    $allowedMime = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp",
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    if (!isset($allowedMime[$mime])) {
        return false;
    }

    $ext = $allowedMime[$mime];
    $newName = "menu_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;

    $uploadDirFs = dirname(__DIR__) . "/image/";
    $uploadDirDb = "image/";

    if (!is_dir($uploadDirFs)) {
        mkdir($uploadDirFs, 0755, true);
    }

    $destinationFs = $uploadDirFs . $newName;
    $destinationDb = $uploadDirDb . $newName;

    if (!move_uploaded_file($tmpName, $destinationFs)) {
        return false;
    }

    if (!empty($oldImage)) {
        $oldPath = dirname(__DIR__) . "/" . ltrim($oldImage, "/");
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    return $destinationDb;
}

$message = "";
$messageType = "success";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $name = clean($conn, $_POST["name"] ?? "");
        $category = clean($conn, $_POST["category"] ?? "");
        $price = (int)($_POST["price"] ?? 0);
        $description = clean($conn, $_POST["description"] ?? "");

        if ($name === "" || $category === "" || $price <= 0) {
            $message = "Nama, kategori, dan harga wajib diisi.";
            $messageType = "error";
        } else {
            $imagePath = uploadMenuImage("image_file");

            if ($imagePath === false) {
                $message = "Upload gambar gagal. Gunakan JPG, PNG, atau WEBP maksimal 2MB.";
                $messageType = "error";
            } else {
                $imageSql = $imagePath ? "'" . mysqli_real_escape_string($conn, $imagePath) . "'" : "NULL";

                $sql = "INSERT INTO menu (name, category, price, description, image, created_at)
                        VALUES ('$name', '$category', $price, '$description', $imageSql, NOW())";

                if (mysqli_query($conn, $sql)) {
                    $message = "Menu berhasil ditambahkan.";
                    $messageType = "success";
                } else {
                    $message = "Gagal menambah menu: " . mysqli_error($conn);
                    $messageType = "error";
                }
            }
        }
    }

    if ($action === "update") {
        $id = (int)($_POST["id"] ?? 0);
        $name = clean($conn, $_POST["name"] ?? "");
        $category = clean($conn, $_POST["category"] ?? "");
        $price = (int)($_POST["price"] ?? 0);
        $description = clean($conn, $_POST["description"] ?? "");
        $oldImage = $_POST["old_image"] ?? "";

        if ($id <= 0 || $name === "" || $category === "" || $price <= 0) {
            $message = "Data edit menu belum lengkap.";
            $messageType = "error";
        } else {
            $imagePath = uploadMenuImage("image_file", $oldImage);

            if ($imagePath === false) {
                $message = "Upload gambar gagal. Gunakan JPG, PNG, atau WEBP maksimal 2MB.";
                $messageType = "error";
            } else {
                $imageSql = $imagePath ? "'" . mysqli_real_escape_string($conn, $imagePath) . "'" : "NULL";

                $sql = "UPDATE menu
                        SET name = '$name',
                            category = '$category',
                            price = $price,
                            description = '$description',
                            image = $imageSql
                        WHERE id = $id
                        LIMIT 1";

                if (mysqli_query($conn, $sql)) {
                    $message = "Menu berhasil diupdate.";
                    $messageType = "success";
                } else {
                    $message = "Gagal update menu: " . mysqli_error($conn);
                    $messageType = "error";
                }
            }
        }
    }

    if ($action === "delete") {
        $id = (int)($_POST["id"] ?? 0);

        if ($id > 0) {
            $check = mysqli_query($conn, "SELECT image FROM menu WHERE id = $id LIMIT 1");
            $row = mysqli_fetch_assoc($check);

            if (mysqli_query($conn, "DELETE FROM menu WHERE id = $id LIMIT 1")) {
                if (!empty($row["image"])) {
                    $oldPath = dirname(__DIR__) . "/" . ltrim($row["image"], "/");
                    if (is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                $message = "Menu berhasil dihapus.";
                $messageType = "success";
            } else {
                $message = "Gagal hapus menu: " . mysqli_error($conn);
                $messageType = "error";
            }
        }
    }
}

$editData = null;
if (isset($_GET["edit"])) {
    $editId = (int)$_GET["edit"];
    if ($editId > 0) {
        $editQuery = mysqli_query($conn, "SELECT * FROM menu WHERE id = $editId LIMIT 1");
        if ($editQuery && mysqli_num_rows($editQuery) > 0) {
            $editData = mysqli_fetch_assoc($editQuery);
        }
    }
}

$menus = mysqli_query($conn, "SELECT * FROM menu ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Roumah Kitchen</title>
    <link rel="stylesheet" href="../roumahstyle.css">
    <style>
        .manage-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .manage-grid {
            display: grid;
            grid-template-columns: 380px 1fr;
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

        .menu-table {
            width: 100%;
            border-collapse: collapse;
        }

        .menu-table th,
        .menu-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: top;
            font-size: 14px;
        }

        .menu-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
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

        .cancel-btn {
            background: #e5e7eb;
            color: #111827;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }

        .image-preview {
            max-width: 120px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            margin-top: 10px;
        }

        .action-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        @media (max-width: 900px) {
            .manage-grid {
                grid-template-columns: 1fr;
            }

            .menu-table {
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
            <h1 class="page-title">Kelola Menu</h1>
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
                <?php echo $editData ? "Edit Menu" : "Tambah Menu"; ?>
            </h2>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $editData ? "update" : "add"; ?>">

                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$editData["id"]; ?>">
                    <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($editData["image"] ?? ""); ?>">
                <?php endif; ?>

                <div class="field">
                    <label for="name">Nama Menu</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        required
                        value="<?php echo htmlspecialchars($editData["name"] ?? ""); ?>"
                        placeholder="Contoh: Nasi Ayam Taichan"
                    >
                </div>

                <div class="field">
                    <label for="category">Kategori</label>
                    <select id="category" name="category" required>
                        <option value="">-- Pilih kategori --</option>
                        <?php
                        $categories = ["Nasi", "Minuman", "Snack"];
                        $selectedCategory = $editData["category"] ?? "";
                        foreach ($categories as $cat):
                        ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $selectedCategory === $cat ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="price">Harga</label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="1"
                        required
                        value="<?php echo htmlspecialchars($editData["price"] ?? ""); ?>"
                        placeholder="Contoh: 18000"
                    >
                </div>

                <div class="field">
                    <label for="description">Deskripsi</label>
                    <textarea
                        id="description"
                        name="description"
                        placeholder="Contoh: Ayam taichan gurih pedas"
                    ><?php echo htmlspecialchars($editData["description"] ?? ""); ?></textarea>
                </div>

                <div class="field">
                    <label for="image_file">Gambar Menu</label>
                    <input type="file" id="image_file" name="image_file" accept=".jpg,.jpeg,.png,.webp">

                    <?php if ($editData && !empty($editData["image"])): ?>
                        <div>
                            <img src="../<?php echo htmlspecialchars($editData["image"]); ?>" alt="Preview" class="image-preview">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="action-row">
                    <button type="submit" class="primary-btn" style="margin-top:0;">
                        <?php echo $editData ? "Update Menu" : "Tambah Menu"; ?>
                    </button>

                    <?php if ($editData): ?>
                        <a href="manage_menu.php" class="secondary-btn cancel-btn" style="margin-top:0;">
                            Batal Edit
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-card">
            <h2 class="manage-title">Daftar Menu</h2>

            <?php if ($menus && mysqli_num_rows($menus) > 0): ?>
                <table class="menu-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($menu = mysqli_fetch_assoc($menus)): ?>
                            <tr>
                                <td><?php echo (int)$menu["id"]; ?></td>
                                <td>
                                    <?php if (!empty($menu["image"])): ?>
                                        <img src="../<?php echo htmlspecialchars($menu["image"]); ?>" alt="Menu" class="menu-thumb">
                                    <?php else: ?>
                                        <div class="menu-thumb" style="display:flex;align-items:center;justify-content:center;">🍽️</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($menu["name"]); ?></td>
                                <td><?php echo htmlspecialchars($menu["category"]); ?></td>
                                <td>Rp <?php echo number_format((int)$menu["price"], 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($menu["description"]); ?></td>
                                <td>
                                    <a href="manage_menu.php?edit=<?php echo (int)$menu["id"]; ?>" class="mini-btn edit-btn">
                                        Edit
                                    </a>

                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus menu ini?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$menu["id"]; ?>">
                                        <button type="submit" class="mini-btn delete-btn">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty">Belum ada menu.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>