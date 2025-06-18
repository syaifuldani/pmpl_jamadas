<?php
session_start();

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) && $_SESSION['user_id'] != 'admin') {
    // Jika tidak ada session login, redirect ke halaman login
    header("Location: login_admin.php");
    exit();
}

$jenishalaman = "Produk";

// Sertakan file koneksi ke database
require '../config/connection.php'; // Pastikan path sesuai dengan struktur folder Anda

// Alias objek PDO dari $GLOBALS['db'] ke $pdo untuk kompatibilitas
$pdo = $GLOBALS['db'];

// Tentukan jumlah data per halaman
$limit = 8;

// Ambil halaman saat ini dari URL, jika tidak ada set ke 1
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Ambil data untuk halaman saat ini
$where_clause = "";
$params = [];

// Filter berdasarkan kategori
if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
    $where_clause .= " WHERE kategori = :kategori";
    $params[':kategori'] = $_GET['kategori'];
}

// Filter berdasarkan sub kategori
if (isset($_GET['sub_kategori']) && !empty($_GET['sub_kategori'])) {
    $where_clause .= ($where_clause ? " AND" : " WHERE") . " sub_kategori = :sub_kategori";
    $params[':sub_kategori'] = $_GET['sub_kategori'];
}

// Filter berdasarkan pencarian
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = "%" . $_GET['search'] . "%";
    $where_clause .= ($where_clause ? " AND" : " WHERE") . " (nama_produk LIKE :search OR deskripsi LIKE :search)";
    $params[':search'] = $search_term;
}

// Hitung total entri dengan filter
$total_sql = "SELECT COUNT(*) as total FROM products" . $where_clause;
try {
    $total_stmt = $pdo->prepare($total_sql);
    foreach ($params as $key => $value) {
        $total_stmt->bindValue($key, $value);
    }
    $total_stmt->execute();
    $total_data = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_data / $limit);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Ambil data untuk halaman saat ini dengan filter
$sql = "SELECT product_id, nama_produk, deskripsi, stok, harga_produk, gambar_satu, kategori, sub_kategori 
        FROM products" . $where_clause . " LIMIT :limit OFFSET :offset";

