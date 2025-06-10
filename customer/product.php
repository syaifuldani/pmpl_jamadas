<?php
session_start();

require '../config/connection.php';
require '../config/function.php';

// Inisialisasi variabel cartItems
$cartItems = [];

// Cek apakah pengguna sudah login
if (isset($_SESSION['user_id'])) {
    // Ambil item keranjang dari database
    $cartItems = getCartItems($_SESSION['user_id']);
}

// Ambil semua kategori
$query_kategori = "SELECT DISTINCT kategori FROM products";
$stmt_kategori = $GLOBALS['db']->prepare($query_kategori);
$stmt_kategori->execute();
$result_kategori = $stmt_kategori->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua sub kategori
$query_subkategori = "SELECT DISTINCT sub_kategori FROM products WHERE sub_kategori IS NOT NULL AND sub_kategori != ''";
$stmt_subkategori = $GLOBALS['db']->prepare($query_subkategori);
$stmt_subkategori->execute();
$result_subkategori = $stmt_subkategori->fetchAll(PDO::FETCH_ASSOC);

// Filter produk berdasarkan kategori
$selected_kategori = $_GET['kategori'] ?? 'all';
$selected_subkategori = $_GET['sub_kategori'] ?? 'all';

if ($selected_kategori === 'all' && $selected_subkategori === 'all') {
    $query_produk = "SELECT * FROM products";
    $stmt_produk = $GLOBALS['db']->prepare($query_produk);
} elseif ($selected_kategori !== 'all' && $selected_subkategori === 'all') {
    $query_produk = "SELECT * FROM products WHERE kategori = :kategori";
    $stmt_produk = $GLOBALS['db']->prepare($query_produk);
    $stmt_produk->bindParam(':kategori', $selected_kategori);
} elseif ($selected_kategori === 'all' && $selected_subkategori !== 'all') {
    $query_produk = "SELECT * FROM products WHERE sub_kategori = :sub_kategori";
    $stmt_produk = $GLOBALS['db']->prepare($query_produk);
    $stmt_produk->bindParam(':sub_kategori', $selected_subkategori);
} else {
    $query_produk = "SELECT * FROM products WHERE kategori = :kategori AND sub_kategori = :sub_kategori";
    $stmt_produk = $GLOBALS['db']->prepare($query_produk);
    $stmt_produk->bindParam(':kategori', $selected_kategori);
    $stmt_produk->bindParam(':sub_kategori', $selected_subkategori);
}

$stmt_produk->execute();
$produk = $stmt_produk->fetchAll(PDO::FETCH_ASSOC);

$stmt_produk->execute();
$result_produk = $stmt_produk->fetchAll(PDO::FETCH_ASSOC);

// Live Search
if (isset($_POST['query'])) {
    $searchTerm = $_POST['query'];
    searchProducts($searchTerm);
}
?>

<!DOCTYPE html>
<html lang="en">

