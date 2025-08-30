<?php 
require_once "./ConnectDB.php";
$db = new ConnectDB();

// Lấy ID người nợ
$debtorID = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

// Lấy thông tin người nợ
$debtor = $db->getOne("SELECT * FROM debtors WHERE debtorID = ?", [$debtorID]);
if (!$debtor) {
    die("Người nợ không tồn tại!");
}

// Nếu submit form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $description = trim($_POST["description"]);
    $amount = intval($_POST["amount"]);

    $db->execute("INSERT INTO debts (debtorID, description, amount) 
                 VALUES (?, ?, ?)", 
                 [$debtorID, $description, $amount]);

    header("Location: detail_debtor.php?id=" . $debtorID);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thêm khoản nợ - <?= htmlspecialchars($debtor['name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2><i class="bi bi-plus-circle"></i> Thêm khoản nợ cho <?= htmlspecialchars($debtor['name']) ?></h2>
      <a href="detail_debtor.php?id=<?= $debtorID ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Quay lại
      </a>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <input type="text" name="description" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Số tiền</label>
            <input type="number" name="amount" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-save"></i> Lưu
          </button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