try {
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Ambil daftar kategori untuk filter
$kategori_sql = "SELECT DISTINCT kategori FROM products ORDER BY kategori ASC";
try {
    $kategori_stmt = $pdo->prepare($kategori_sql);
    $kategori_stmt->execute();
    $kategori_list = $kategori_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $kategori_list = [];
}

// Ambil daftar sub kategori untuk filter
$sub_kategori_sql = "SELECT DISTINCT sub_kategori FROM products ORDER BY sub_kategori ASC";
try {
    $sub_kategori_stmt = $pdo->prepare($sub_kategori_sql);
    $sub_kategori_stmt->execute();
    $sub_kategori_list = $sub_kategori_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $sub_kategori_list = [];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products</title>
    <link rel="stylesheet" href="./style/style.css">
</head>

<body>
    <div class="container">

        <?php require "template/sidebar.php"; ?>

        <main class="main-content">

            <?php require "template/header.php"; ?>

            <div class="add-product-button">
                <div class="filter-container">
                    <form action="" method="GET" class="filter-form" id="filterForm">
                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="Cari produk..."
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                        <select name="kategori" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($kategori_list as $kat): ?>
                            <option value="<?php echo htmlspecialchars($kat); ?>"
                                <?php echo (isset($_GET['kategori']) && $_GET['kategori'] === $kat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kat); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="sub_kategori" onchange="this.form.submit()">
                            <option value="">Semua Sub Kategori</option>
                            <?php foreach ($sub_kategori_list as $sub_kat): ?>
                            <option value="<?php echo htmlspecialchars($sub_kat); ?>"
                                <?php echo (isset($_GET['sub_kategori']) && $_GET['sub_kategori'] === $sub_kat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sub_kat); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <button onclick="window.location.href='tambah_barang.php'">
                        <i class="fas fa-plus"></i> Tambah Produk Baru
                    </button>
                </div>
            </div>

            <div class="content">
                <div class="content-header">
                    <div class="content-title">
                        <h3><i class="fas fa-box"></i>List Produk</h3>
                        <p>Manajemen Produk Jamu</p>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="table" id="productTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-image"></i> Gambar</th>
                                <th><i class="fas fa-hashtag"></i> ID</th>
                                <th><i class="fas fa-box"></i> Nama Produk</th>
                                <th><i class="fas fa-align-left"></i> Deskripsi</th>
                                <th><i class="fas fa-tag"></i> Stok</th>
                                <th><i class="fas fa-tag"></i> Harga</th>
                                <th><i class="fas fa-tags"></i> Kategori</th>
                                <th><i class="fas fa-tags"></i> Sub Kategori</th>
                                <th><i class="fas fa-cogs"></i> Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($product['gambar_satu'])): ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($product['gambar_satu']); ?>"
                                        alt="Gambar Produk"
                                        style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                    <div
                                        style="width: 50px; height: 50px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                        <i class="fas fa-image" style="color: #ddd; font-size: 20px;"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="small-text">#<?php echo htmlspecialchars($product['product_id']); ?></td>
                                <td class="small-text"><?php echo htmlspecialchars($product['nama_produk']); ?></td>
                                <td class="small-text">
                                    <?php echo htmlspecialchars(substr($product['deskripsi'], 0, 50)) . '...'; ?></td>
                                <td class="small-text">
                                    <?php echo htmlspecialchars($product['stok']); ?></td>
                                <td class="small-text">Rp
                                    <?php echo number_format($product['harga_produk'], 0, ',', '.'); ?></td>
                                <td class="small-text"><?php echo htmlspecialchars($product['kategori']); ?></td>
                                <td class="small-text"><?php echo htmlspecialchars($product['sub_kategori']); ?></td>
                                <td>
                                    <div class="aksi">
                                        <button
                                            onclick="window.location.href='edit_barang.php?id=<?php echo urlencode($product['product_id']); ?>'"
                                            class="edit-btn small-btn">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button
                                            onclick="if(confirm('Apakah Anda yakin ingin menghapus produk ini?')) window.location.href='hapus_barang.php?id=<?php echo urlencode($product['product_id']); ?>'"
                                            class="delete-btn small-btn">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 30px;">
                                    <i class="fas fa-box-open"
                                        style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
                                    <p>Tidak ada produk ditemukan.</p>
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
                            <a
                                href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['kategori']) ? '&kategori=' . urlencode($_GET['kategori']) : ''; ?><?php echo isset($_GET['sub_kategori']) ? '&sub_kategori=' . urlencode($_GET['sub_kategori']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>">
                                <i class="fas fa-chevron-left"></i> Prev
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a
                                href="?page=<?php echo $i; ?><?php echo isset($_GET['kategori']) ? '&kategori=' . urlencode($_GET['kategori']) : ''; ?><?php echo isset($_GET['sub_kategori']) ? '&sub_kategori=' . urlencode($_GET['sub_kategori']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                        <li>
                            <a
                                href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['kategori']) ? '&kategori=' . urlencode($_GET['kategori']) : ''; ?><?php echo isset($_GET['sub_kategori']) ? '&sub_kategori=' . urlencode($_GET['sub_kategori']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const productTable = document.getElementById('productTable');
        const rows = productTable.getElementsByTagName('tr');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            // Mulai dari index 1 untuk melewati header tabel
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                // Cari di setiap sel kecuali sel gambar dan aksi
                for (let j = 1; j < cells.length - 1; j++) {
                    const cell = cells[j];
                    if (cell.textContent.toLowerCase().indexOf(searchTerm) > -1) {
                        found = true;
                        break;
                    }
                }

                // Tampilkan atau sembunyikan baris berdasarkan hasil pencarian
                row.style.display = found ? '' : 'none';
            }
        });

        // Tambahkan event listener untuk filter kategori dan sub kategori
        const filterForm = document.getElementById('filterForm');
        const kategoriSelect = filterForm.querySelector('select[name="kategori"]');
        const subKategoriSelect = filterForm.querySelector('select[name="sub_kategori"]');

        // Fungsi untuk memfilter tabel berdasarkan kategori dan sub kategori
        function filterTable() {
            const selectedKategori = kategoriSelect.value.toLowerCase();
            const selectedSubKategori = subKategoriSelect.value.toLowerCase();

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const kategoriCell = row.cells[5]; // Index kolom kategori
                const subKategoriCell = row.cells[6]; // Index kolom sub kategori
                const searchTerm = searchInput.value.toLowerCase();

                const kategoriMatch = !selectedKategori || kategoriCell.textContent.toLowerCase() ===
                    selectedKategori;
                const subKategoriMatch = !selectedSubKategori || subKategoriCell.textContent.toLowerCase() ===
                    selectedSubKategori;
                const searchMatch = !searchTerm || Array.from(row.cells).some(cell =>
                    cell.textContent.toLowerCase().indexOf(searchTerm) > -1
                );

                row.style.display = (kategoriMatch && subKategoriMatch && searchMatch) ? '' : 'none';
            }
        }

        // Event listener untuk filter
        kategoriSelect.addEventListener('change', filterTable);
        subKategoriSelect.addEventListener('change', filterTable);
    });
    </script>
</body>

</html>