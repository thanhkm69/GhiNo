<?php
require_once "./ConnectDB.php";
$db = new ConnectDB();

$message = "";
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

// Lấy dữ liệu khoản nợ theo ID
$debt = $db->getOne("SELECT * FROM debts WHERE debtID = ?", [$id]);
if (!$debt) {
    die("Khoản nợ không tồn tại!");
}

// Xử lý form sửa
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $description = trim($_POST["description"]);
    $amount = floatval($_POST["amount"]);

    // Cập nhật khoản nợ
    $update = $db->execute(
        "UPDATE debts SET description = ?, amount = ? WHERE debtID = ?",
        [$description, $amount, $id]
    );

    if ($update) {
        header("Location: detail_debtor.php?id=" . $debt['debtorID']);
        exit;
    } else {
        $message = '<div class="alert alert-danger">Cập nhật thất bại, vui lòng thử lại!</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sửa khoản nợ</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2><i class="bi bi-pencil-square"></i> Sửa khoản nợ</h2>
      <a href="detail_debtor.php?id=<?= $debt['debtorID'] ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Quay lại
      </a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
      <div class="card-body">
        <form method="post">
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-card-text"></i> Mô tả</label>
            <input type="text" name="description" class="form-control"
                   value="<?= htmlspecialchars($debt['description']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-cash-coin"></i> Số tiền</label>
            <input type="number" step="1000" name="amount" class="form-control"
                   value="<?= $debt['amount'] ?>" required>
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
