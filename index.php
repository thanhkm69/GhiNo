<?php
require_once "./ConnectDB.php";
$db = new ConnectDB();

// Xử lý tìm kiếm
$keyword = isset($_GET["keyword"]) ? trim($_GET["keyword"]) : "";

// Query với tìm kiếm + sắp xếp
if ($keyword !== "") {
  $debtors = $db->getAll("SELECT * FROM debtors WHERE name LIKE ? ORDER BY debtorID DESC", ["%$keyword%"]);
} else {
  $debtors = $db->getAll("SELECT * FROM debtors ORDER BY debtorID DESC");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sổ ghi nợ</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
      <h2 class="mb-0">
        <i class="bi bi-journal-text"></i> Sổ ghi nợ
      </h2>
      <a class="btn btn-danger" href="stats.php">Xem thống kê</a>
      <div class="d-flex gap-2">
        <!-- Form tìm kiếm -->
        <form method="get" class="d-flex" role="search">
          <input class="form-control me-2" type="search" name="keyword" placeholder="Tìm theo tên..." value="<?= htmlspecialchars($keyword) ?>">
          <button class="btn btn-outline-secondary" type="submit">
            <i class="bi bi-search"></i>
          </button>
        </form>
        <!-- Nút thêm -->
        <a href="add_debtor.php" class="btn btn-primary">
          <i class="bi bi-person-plus-fill"></i> Thêm
        </a>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th><i class="bi bi-hash"></i> ID</th>
              <th><i class="bi bi-person-circle"></i> Tên</th>
              <th><i class="bi bi-telephone-fill"></i> Điện Thoại</th>
              <th><i class="bi bi-geo-alt-fill"></i> Địa chỉ</th>
              <th class="text-center"><i class="bi bi-gear"></i> Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($debtors)) : ?>
              <?php foreach ($debtors as $d) : ?>
                <tr>
                  <td><?= $d['debtorID'] ?></td>
                  <td><?= htmlspecialchars($d['name']) ?></td>
                  <td><?= htmlspecialchars($d['phone']) ?></td>
                  <td><?= htmlspecialchars($d['address']) ?></td>
                  <td class="text-center">
                    <a href="edit_debtor.php?id=<?= $d['debtorID'] ?>" class="btn btn-sm btn-warning me-1">
                      <i class="bi bi-pencil-square"></i> Sửa
                    </a>
                    <a href="detail_debtor.php?id=<?= $d['debtorID'] ?>" class="btn btn-sm btn-info me-1">
                      <i class="bi bi-eye"></i> Xem
                    </a>

                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else : ?>
              <tr>
                <td colspan="5" class="text-center text-muted">Không tìm thấy dữ liệu</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>