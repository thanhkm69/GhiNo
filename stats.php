<?php
require_once "./ConnectDB.php";
$db = new ConnectDB();

// =====================
// 1. Thống kê tổng quát
// =====================
$summary = $db->getOne("
    SELECT 
        SUM(amount) as total_all,
        SUM(CASE WHEN status = 0 THEN amount ELSE 0 END) as total_unpaid,
        SUM(CASE WHEN status = 1 THEN amount ELSE 0 END) as total_paid
    FROM debts
");

$totalDebtors = $db->getOne("SELECT COUNT(*) as cnt FROM debtors")['cnt'];

// =====================
// 2. Thống kê theo ngày
// =====================
$dailyStats = $db->getAll("
    SELECT 
        DATE(created_at) as period,
        SUM(amount) as total_all,
        SUM(CASE WHEN status = 0 THEN amount ELSE 0 END) as total_unpaid,
        SUM(CASE WHEN status = 1 THEN amount ELSE 0 END) as total_paid
    FROM debts
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) DESC
");

// =====================
// 3. Thống kê theo tháng
// =====================
$monthlyStats = $db->getAll("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as period,
        SUM(amount) as total_all,
        SUM(CASE WHEN status = 0 THEN amount ELSE 0 END) as total_unpaid,
        SUM(CASE WHEN status = 1 THEN amount ELSE 0 END) as total_paid
    FROM debts
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY period DESC
");

// =====================
// 4. Thống kê theo năm
// =====================
$yearlyStats = $db->getAll("
    SELECT 
        YEAR(created_at) as period,
        SUM(amount) as total_all,
        SUM(CASE WHEN status = 0 THEN amount ELSE 0 END) as total_unpaid,
        SUM(CASE WHEN status = 1 THEN amount ELSE 0 END) as total_paid
    FROM debts
    GROUP BY YEAR(created_at)
    ORDER BY period DESC
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thống kê nợ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2><i class="bi bi-bar-chart"></i> Thống kê nợ</h2>
      <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </div>

    <!-- Tổng quan -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h4 class="mb-3"><i class="bi bi-graph-up"></i> Tổng quan</h4>
        <div class="row text-center">
          <div class="col-md-3">
            <div class="p-3 bg-primary text-white rounded">
              <h5>Tổng số người nợ</h5>
              <p class="fs-4 fw-bold"><?= number_format($totalDebtors) ?></p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3 bg-info text-white rounded">
              <h5>Tổng tất cả</h5>
              <p class="fs-4 fw-bold"><?= number_format($summary['total_all']) ?> đ</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3 bg-danger text-white rounded">
              <h5>Chưa trả</h5>
              <p class="fs-4 fw-bold"><?= number_format($summary['total_unpaid']) ?> đ</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3 bg-success text-white rounded">
              <h5>Đã trả</h5>
              <p class="fs-4 fw-bold"><?= number_format($summary['total_paid']) ?> đ</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Thống kê theo ngày -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h4 class="mb-3"><i class="bi bi-calendar-day"></i> Theo ngày</h4>
        <table class="table table-bordered table-striped">
          <thead class="table-dark">
            <tr>
              <th>Ngày</th>
              <th class="text-end">Tổng tất cả</th>
              <th class="text-end">Chưa trả</th>
              <th class="text-end">Đã trả</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dailyStats as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['period']) ?></td>
                <td class="text-end"><?= number_format($row['total_all']) ?> đ</td>
                <td class="text-end text-danger"><?= number_format($row['total_unpaid']) ?> đ</td>
                <td class="text-end text-success"><?= number_format($row['total_paid']) ?> đ</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Thống kê theo tháng -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h4 class="mb-3"><i class="bi bi-calendar-month"></i> Theo tháng</h4>
        <table class="table table-bordered table-striped">
          <thead class="table-dark">
            <tr>
              <th>Tháng</th>
              <th class="text-end">Tổng tất cả</th>
              <th class="text-end">Chưa trả</th>
              <th class="text-end">Đã trả</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($monthlyStats as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['period']) ?></td>
                <td class="text-end"><?= number_format($row['total_all']) ?> đ</td>
                <td class="text-end text-danger"><?= number_format($row['total_unpaid']) ?> đ</td>
                <td class="text-end text-success"><?= number_format($row['total_paid']) ?> đ</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Thống kê theo năm -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h4 class="mb-3"><i class="bi bi-calendar"></i> Theo năm</h4>
        <table class="table table-bordered table-striped">
          <thead class="table-dark">
            <tr>
              <th>Năm</th>
              <th class="text-end">Tổng tất cả</th>
              <th class="text-end">Chưa trả</th>
              <th class="text-end">Đã trả</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($yearlyStats as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['period']) ?></td>
                <td class="text-end"><?= number_format($row['total_all']) ?> đ</td>
                <td class="text-end text-danger"><?= number_format($row['total_unpaid']) ?> đ</td>
                <td class="text-end text-success"><?= number_format($row['total_paid']) ?> đ</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
