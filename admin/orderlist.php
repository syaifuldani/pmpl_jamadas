<?php
session_start();
require '../config/connection.php'; // Pastikan path sesuai dengan struktur folder Anda
require '../config/function.php'; // Pastikan path sesuai dengan struktur folder Anda

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) && $_SESSION['user_id'] != 'admin') {
    // Jika tidak ada session login, redirect ke halaman login
    header("Location: login_admin.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$jenishalaman = "Order list";

// Ambil daftar status unik dari database
$status_sql = "SELECT DISTINCT transaction_status FROM orders ORDER BY transaction_status ASC";
try {
    $status_stmt = $GLOBALS["db"]->prepare($status_sql);
    $status_stmt->execute();
    $status_list = $status_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $status_list = [];
}

// Filter dan pencarian
$where_clause = "";
$params = [];

// Filter berdasarkan status
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where_clause .= " WHERE transaction_status = :status";
    $params[':status'] = $_GET['status'];
}

// Filter berdasarkan tanggal
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $where_clause .= ($where_clause ? " AND" : " WHERE") . " DATE(transaction_time) >= :start_date";
    $params[':start_date'] = $_GET['start_date'];
}

if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $where_clause .= ($where_clause ? " AND" : " WHERE") . " DATE(transaction_time) <= :end_date";
    $params[':end_date'] = $_GET['end_date'];
}

// Filter berdasarkan pencarian order ID
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_clause .= ($where_clause ? " AND" : " WHERE") . " order_id LIKE :search";
    $params[':search'] = "%" . $_GET['search'] . "%";
}

// Tentukan jumlah data per halaman
$limit = 10;

// Ambil halaman saat ini dari URL, jika tidak ada set ke 1
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total dengan filter
$total_sql = "SELECT COUNT(*) as total FROM orders" . $where_clause;
$total_stmt = $GLOBALS["db"]->prepare($total_sql);
foreach ($params as $key => $value) {
    $total_stmt->bindValue($key, $value);
}
$total_stmt->execute();
$total_data = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data dengan filter
$sql = "SELECT o.*, c.nama_lengkap 
        FROM orders o 
        LEFT JOIN users c ON o.user_id = c.user_id" . 
        $where_clause . " 
        ORDER BY o.transaction_time DESC 
        LIMIT :limit OFFSET :offset";

try {
    $stmt = $GLOBALS["db"]->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$n = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order List</title>
    <link rel="stylesheet" href="./style/style.css">
</head>

<body>
    <div class="container">

        <?php require "template/sidebar.php"; ?>

        <div class="main-content">

            <?php require "template/header.php"; ?>

            <div class="content">
                <div class="content-header">
                    <div class="content-title">
                        <h3><i class="fas fa-shopping-cart"></i> Riwayat Penjualan</h3>
                        <p>Kelola dan pantau semua pesanan pelanggan</p>
                    </div>
                </div>

                <div class="filter-container">
                    <form action="" method="GET" class="filter-form" id="filterForm">
                        <div class="search-box">
                            <input type="text" id="searchInput" name="search" placeholder="Cari Order ID..." 
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                        <select name="status" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <?php foreach ($status_list as $status): ?>
                                <option value="<?php echo htmlspecialchars($status); ?>" 
                                    <?php echo (isset($_GET['status']) && $_GET['status'] === $status) ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(htmlspecialchars($status)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="date" name="start_date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>" 
                            onchange="this.form.submit()" placeholder="Tanggal Mulai">
                        <input type="date" name="end_date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>" 
                            onchange="this.form.submit()" placeholder="Tanggal Akhir">
                    </form>
                </div>

                <div class="table-wrapper">
                    <table class="table" id="orderTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> No</th>
                                <th><i class="fas fa-box"></i> Produk</th>
                                <th><i class="fas fa-receipt"></i> Order ID</th>
                                <th><i class="fas fa-calendar"></i> Tanggal</th>
                                <th><i class="fas fa-user"></i> Nama Kustomer</th>
                                <th><i class="fas fa-info-circle"></i> Status</th>
                                <th><i class="fas fa-tag"></i> Total Harga</th>
                                <th><i class="fas fa-cogs"></i> Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?= $n += 1 ?></td>
                                        <td>Undangan Pernikahan</td>
                                        <td>#<?= $order['order_id']; ?></td>
                                        <td><?= date('d M Y', strtotime($order['transaction_time'])); ?></td>
                                        <td><?= $order['nama_lengkap']; ?></td>
                                        <td>
                                            <span class="status-badge <?= strtolower($order['transaction_status']); ?>">
                                                <?php
                                                $status_icon = '';
                                                switch (strtolower($order['transaction_status'])) {
                                                    case 'pending':
                                                        $status_icon = 'fa-clock';
                                                        break;
                                                    case 'success':
                                                        $status_icon = 'fa-check-circle';
                                                        break;
                                                    case 'failed':
                                                        $status_icon = 'fa-times-circle';
                                                        break;
                                                    default:
                                                        $status_icon = 'fa-info-circle';
                                                }
                                                ?>
                                                <i class="fas <?= $status_icon ?>"></i>
                                                <?= ucfirst($order['transaction_status']); ?>
                                            </span>
                                        </td>
                                        <td>Rp <?= number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                        <td>
                                            <a href="detail_order.php?order_id=<?= htmlspecialchars($order['order_id']); ?>" 
                                               class="btn-detail">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="no-data">
                                        <i class="fas fa-shopping-cart"></i>
                                        <p>Belum ada pesanan</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <ul>
                        <?php if ($page > 1): ?>
                            <li>
                                <a href="?page=<?= $page - 1; ?><?php 
                                    echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; 
                                    echo isset($_GET['start_date']) ? '&start_date=' . urlencode($_GET['start_date']) : ''; 
                                    echo isset($_GET['end_date']) ? '&end_date=' . urlencode($_GET['end_date']) : ''; 
                                    echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; 
                                ?>">
                                    <i class="fas fa-chevron-left"></i> Prev
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="<?= ($i == $page) ? 'active' : ''; ?>">
                                <a href="?page=<?= $i; ?><?php 
                                    echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; 
                                    echo isset($_GET['start_date']) ? '&start_date=' . urlencode($_GET['start_date']) : ''; 
                                    echo isset($_GET['end_date']) ? '&end_date=' . urlencode($_GET['end_date']) : ''; 
                                    echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; 
                                ?>">
                                    <?= $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li>
                                <a href="?page=<?= $page + 1; ?><?php 
                                    echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; 
                                    echo isset($_GET['start_date']) ? '&start_date=' . urlencode($_GET['start_date']) : ''; 
                                    echo isset($_GET['end_date']) ? '&end_date=' . urlencode($_GET['end_date']) : ''; 
                                    echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; 
                                ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit-id.js" crossorigin="anonymous"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const orderTable = document.getElementById('orderTable');
        const rows = orderTable.getElementsByTagName('tr');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            // Mulai dari index 1 untuk melewati header tabel
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const orderIdCell = row.cells[2]; // Index kolom Order ID
                const orderId = orderIdCell.textContent.toLowerCase();
                
                // Tampilkan atau sembunyikan baris berdasarkan hasil pencarian
                row.style.display = orderId.includes(searchTerm) ? '' : 'none';
            }
        });
    });
    </script>
</body>

</html>