<head></head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produk Jamu Madura</title>
<link rel="icon" href="../resources/img/icons/jamadas2.png" type="image/png">
<link rel="stylesheet" href="../resources/css/navbar.css">
<link rel="stylesheet" href="../resources/css/dashboard.css">
<style>
    .product-container {
        display: flex;
        margin-top: 60px;
        padding: 20px;
        position: relative;
    }

    .sidebar-kategori {
        width: 280px;
        padding: 20px;
        border-radius: 15px;
        height: calc(100vh - 100px);
        position: fixed;
        left: 20px;
        z-index: 1;
        background: #ffffff;
        border: 1px solid #77dd77;
        transition: all 0.3s ease;
    }

    .sidebar-kategori:hover {
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
    }

    .sidebar-kategori h3 {
        color: #2c3e50;
        margin-bottom: 25px;
        padding-top: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #77dd77;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .sidebar-kategori h3 i {
        color: #77dd77;
        font-size: 1.3rem;
    }

    .kategori-dropdown {
        position: relative;
        margin-bottom: 25px;
    }

    .kategori-select {
        width: 100%;
        padding: 14px 18px;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        background-color: #f8f9fa;
        font-size: 14px;
        color: #2c3e50;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2377dd77' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 15px;
        transition: all 0.3s ease;
    }

    .kategori-select:hover {
        border-color: #77dd77;
        background-color: #ffffff;
    }

    .kategori-select:focus {
        outline: none;
        border-color: #77dd77;
        box-shadow: 0 0 0 3px rgba(119, 221, 119, 0.1);
    }

    .kategori-select option {
        color: #666;
        background-color: #fff;
        padding: 10px;
    }

    .kategori-select option:hover {
        color: #77dd77;
    }

    .kategori-select option:checked {
        color: #77dd77;
        font-weight: 500;
    }

    .sub-kategori {
        margin-top: 25px;
        position: relative;
    }

    .sub-kategori-select {
        width: 100%;
        padding: 14px 18px;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        background-color: #f8f9fa;
        font-size: 14px;
        color: #2c3e50;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2377dd77' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 15px;
        transition: all 0.3s ease;
    }

    .sub-kategori-select:hover {
        border-color: #77dd77;
        background-color: #ffffff;
    }

    .sub-kategori-select:focus {
        outline: none;
        border-color: #77dd77;
        box-shadow: 0 0 0 3px rgba(119, 221, 119, 0.1);
    }

    .reset-filter {
        margin-top: 25px;
        width: 100%;
        padding: 14px 18px;
        background-color: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        font-size: 14px;
        color: #2c3e50;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-weight: 500;
    }

    .reset-filter:hover {
        background-color: #fff5f5;
        border-color: #dc3545;
        color: #dc3545;
    }

    .reset-filter i {
        color: #dc3545;
        font-size: 1.1rem;
    }

    .product-content {
        flex: 1;
        max-width: 1200px;
        margin-left: 290px;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }

    .cart-icon {
        width: 16px;
        height: 16px;
    }

    @media (max-width: 1024px) {
        .product-container {
            flex-direction: column;
        }

        .sidebar-kategori {
            width: 100%;
            height: auto;
            position: relative;
            margin-bottom: 30px;
            left: 0;
            padding: 15px;
        }

        .kategori-dropdown {
            margin-bottom: 15px;
        }

        .product-content {
            margin-left: 0;
            margin-right: 0;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .product-card {
            max-width: 100%;
        }
    }

    @media (max-width: 768px) {
        .product-content {
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .product-card {
            padding: 8px;
        }

        .product-card img {
            max-height: 160px;
        }

        .product-card .description {
            min-height: 80px;
            max-height: 80px;
            padding: 8px;
        }

        .product-card .description p {
            font-size: 11px;
        }

        .product-name {
            font-size: 13px;
        }

        .detail-button p {
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .product-content {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .product-card {
            padding: 6px;
        }

        .product-card img {
            max-height: 140px;
        }

        .product-card .description {
            min-height: 70px;
            max-height: 70px;
            padding: 6px;
        }

        .product-card .description p {
            font-size: 10px;
        }

        .product-name {
            font-size: 12px;
        }

        .detail-button p {
            font-size: 11px;
        }
    }

    .sub-kategori-list {
        max-height: 230px;
        overflow-y: auto;
        margin-top: 15px;
        padding-right: 10px;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        background-color: #f8f9fa;
    }

    .sub-kategori-list::-webkit-scrollbar {
        width: 6px;
    }

    .sub-kategori-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .sub-kategori-list::-webkit-scrollbar-thumb {
        background: #77dd77;
        border-radius: 10px;
    }

    .sub-kategori-list::-webkit-scrollbar-thumb:hover {
        background: #6eca6e;
    }

    .sub-kategori-item {
        display: block;
        padding: 10px 15px;
        color: #2c3e50;
        text-decoration: none;
        font-size: 14px;
        border-bottom: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }

    .sub-kategori-item:last-child {
        border-bottom: none;
    }

    .sub-kategori-item:hover {
        background-color: #ffffff;
        color: #77dd77;
        padding-left: 20px;
    }

    .sub-kategori-item.active {
        background-color: #77dd77;
        color: white;
        font-weight: 500;
    }

    .sub-kategori-item.active:hover {
        background-color: #6eca6e;
        color: white;
    }

    .product-card {
        background-color: white;
        padding: 10px;
        border-radius: 10px;
        border: 1px solid #77dd77;
        transition: transform 0.3s ease;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .product-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .product-card .description {
        background-color: white;
        padding: 10px;
        text-align: left;
        min-height: 80px;
        max-height: 80px;
        overflow-x: hidden;
        word-wrap: break-word;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .product-card .description p {
        margin: 0;
        font-size: 12px;
        color: #666;
    }

    .product-card .product-info {
        margin-top: auto;
        padding: 10px;
        background-color: #ffffff;
        border-radius: 8px;
    }

    .product-name {
        font-weight: 600;
        font-size: 14px;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .product-price {
        color: #77dd77;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .detail-button {
        border: 1px solid #77dd77;
        color: #77dd77;
        text-decoration: none;
        padding: 8px;
        border-radius: 5px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        width: 100%;
    }

    .detail-button:hover {
        background-color: #77dd77;
        color: white;
    }

    .detail-button p {
        margin: 0;
        font-size: 14px;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .product-card {
            padding: 8px;
        }

        .product-card img {
            height: 160px;
            margin-bottom: 8px;
        }

        .product-card .description {
            min-height: 70px;
            max-height: 70px;
            padding: 8px;
            margin-bottom: 8px;
        }

        .product-card .description p {
            font-size: 11px;
        }

        .product-card .product-info {
            padding: 8px;
        }

        .product-name {
            font-size: 13px;
            margin-bottom: 4px;
        }

        .product-price {
            font-size: 13px;
            margin-bottom: 8px;
        }

        .detail-button {
            padding: 6px;
        }

        .detail-button p {
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .product-card {
            padding: 6px;
        }

        .product-card img {
            height: 140px;
            margin-bottom: 6px;
        }

        .product-card .description {
            min-height: 60px;
            max-height: 60px;
            padding: 6px;
            margin-bottom: 6px;
        }

        .product-card .description p {
            font-size: 10px;
        }

        .product-card .product-info {
            padding: 6px;
        }

        .product-name {
            font-size: 12px;
            margin-bottom: 3px;
        }

        .product-price {
            font-size: 12px;
            margin-bottom: 6px;
        }

        .detail-button {
            padding: 5px;
        }

        .detail-button p {
            font-size: 11px;
        }
    }
</style>
<link rel="stylesheet" href="../resources/css/chat.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <?php include 'layout/cusmrLayout/navbar.php'; ?>
    </nav>
    <div id="navbarSearchResults" class="search-results"></div>

    <div class="product-container">
        <!-- Sidebar Kategori -->
        <div class="sidebar-kategori">
            <h3><i class="fas fa-filter"></i> Filter Produk</h3>

            <div class="kategori-dropdown">
                <select class="kategori-select" onchange="window.location.href=this.value">
                    <option value="?kategori=all&sub_kategori=<?= urlencode($selected_subkategori) ?>"
                        <?= $selected_kategori === 'all' ? 'selected' : '' ?>>
                        Semua Kategori
                    </option>
                    <?php foreach ($result_kategori as $kategori): ?>
                        <option
                            value="?kategori=<?= urlencode($kategori['kategori']) ?>&sub_kategori=<?= urlencode($selected_subkategori) ?>"
                            <?= $selected_kategori === $kategori['kategori'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kategori['kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <h3><i class="fas fa-tags"></i> Sub Kategori</h3>
            <div class="sub-kategori-list">
                <a href="?kategori=<?= urlencode($selected_kategori) ?>&sub_kategori=all"
                    class="sub-kategori-item <?= $selected_subkategori === 'all' ? 'active' : '' ?>">
                    Semua Sub Kategori
                </a>
                <?php
                $regular_subkategori = [];
                $lainnya_subkategori = [];

                foreach ($result_subkategori as $sub) {
                    if (strtolower($sub['sub_kategori']) === 'lainnya' || strtolower($sub['sub_kategori']) === 'lain-lain') {
                        $lainnya_subkategori[] = $sub;
                    } else {
                        $regular_subkategori[] = $sub;
                    }
                }

                // Tampilkan sub kategori regular
                foreach ($regular_subkategori as $sub): ?>
                    <a href="?kategori=<?= urlencode($selected_kategori) ?>&sub_kategori=<?= urlencode($sub['sub_kategori']) ?>"
                        class="sub-kategori-item <?= $selected_subkategori === $sub['sub_kategori'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($sub['sub_kategori']) ?>
                    </a>
                <?php endforeach;

                // Tambahkan pemisah jika ada sub kategori "Lainnya"
                if (!empty($lainnya_subkategori)): ?>
                    <div class="sub-kategori-divider"></div>
                <?php endif;

                // Tampilkan sub kategori "Lainnya"
                foreach ($lainnya_subkategori as $sub): ?>
                    <a href="?kategori=<?= urlencode($selected_kategori) ?>&sub_kategori=<?= urlencode($sub['sub_kategori']) ?>"
                        class="sub-kategori-item lainnya <?= $selected_subkategori === $sub['sub_kategori'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($sub['sub_kategori']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <button class="reset-filter" onclick="window.location.href='?kategori=all&sub_kategori=all'">
                <i class="fas fa-sync-alt"></i>
                Reset Filter
            </button>
        </div>


        <!-- Items Product -->
        <div class="product-content">
            <?php if (empty($result_produk)): ?>
                <div class="no-products">
                    <p>Tidak ada produk dalam kategori ini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($result_produk as $produk): ?>
                    <div class="product-card">
                        <img class="product" src="data:image/jpeg;base64,<?= base64_encode($produk['gambar_satu']) ?>"
                            alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
                        <div class="description">
                            <p><?= htmlspecialchars($produk['deskripsi']) ?></p>
                        </div>
                        <div class="product-info">
                            <p class="product-name"><?= htmlspecialchars($produk['nama_produk']) ?></p>
                            <p class="product-price">Rp. <?= number_format($produk['harga_produk'], 0, ',', '.') ?></p>
                            <a href="productdetail.php?id=<?= $produk['product_id'] ?>" class="detail-button">
                                <p>Lihat Detail</p>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>    <!-- Chatbot -->
    <div class="chat-toggle" onclick="toggleChat()">
        <img src="https://cdn-icons-png.flaticon.com/512/4712/4712027.png" alt="Chat" width="30" height="30">
    </div><div class="chat-container">
        <div class="chat-header">
            <img src="https://cdn-icons-png.flaticon.com/512/4712/4712027.png" alt="Bot Avatar">
            <h3>Asisten Jamu</h3>
        </div>
        <div class="chat-box" id="chatMessages"></div>
        <div class="chat-input">
            <input type="text" placeholder="Tanyakan tentang jamu..." id="chat-input">
            <button onclick="sendMessage()">Kirim</button>
        </div>
    </div>    <script>
        // Chat functionality is handled by chat.js
        // Make sure chat toggle has click event listener
        document.addEventListener('DOMContentLoaded', function() {
            const chatToggle = document.querySelector('.chat-toggle');
            if (chatToggle) {
                // Only add event listener if it doesn't already have onclick
                if (!chatToggle.onclick) {
                    chatToggle.addEventListener('click', function() {
                        toggleChat();
                    });
                }
            }
        });
    </script>
    <script src="../resources/js/burgersidebar.js"></script>
    <script src="../resources/js/livesearch.js"></script>
    <script src="../resources/js/chat.js"></script>
    <link rel="stylesheet" href="../resources/css/chat.css">
</body>

</html>