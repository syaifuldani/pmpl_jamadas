<?php
session_start();
// Cek apakah pengguna sudah login
if (isset($_SESSION['user_id'])) {
    // Jika tidak ada session login, redirect ke halaman login
    header("Location: dashboard.php");
    exit();
}
?>
<?php
require '../config/connection.php';
require_once '../config/function.php';

// Live Search
if (isset($_POST['query'])) {
    $searchTerm = $_POST['query'];
    searchProducts($searchTerm);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="icon" href="../resources/img/icons/jamadas.jpg" type="image/png">
    <link rel="stylesheet" href="../resources/css/dashboard.css">
    <link rel="stylesheet" href="../resources/css/navbar.css">
</head>

<body>
    <div class="container">
        <!-- Navbar -->
        <nav class="navbar">
            <?php include 'layout/cusmrLayout/navbar.php'; ?>
        </nav>
        <!-- Menampilkan hasil pencarian -->
        <div id="navbarSearchResults" class="search-results"></div>

        <!-- Hero Section -->
        <section class="hero animate-slide-left">
            <h1 class="animate-fade-in animate-delay-1">Selamat datang di layanan Jamu Madura Online!</h1>
            <p class="animate-fade-in animate-delay-2">
                Kami menyediakan berbagai pilihan jamu Madura asli yang siap Anda pesan untuk menjaga kesehatan dan kebugaran Anda.
                Mulai dari jamu untuk vitalitas, kecantikan, hingga kesehatan harian, semua terbuat dari bahan alami pilihan khas Madura.
                Pilih produk favorit Anda, tambahkan ke keranjang, dan biarkan kami mengirimkannya langsung ke alamat Anda.
                Proses mudah, hasil menyehatkan!
            </p>
        </section>

        <!-- Search Section -->
        <div class="section-search">
            <section class="search animate-slide-right">
                <h2 class="animate-fade-in animate-delay-1">
                    Temukan Beragam Jamu Madura Asli & Berkualitas
                </h2>
                <p class="animate-fade-in animate-delay-2">
                    Pesan Sekarang, Jamu Madura Siap Dikirim ke Rumah Anda
                <!-- </p>
                <form action="" method="POST" class="search-input animate-slide-right animate-delay-3">
                    <label><img src="../resources/img/icons/search.png" alt=""></label>
                    <input type="text" id="contentSearchBox" name="query" placeholder="Cari Jamu Madura Anda"
                        value="<?= isset($_POST['query']) ? htmlspecialchars($_POST['query']) : '' ?>">
                </form>
                <div id="contentSearchResults" class="search-results animate-slide-bottom animate-delay-4">
                </div> -->
            </section>
            <div class="image animate-slide-left animate-delay-3">
                <img src="../resources/img/introduction/image6.png" alt="Jamu Madura">
            </div>
        </div>

        <!-- Product Section -->
        <section class="products animate-slide-bottom">
            <h2 class="animate-fade-in animate-delay-1">
                Pesan Jamu Madura Asli dengan Mudah!
            </h2>
            <div class="product-grid">
                <div class="product-card-dsbrd animate-slide-top animate-delay-2">
                    <a href="jamu_vitalitas.php">
                        <img src="../resources/img/homeimg/jamu_vitalitas.jpeg" alt="Jamu Vitalitas">
                        <p>Jamu Vitalitas</p>
                    </a>
                </div>
                <div class="product-card-dsbrd animate-slide-top animate-delay-3">
                    <a href="jamu_kecantikan.php">
                        <img src="../resources/img/homeimg/jamu_kecantikan.jpg" alt="Jamu Kecantikan">
                        <p>Jamu Kecantikan</p>
                    </a>
                </div>
                <div class="product-card-dsbrd animate-slide-top animate-delay-4">
                    <a href="jamu_kesehatan.php">
                        <img src="../resources/img/homeimg/jamu_kewanitaan.jpg" alt="Jamu Kewanitaan">
                        <p>Jamu Kewanitaan</p>
                    </a>
                </div>
                <div class="product-card-dsbrd animate-slide-top animate-delay-5">
                    <a href="jamu_herbal.php">
                        <img src="../resources/img/homeimg/jamu_pelangsing.jpg" alt="Jamu Pelangsing">
                        <p>Jamu Pelangsing</p>
                    </a>
                </div>
                <div class="product-card-dsbrd animate-slide-top animate-delay-6">
                    <a href="jamu_lainnya.php">
                        <img src="../resources/img/homeimg/jamu_lainnya.jpeg" alt="Jamu Lainnya">
                        <p>Jamu Lainnya</p>
                    </a>
                </div>
            </div>
        </section>

        <div class="layout-wrapper animate-slide-right animate-delay-3">
            <div class="instructions">
                <div class="step animate-slide-left animate-delay-1">
                    <img src="../resources/img/icons/checkaction.png" alt="Pilih Jamu" class="icon">
                    <div class="text">
                        <h3>Pilih Jamu</h3>
                        <p>Mulai pesanan Anda dengan memilih jamu Madura yang sesuai kebutuhan dari berbagai produk asli yang kami sediakan.</p>
                    </div>
                </div>
                <div class="step animate-slide-left animate-delay-1">
                    <img src="../resources/img/icons/cartaction.png" alt="Tambahkan ke Keranjang" class="icon">
                    <div class="text">
                        <h3>Tambahkan ke Keranjang</h3>
                        <p>Isi detail pemesanan, seperti jumlah, alamat pengiriman, dan catatan khusus jika ada. Pastikan data Anda benar sebelum checkout.</p>
                    </div>
                </div>
                <div class="step animate-slide-left animate-delay-1">
                    <img src="../resources/img/icons/payaction.png" alt="Pilih Metode Pembayaran" class="icon">
                    <div class="text">
                        <h3>Pilih Metode Pembayaran</h3>
                        <p>Pilih metode pembayaran yang Anda inginkan dan tunggu jamu Madura pesanan Anda sampai di rumah.</p>
                    </div>
                </div>
            </div>
            <div class="preview slide-in-bottom">
                <img src="../resources/img/introduction/image2.png" alt="Preview Jamu Madura" class="preview-image">
            </div>
        </div>
    </div>

    <!-- Footers Promotions -->
    <footer class="footer animate-slide-top animate-delay-2">
        <?php include 'layout/cusmrLayout/footer.php'; ?>
    </footer>
    </div>

    <script src="../resources/js/burgersidebar.js"></script>
    <script src="../resources/js/livesearch.js"></script>
</body>

</html>