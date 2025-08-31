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
    } elseif (isset($_POST["debtID"]) && isset($_POST["status"])) {
        // ✅ Cập nhật trực tiếp trạng thái từ select
        $debtID = intval($_POST["debtID"]);
        $status = intval($_POST["status"]);

        if ($status == 1) {
            $db->execute("UPDATE debts SET status = 1, paid_at = NOW() WHERE debtID = ?", [$debtID]);
        } else {
            $db->execute("UPDATE debts SET status = 0, paid_at = NULL WHERE debtID = ?", [$debtID]);
        }
    } elseif (isset($_POST["toggleAll"])) {
        // ✅ Chọn tất cả
        $allChecked = isset($_POST["checkbox"]) ? 1 : 0;
        $db->execute("UPDATE debts SET checkbox = ? WHERE debtorID = ?", [$allChecked, $debtorID]);
    } elseif (isset($_POST["debtID"]) && isset($_POST["checkbox"])) {
        // ✅ Tick từng ô checkbox riêng lẻ
        $debtID = intval($_POST["debtID"]);
        $checked = ($_POST["checkbox"] == 1) ? 1 : 0;
        $db->execute("UPDATE debts SET checkbox = ? WHERE debtID = ?", [$checked, $debtID]);
    } elseif (isset($_POST["deleteDebt"])) {
        // ✅ Xóa nợ
        $debtID = intval($_POST["deleteDebt"]);
        $db->execute("DELETE FROM debts WHERE debtID = ? AND debtorID = ?", [$debtID, $debtorID]);
    }

    // ✅ Giữ lại statusFilter khi redirect
    $statusFilter = isset($_GET["statusFilter"]) ? "&statusFilter=" . intval($_GET["statusFilter"]) : "";
    header("Location: detail_debtor.php?id=" . $debtorID . $statusFilter);
    exit;
}



$statusFilter = isset($_GET['statusFilter']) && $_GET['statusFilter'] !== ''
    ? intval($_GET['statusFilter'])
    : null;

if ($statusFilter !== null) {
    // Lấy danh sách nợ theo status filter
    $debts = $db->getAll(
        "SELECT * FROM debts WHERE debtorID = ? AND status = ? ORDER BY debtID DESC",
        [$debtorID, $statusFilter]
    );

    // ✅ Tính tổng theo status filter
    $totals = $db->getOne("
        SELECT 
            SUM(CASE WHEN status = 0 THEN amount ELSE 0 END) as total_unpaid,
            SUM(CASE WHEN status = 1 THEN amount ELSE 0 END) as total_paid,
            SUM(amount) as total_all
        FROM debts
        WHERE debtorID = ? AND status = ? AND checkbox = 1
    ", [$debtorID, $statusFilter]);
} else {
    // Lấy tất cả nếu không lọc
    $debts = $db->getAll(
        "SELECT * FROM debts WHERE debtorID = ? ORDER BY debtID DESC",
        [$debtorID]
    );

    // ✅ Tính tổng không giới hạn status
    $totals = $db->getOne("
        SELECT 
            SUM(CASE WHEN status = 0 THEN amount ELSE 0 END) as total_unpaid,
            SUM(CASE WHEN status = 1 THEN amount ELSE 0 END) as total_paid,
            SUM(amount) as total_all
        FROM debts
        WHERE debtorID = ? AND checkbox = 1
    ", [$debtorID]);
}


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

        <!-- Form lọc trạng thái -->
        <form method="get" class="mb-3 d-flex align-items-center">
            <input type="hidden" name="id" value="<?= $debtorID ?>">
            <label for="statusFilter" class="me-2 fw-bold">Lọc trạng thái:</label>
            <select name="statusFilter" id="statusFilter" class="form-select w-auto me-2"
                onchange="this.form.submit()">
                <option value="">Tất cả</option>
                <option value="0" <?= (isset($_GET['statusFilter']) && $_GET['statusFilter'] === '0') ? 'selected' : '' ?>>
                    ❌ Chưa trả
                </option>
                <option value="1" <?= (isset($_GET['statusFilter']) && $_GET['statusFilter'] === '1') ? 'selected' : '' ?>>
                    ✅ Đã trả
                </option>
            </select>

            <!-- Nút xóa lọc -->
            <a href="detail_debtor.php?id=<?= $debtorID ?>" class="btn btn-secondary">
                Xóa lọc
            </a>
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
                            <th><i class="bi bi-gear"></i> Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($debts)) : ?>
                            <?php foreach ($debts as $d) : ?>
                                <tr>
                                    <td class="text-center">
                                        <form method="post" action="" class="d-inline debt-form">
                                            <input type="hidden" name="debtID" value="<?= $d['debtID'] ?>">
                                            <!-- hidden luôn gửi 0 -->
                                            <input type="hidden" name="checkbox" value="0">
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
                                        <form method="post" action="" class="d-inline">
                                            <input type="hidden" name="debtID" value="<?= $d['debtID'] ?>">
                                            <select name="status" class="form-select form-select-sm fw-bold"
                                                onchange="this.form.submit()">
                                                <option value="0" <?= $d['status'] == 0 ? 'selected' : '' ?>>
                                                    ❌ Chưa trả
                                                </option>
                                                <option value="1" <?= $d['status'] == 1 ? 'selected' : '' ?>>
                                                    ✅ Đã trả
                                                </option>
                                            </select>
                                        </form>
                                    </td>
                                    <td><?= date("d-m-Y", strtotime($d['created_at'])) ?></td>
                                    <td><?= $d['paid_at'] != NULL ? date("d-m-Y", strtotime($d['paid_at'])) : '-' ?></td>
                                    <td>
                                        <a href="edit_debt.php?id=<?= $d['debtID'] ?>" class="btn btn-sm btn-warning"> Sửa
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="post" action="" class="d-inline"
                                            onsubmit="return confirm('Bạn có chắc muốn xóa khoản nợ này?');">
                                            <input type="hidden" name="deleteDebt" value="<?= $d['debtID'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Xóa
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
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