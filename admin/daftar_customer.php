<?php
session_start();
require '../config/connection.php'; // Menghubungkan dengan file connection.php
require '../config/function.php';   // Jika diperlukan, untuk fungsi tambahan

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    // Jika tidak ada session login, redirect ke halaman login
    header("Location: login_admin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$jenishalaman = "Daftar Customer";

// Tentukan jumlah data per halaman
$limit = 10;

// Ambil halaman saat ini dari URL, jika tidak ada set ke 1
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query untuk menghitung total data customer
$total_sql = "SELECT COUNT(*) as total FROM users WHERE jenis_pengguna = 'customer'";  // Memfilter dengan jenis_pengguna = 'customer'
$total_stmt = $GLOBALS['db']->prepare($total_sql);
$total_stmt->execute();
$total_data = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_data / $limit);

// Query sederhana untuk mengambil data customer
$sql = "SELECT * FROM users WHERE jenis_pengguna = 'customer' LIMIT :limit OFFSET :offset";
$stmt = $GLOBALS['db']->prepare($sql);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$n = 0;

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM users WHERE user_id = :user_id";
    $delete_stmt = $GLOBALS['db']->prepare($delete_sql);
    $delete_stmt->bindParam(':user_id', $delete_id, PDO::PARAM_INT);
    if ($delete_stmt->execute()) {
        header("Location: daftar_customer.php"); // Redirect setelah penghapusan
        exit();
    } else {
        echo "<script>alert('Terjadi kesalahan saat menghapus data.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Customer</title>
    <link rel="stylesheet" href="./style/style.css">
    <script>
        function confirmDelete(url) {
            const dialog = document.createElement('div');
            dialog.className = 'dialog-overlay';
            dialog.innerHTML = `
                <div class="confirm-dialog">
                    <h3><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus</h3>
                    <p>Apakah Anda yakin ingin menghapus data customer ini? Tindakan ini tidak dapat dibatalkan.</p>
                    <div class="button-group">
                        <button class="btn btn-cancel" onclick="closeDialog()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button class="btn btn-confirm" onclick="window.location.href='${url}'">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(dialog);
        }

        function closeDialog() {
            const dialog = document.querySelector('.dialog-overlay');
            if (dialog) {
                dialog.remove();
            }
        }
    </script>
</head>

<body>
    <div class="container">

        <?php require "template/sidebar.php"; ?>

        <div class="main-content">

            <?php require "template/header.php"; ?>

            <div class="content">
                <div class="content-header">
                    <div class="content-title">
                        <h3><i class="fas fa-users"></i> Daftar Customer</h3>
                        <p>Kelola data pelanggan JAMADAS</p>
                    </div>
                </div>

                <div class="filter-container">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Cari nama atau email...">
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="table" id="customerTable">
                    <thead>
                        <tr>
                                <th><i class="fas fa-hashtag"></i> No</th>
                                <th><i class="fas fa-user"></i> Nama Lengkap</th>
                                <th><i class="fas fa-image"></i> Foto Profil</th>
                                <th><i class="fas fa-envelope"></i> Email</th>
                                <th><i class="fas fa-phone"></i> Nomor Telepon</th>
                                <th><i class="fas fa-cogs"></i> Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= ++$n; ?></td>
                                <td><?= htmlspecialchars($customer['nama_lengkap']); ?></td>
                                <td>
                                            <div class="profile-image">
                                                <?php if (!empty($customer['profile_image'])): ?>
                                                    <img src="../images/<?= htmlspecialchars($customer['profile_image']); ?>" 
                                                         alt="Profile Image">
                                                <?php else: ?>
                                                    <i class="fas fa-user"></i>
                                                <?php endif; ?>
                                            </div>
                                </td>
                                <td><?= htmlspecialchars($customer['email']); ?></td>
                                <td><?= !empty($customer['nomor_telepon']) ? htmlspecialchars($customer['nomor_telepon']) : '-'; ?></td>
                                <td>
                                            <div class="aksi">
                                                <button onclick="confirmDelete('?delete_id=<?= $customer['user_id']; ?>')" 
                                                        class="btn-delete">
                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                </button>
                                            </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="no-data">
                                        <i class="fas fa-users"></i>
                                        <p>Belum ada data customer</p>
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
                                <a href="?page=<?= $page - 1; ?>">
                                    <i class="fas fa-chevron-left"></i> Prev
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="<?= ($i == $page) ? 'active' : ''; ?>">
                                <a href="?page=<?= $i; ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li>
                                <a href="?page=<?= $page + 1; ?>">
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
            const customerTable = document.getElementById('customerTable');
            const rows = customerTable.getElementsByTagName('tr');

            searchInput.addEventListener('input', function(e) {
                e.preventDefault(); // Mencegah refresh halaman
                const searchTerm = this.value.toLowerCase();
                
                // Mulai dari index 1 untuk melewati header tabel
                for (let i = 1; i < rows.length; i++) {
                    const row = rows[i];
                    const cells = row.getElementsByTagName('td');
                    let found = false;

                    // Cari di kolom nama dan email
                    const namaCell = cells[1]; // Index kolom nama
                    const emailCell = cells[3]; // Index kolom email
                    
                    if (namaCell.textContent.toLowerCase().includes(searchTerm) || 
                        emailCell.textContent.toLowerCase().includes(searchTerm)) {
                        found = true;
                    }

                    // Tampilkan atau sembunyikan baris berdasarkan hasil pencarian
                    row.style.display = found ? '' : 'none';
                }
            });
        });

        function confirmDelete(url) {
            const dialog = document.createElement('div');
            dialog.className = 'dialog-overlay';
            dialog.innerHTML = `
                <div class="confirm-dialog">
                    <h3><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus</h3>
                    <p>Apakah Anda yakin ingin menghapus data customer ini? Tindakan ini tidak dapat dibatalkan.</p>
                    <div class="button-group">
                        <button class="btn btn-cancel" onclick="closeDialog()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button class="btn btn-confirm" onclick="window.location.href='${url}'">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(dialog);
        }

        function closeDialog() {
            const dialog = document.querySelector('.dialog-overlay');
            if (dialog) {
                dialog.remove();
            }
        }
    </script>
</body>
