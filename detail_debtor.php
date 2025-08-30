<?php
require_once "./ConnectDB.php";
$db = new ConnectDB();

$debtorID = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
$debtor = $db->getOne("SELECT * FROM debtors WHERE debtorID = ?", [$debtorID]);
if (!$debtor) {
    die("Người nợ không tồn tại!");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["toggleStatus"])) {
        // Đổi trạng thái những khoản được tick
        $db->execute("
            UPDATE debts 
            SET status = CASE WHEN status = 0 THEN 1 ELSE 0 END,
                paid_at = CASE WHEN status = 1 THEN NOW() ELSE NULL END
            WHERE debtorID = ? AND checkbox = 1
        ", [$debtorID]);
    } elseif (isset($_POST["debtID"])) {
        // Chỉ cập nhật từng checkbox
        $debtID = intval($_POST["debtID"]);
        $checkbox = isset($_POST["checkbox"]) ? 1 : 0;
        $db->execute("UPDATE debts SET checkbox = ? WHERE debtID = ?", [$checkbox, $debtID]);
    } elseif (isset($_POST["toggleAll"])) {
        // Nếu checkbox "chọn tất cả" được tick => set tất cả = 1, ngược lại = 0
        $allChecked = isset($_POST["checkbox"]) ? 1 : 0;
        $db->execute("UPDATE debts SET checkbox = ? WHERE debtorID = ?", [$allChecked, $debtorID]);
    }

    header("Location: detail_debtor.php?id=" . $debtorID);
    exit;
}


$debts = $db->getAll("SELECT * FROM debts WHERE debtorID = ? ORDER BY debtID DESC", [$debtorID]);

$totals = $db->getOne("
    SELECT 
        SUM(CASE WHEN status = 0 THEN amount ELSE 0 END) as total_unpaid,
        SUM(CASE WHEN status = 1 THEN amount ELSE 0 END) as total_paid,
        SUM(amount) as total_all
    FROM debts
    WHERE debtorID = ? AND checkbox = 1
", [$debtorID]);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết nợ - <?= htmlspecialchars($debtor['name']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>
                <i class="bi bi-person-lines-fill"></i> Chi tiết nợ: <?= htmlspecialchars($debtor['name']) ?>
            </h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <p><strong><i class="bi bi-person-circle"></i> Tên:</strong> <?= htmlspecialchars($debtor['name']) ?></p>
                <p><strong><i class="bi bi-telephone-fill"></i> Điện thoại:</strong> <?= htmlspecialchars($debtor['phone']) ?></p>
                <p><strong><i class="bi bi-geo-alt-fill"></i> Địa chỉ:</strong> <?= htmlspecialchars($debtor['address']) ?></p>
            </div>
        </div>

        <form method="post" action="">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Danh sách khoản nợ</h5>
                <div>
                    <a href="add_debt.php?id=<?= $debtorID ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Thêm
                    </a>
                    <button type="submit" name="toggleStatus" class="btn btn-warning">
                        <i class="bi bi-arrow-repeat"></i> Đổi
                    </button>
                </div>
            </div>
        </form>


        <div class="card shadow-sm">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center">
                                <form method="post" action="">
                                    <input type="hidden" name="toggleAll" value="1">
                                    <input type="checkbox"
                                        name="checkbox"
                                        value="1"
                                        onchange="this.form.submit()"
                                        <?= $db->getOne("SELECT COUNT(*) as cnt FROM debts WHERE debtorID = ? AND checkbox = 1", [$debtorID])['cnt'] == count($debts) && count($debts) > 0 ? 'checked' : '' ?>>
                                </form>


                            <th><i class="bi bi-text-paragraph"></i> Mô tả</th>
                            <th><i class="bi bi-cash-coin"></i> Số tiền</th>
                            <th><i class="bi bi-flag-fill"></i> Trạng thái</th>
                            <th><i class="bi bi-calendar-event"></i> Ngày nợ</th>
                            <th><i class="bi bi-calendar-check"></i> Ngày trả</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($debts)) : ?>
                            <?php foreach ($debts as $d) : ?>
                                <tr>
                                    <td class="text-center">
                                        <form method="post" action="" class="d-inline debt-form">
                                            <input type="hidden" name="debtID" value="<?= $d['debtID'] ?>">
                                            <input type="checkbox"
                                                class="debt-checkbox"
                                                name="checkbox"
                                                value="1"
                                                onchange="this.form.submit()"
                                                <?= $d['checkbox'] == 1 ? 'checked' : '' ?>>
                                        </form>
                                    </td>
                                    <td><?= htmlspecialchars($d['description']) ?></td>
                                    <td><?= number_format($d['amount'], 0, ',', '.') ?> đ</td>
                                    <td>
                                        <?php if ($d['status'] == 1) : ?>
                                            <span class="badge bg-success">Đã trả</span>
                                        <?php else : ?>
                                            <span class="badge bg-danger">Chưa trả</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date("d-m-Y", strtotime($d['created_at'])) ?></td>
                                    <td><?= $d['paid_at'] ?: '-' ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- ✅ 3 dòng tổng kết -->
                            <tr class="fw-bold">
                                <td colspan="2" class="text-danger">Tổng chưa trả:</td>
                                <td class="text-danger"><?= number_format($totals['total_unpaid'] ?? 0, 0, ',', '.') ?> đ</td>
                                <td colspan="3"></td>
                            </tr>
                            <tr class="fw-bold">
                                <td colspan="2" class="text-success">Tổng đã trả:</td>
                                <td class="text-success"><?= number_format($totals['total_paid'] ?? 0, 0, ',', '.') ?> đ</td>
                                <td colspan="3"></td>
                            </tr>
                            <tr class="fw-bold">
                                <td colspan="2">Tất cả:</td>
                                <td><?= number_format($totals['total_all'] ?? 0, 0, ',', '.') ?> đ</td>
                                <td colspan="3"></td>
                            </tr>

                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Chưa có khoản nợ nào</td>
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