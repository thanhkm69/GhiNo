<?php
require_once "./ConnectDB.php";
$db = new ConnectDB();

$message = "";
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

// Lấy dữ liệu người nợ theo ID
$debtor = $db->getOne("SELECT * FROM debtors WHERE debtorID = ?", [$id]);
if (!$debtor) {
    die("Người nợ không tồn tại!");
}

// Xử lý form sửa
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);

    // Kiểm tra trùng tên (ngoại trừ chính nó)
    $check = $db->getOne("SELECT * FROM debtors WHERE name = ? AND debtorID <> ?", [$name, $id]);
    if ($check) {
        $message = '<div class="alert alert-danger">Tên người nợ đã tồn tại!</div>';
    } else {
        $update = $db->execute(
            "UPDATE debtors SET name = ?, phone = ?, address = ? WHERE debtorID = ?",
            [$name, $phone, $address, $id]
        );
        if ($update) {
            header("Location: index.php");
            exit;
        } else {
            $message = '<div class="alert alert-danger">Cập nhật thất bại, vui lòng thử lại!</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sửa người nợ</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2><i class="bi bi-pencil-square"></i> Sửa người nợ</h2>
      <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
      <div class="card-body">
        <form method="post">
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-person-circle"></i> Tên người nợ</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($debtor['name']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-telephone-fill"></i> Số điện thoại</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($debtor['phone']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-geo-alt-fill"></i> Địa chỉ</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($debtor['address']) ?>">
          </div>
          <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Cập nhật</button>
        </form>
      </div>
    </div>
  </div>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
