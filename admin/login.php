<?php
session_start();
include "../config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = mysqli_real_escape_string($conn, $_POST["username"] ?? "");
    $password = md5($_POST["password"] ?? "");

    $query = mysqli_query($conn, "SELECT * FROM admin_users WHERE username = '$username' AND password = '$password' LIMIT 1");

    if (mysqli_num_rows($query) > 0) {
        $admin = mysqli_fetch_assoc($query);
        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_username"] = $admin["username"];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Roumah Kitchen</title>
    <link rel="stylesheet" href="../roumahstyle.css">
</head>
<body class="login-page">

    <div class="login-box">
        <h1 class="login-title">Login Admin</h1>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="field">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="login-btn">Masuk</button>
        </form>
    </div>

</body>
</html>