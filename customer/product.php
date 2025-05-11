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

// Filter produk berdasarkan kategori
$selected_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'all';

if ($selected_kategori === 'all') {
    // Ambil semua produk jika tidak ada kategori yang dipilih
    $query_produk = "SELECT product_id, nama_produk, deskripsi, harga_produk, gambar_satu, kategori FROM products";
    $stmt_produk = $GLOBALS['db']->prepare($query_produk);
} else {
    // Ambil produk berdasarkan kategori yang dipilih
    $query_produk = "SELECT product_id, nama_produk, deskripsi, harga_produk, gambar_satu, kategori 
                     FROM products 
                     WHERE kategori = :kategori";
    $stmt_produk = $GLOBALS['db']->prepare($query_produk);
    $stmt_produk->bindParam(':kategori', $selected_kategori);
}

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
    <link rel="icon" href="../resources/img/icons/jamadas.jpg" type="image/png">
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
            width: 250px;
            /* background-color: #f8f9fa; */
            padding: 5px;
            border-radius: 5px;
            /* box-shadow: 0 2px 5px rgba(0,0,0,0.1); */
            height: calc(100vh - 100px);
            position: fixed;
            left: 20px;
            z-index: 1;
        }

        .sidebar-kategori h3 {
            color: #333;
            margin-bottom: 20px;
            padding-top: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #77dd77;
        }

        .kategori-list {
            list-style: none;
            padding: 0;
        }

        .kategori-list li {
            margin-bottom: 10px;
        }

        .kategori-list a {
            display: block;
            padding: 3px;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .kategori-list a:hover,
        .kategori-list a.active {
            color: #77dd77;
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

        .detail-button {
            background-color: #77dd77 !important;
            color: white !important;
            text-decoration: none;
            padding: 5px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .detail-button:hover {
            background-color: #6eca6e !important;
        }

        .detail-button p {
            margin: 0;
            font-size: 14px;
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
                margin-bottom: 20px;
                left: 0;
            }

            .product-content {
                margin-left: 0;
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

        .sub-kategori {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .sub-kategori-btn {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: left;
            font-size: 14px;
            color: #333;
        }

        .sub-kategori-btn:hover {
            background-color: #77dd77;
            color: white;
            border-color: #77dd77;
        }

        .sub-kategori-btn.active {
            background-color: #77dd77;
            color: white;
            border-color: #77dd77;
        }

        @media (max-width: 1024px) {
            .sub-kategori {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
            }

            .sub-kategori-btn {
                flex: 1;
                min-width: 120px;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .sub-kategori {
                flex-direction: column;
            }

            .sub-kategori-btn {
                width: 100%;
            }
        }
    </style>
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
            <h3>Kategori Jamu</h3>
            <ul class="kategori-list">
                <li>
                    <a href="?kategori=all" class="<?= $selected_kategori === 'all' ? 'active' : '' ?>">
                        Semua Kategori
                    </a>
                </li>
                <?php foreach($result_kategori as $kategori): ?>
                    <li>
                        <a href="?kategori=<?= urlencode($kategori['kategori']) ?>" class="<?= $selected_kategori === $kategori['kategori'] ? 'active' : '' ?>">
                            <?= htmlspecialchars($kategori['kategori']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <h3>Sub Kategori</h3>
            <div class="sub-kategori">
                <button class="sub-kategori-btn <?= $selected_kategori === 'Jamu Cair' ? 'active' : '' ?>" 
                        onclick="window.location.href='?kategori=Jamu Cair'">
                    Jamu Cair
                </button>
                <button class="sub-kategori-btn <?= $selected_kategori === 'Jamu Bubuk' ? 'active' : '' ?>" 
                        onclick="window.location.href='?kategori=Jamu Bubuk'">
                    Jamu Bubuk
                </button>
                <button class="sub-kategori-btn <?= $selected_kategori === 'Lainnya' ? 'active' : '' ?>" 
                        onclick="window.location.href='?kategori=Lainnya'">
                    Lainnya
                </button>
            </div>
        </div>

        <!-- Items Product -->
        <div class="product-content">
            <?php if (empty($result_produk)): ?>
                <div class="no-products">
                    <p>Tidak ada produk dalam kategori ini.</p>
                </div>
            <?php else: ?>
                <?php foreach($result_produk as $produk): ?>
                    <div class="product-card">
                        <img class="product" src="data:image/jpeg;base64,<?= base64_encode($produk['gambar_satu']) ?>" 
                            alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
                        <p class="product-name"><?= htmlspecialchars($produk['nama_produk']) ?></p>
                        <div class="description">
                            <h5>Deskripsi Produk</h5>
                            <p><?= htmlspecialchars($produk['deskripsi']) ?></p>
                        </div>
                        <p class="product-price">Rp. <?= number_format($produk['harga_produk'], 0, ',', '.') ?></p>
                        <a href="productdetail.php?id=<?= $produk['product_id'] ?>" class="detail-button">
                            <!-- <img class="cart-icon" src="../resources/img/icons/cart.png" alt=""> -->
                            <p>Lihat Detail</p>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 