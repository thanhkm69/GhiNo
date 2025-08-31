<?php
session_start();

// Nếu đã đăng nhập rồi thì chuyển sang index
if (isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$message = "";

// Xử lý form login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Tài khoản mặc định
    $defaultUser = "phuong";
    $defaultPass = "123";

    if ($username === $defaultUser && $password === $defaultPass) {
        $_SESSION["user"] = $username;
        header("Location: index.php");
        exit;
    } else {
        $message = '<div class="alert alert-danger">Sai tài khoản hoặc mật khẩu!</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
  <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
    <h3 class="text-center mb-3"><i class="bi bi-lock-fill"></i> Đăng nhập</h3>
    <?= $message ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Tên đăng nhập</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Mật khẩu</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</button>
    </form>
  </div>
</body>
</html>
