<?php
require '../config/connection.php';
require_once '../config/function.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="logo">
        <img src="../resources/img/icons/jamadas.jpg" alt="Logo" class="logo-image">
        <p>si<b>JAMADAS</b></p>
    </div>

    <div class="center-items">
        <ul class="nav-links" id="nav-links">
            <div class="search-bar">
                <form action="" method="POST" class="search-input">
                    <label><img src="../resources/img/icons/search.png" alt="search"></label>
                    <input type="text" id="navbarSearchBox" name="query" placeholder="Cari jamu..."
                        value="<?= isset($_POST['query']) ? htmlspecialchars($_POST['query']) : '' ?>">
                </form>
            </div>
            <li><a href="dashboard.php" class="links">Home</a></li>
            <li><a href="product.php" class="links">Jamu</a></li>
            <!-- <li class="dropdown">
                <button class="dropbtn">
                    Pilih Jamu
                </button>
                <div class="dropdown-content">
                    <a href="undangan_pernikahan.php">
                        <img src="../resources/img/icons/paper.png" alt="dropdown">
                        Jamu Kesehatan dan Vitalitas
                    </a>
                    <a href="undangan_khitanan.php">
                        <img src="../resources/img/icons/paper.png" alt="dropdown">
                        Jamu Kewanitaan
                    </a>
                    <a href="undangan_walimatul.php">
                        <img src="../resources/img/icons/paper.png" alt="dropdown">
                        Jamu Pelangsing dan Pencernaan
                    </a>
                    <a href="undangan_tahlilkirimdoa.php">
                        <img src="../resources/img/icons/paper.png" alt="dropdown">
                        Jamu Kecantikan
                    </a>
                    <a href="undangan_ulangtahun.php">
                        <img src="../resources/img/icons/paper.png" alt="dropdown">
                        Jamu Perawatan Pasca Melahirkan
                    </a>
                </div>
            </li> -->
            <li><a href="services/aboutus.php" class="links">About Us</a></li>
            <li><a href="services/contact.php" class="links">Contact</a></li>
        </ul>
    </div>

    <div class="user-options">
        <!-- Tombol untuk membuka dropdown -->
        <a href="#" class="cart" id="cartButton">
            <img src="../resources/img/icons/shoppingcart.png" alt="Cart">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                // Ambil item keranjang untuk pengguna yang sudah login
                $cartItems = getCartItems($_SESSION['user_id']);
                $itemCount = count($cartItems); // Hitung jumlah item di keranjang
                ?>
                <span class="cart-count" id="cart-count"
                    style="<?= $itemCount > 0 ? 'display: inline' : 'display: none'; ?>">
                    <?= $itemCount; ?>
                </span>
            <?php else: ?>
                <span class="cart-count" id="cart-count" style="display: none;"></span>
            <?php endif; ?>
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- If user is logged in, show profile icon -->
            <div class="customer-dropdown">
                <img src="<?= isset($_SESSION['user_profile']) ? $_SESSION['user_profile'] : '../resources/img/profiledefault.png' ?>"
                    alt="Profile" class="profile-photo dropdown-toggle">
                <!-- <img src="</?= isset($_SESSION['user_profile']) ? $_SESSION['user_profile'] : '../resources/img/profiledefault.png' ?>"
            alt="Profile" class="profile-photo dropdown-toggle"> -->
                <ul class="dropdown-menu">
                    <!-- Add user info (name and email) here -->
                    <li class="user-info">
                        <strong><?= $_SESSION['user_name']; ?></strong>
                        <small><?= $_SESSION['user_email']; ?></small>
                    </li>

                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="pesanan_saya.php">Pesanan Saya</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>

        <?php else: ?>
            <!-- If user is not logged in, show Sign in and Register links -->
            <a href="login.php" class="sign-in">Login</a>
            <a href="register.php" class="register">Register</a>
        <?php endif; ?>
    </div>


    <div class="cart-dropdown" id="cartDropdown" style="display: none;">
        <h3>Keranjang</h3>

        <div class="cart-items-container">
            <?php
            // Cek apakah pengguna sudah login
            if (isset($_SESSION['user_id'])) {
                // Panggil fungsi untuk mendapatkan item keranjang
                $cartItems = getCartItems($_SESSION['user_id']);

                // Cek apakah ada item di keranjang
                if (!empty($cartItems)) {
                    // Tampilkan setiap item di keranjang
                    foreach ($cartItems as $item) {
                        ?>
                        <div class="cart-item">
                            <img src="<?= $item['gambar_satu'] ?>" alt="Product" class="cart-item-image">
                            <div class="item-details">
                                <h4 class="item-name"><?= $item['nama_produk'] ?></h4>
                                <p class="item-qty">Qty: <?= $item['jumlah'] ?></p>
                                <p class="item-price">Rp. <?= number_format($item['harga_produk'], 2, ',', '.') ?></p>
                            </div>
                        </div>
                        <?php
                    }

                    // Menghitung total harga
                    $totalHarga = 0;
                    foreach ($cartItems as $item) {
                        $totalHarga += $item['harga_produk'] * $item['jumlah']; // Asumsikan harga_produk adalah per item
                    }
                } else {
                    // Jika tidak ada item di keranjang
                    echo '<p class="empty-cart-message">Keranjang kosong.</p>';
                }
            } else {
                // Pesan untuk pengguna yang belum login
                echo '<p class="login-prompt">Silahkan Login Terlebih Dahulu!</p>';
            }
            ?>
        </div>

        <?php if (!empty($cartItems)) { ?>
            <p class="total-price">Total Harga: Rp. <?= number_format($totalHarga, 2, ',', '.') ?></p>
        <?php } ?>

        <div class="cart-btn">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (!empty($cartItems)): ?>
                    <a href="cart.php" class="cart-btn active">
                        Check Out
                    </a>
                <?php else: ?>
                    <span class="cart-btn disabled" style="cursor: not-allowed; opacity: 0.6;">
                        Check Out
                    </span>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="cart-btn">
                    Login
                </a>
            <?php endif; ?>
        </div>
    </div>

    <button class="burger" id="burger" aria-label="Toggle navigation menu">☰</button>

    <!-- JavaScript -->
    <script>
        // Mengambil elemen tombol keranjang, dropdown, dan count
        const cartButton = document.getElementById('cartButton');
        const cartDropdown = document.getElementById('cartDropdown');
        const cartCount = document.querySelector('.cart-count');

        // Fungsi untuk menampilkan atau menyembunyikan dropdown keranjang
        function toggleCartDropdown() {
            if (cartDropdown.style.display === 'none' || cartDropdown.style.display === '') {
                cartDropdown.style.display = 'block';
            } else {
                cartDropdown.style.display = 'none';
            }
        }

        // Event listener pada tombol keranjang
        cartButton.addEventListener('click', function (event) {
            event.preventDefault(); // Mencegah link agar tidak langsung mengarahkan ke URL lain
            toggleCartDropdown(); // Memanggil fungsi untuk menampilkan atau menyembunyikan dropdown
        });

        // Event listener untuk menutup dropdown jika klik di luar area dropdown
        document.addEventListener('click', function (event) {
            if (!cartButton.contains(event.target) && !cartDropdown.contains(event.target)) {
                cartDropdown.style.display = 'none'; // Sembunyikan dropdown
            }
        });

        // Profile Dropdown
        const profileToggle = document.querySelector('.profile-photo');
        const profileDropdown = document.querySelector('.dropdown-menu');

        // Fungsi untuk menampilkan atau menyembunyikan profile dropdown
        function toggleProfileDropdown() {
            if (profileDropdown.style.display === 'none' || profileDropdown.style.display === '') {
                profileDropdown.style.display = 'block';
            } else {
                profileDropdown.style.display = 'none';
            }
        }

        // Event listener pada profile photo
        profileToggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            toggleProfileDropdown();
        });

        // Event listener untuk menutup profile dropdown jika klik di luar
        document.addEventListener('click', function (event) {
            if (!profileToggle.contains(event.target) && !profileDropdown.contains(event.target)) {
                profileDropdown.style.display = 'none';
            }
        });
    </script>
    <script src="../resources/js/livesearch.js"></script>
    <script src="../resources/js/burgersidebar.js"></script>
</body>

</html>