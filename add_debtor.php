<?php
require_once "./ConnectDB.php";
$db = new ConnectDB();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);

    // Kiểm tra trùng tên
    $check = $db->getOne("SELECT * FROM debtors WHERE name = ?", [$name]);
    if ($check) {
        $message = '<div class="alert alert-danger">Tên người nợ đã tồn tại!</div>';
    } else {
        $insert = $db->execute("INSERT INTO debtors (name, phone, address) VALUES (?, ?, ?)", [$name, $phone, $address]);
        if ($insert) {
            header("Location: index.php"); // trở về trang danh sách
            exit;
        } else {
            $message = '<div class="alert alert-danger">Thêm thất bại, vui lòng thử lại!</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thêm người nợ</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2><i class="bi bi-person-plus-fill"></i> Thêm người nợ</h2>
      <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
      <div class="card-body">
        <form method="post">
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-person-circle"></i> Tên người nợ</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-telephone-fill"></i> Số điện thoại</label>
            <input type="text" name="phone" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-geo-alt-fill"></i> Địa chỉ</label>
            <input type="text" name="address" class="form-control">
          </div>
          <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Lưu</button>
        </form>
      </div>
    </div>
  </div>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
