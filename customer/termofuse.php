<?php
require '../config/connection.php';
require '../config/function.php';

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
    <title>Terms of Use</title>
    <link rel="icon" href="../resources/img/icons/jamadas2.png" type="image/png">
    <link rel="stylesheet" href="../resources/css/dashboard.css">
    <link rel="stylesheet" href="../resources/css/navbar.css">
    <link rel="stylesheet" href="../resources/css/termofuse.css">

</head>

<body>
    <div class="container">
        <!-- Navbar -->
        <nav class="navbar">
            <?php include 'layout/cusmrLayout/navbar.php'; ?>
        </nav>
        <!-- Menampilkan hasil pencarian -->
        <div id="navbarSearchResults" class="search-results">
            <!-- Hasil pencarian akan ditampilkan di sini -->
        </div>

        <!-- Konten Utama -->
        <div class="content">
            <h1>Syarat dan Ketentuan</h1>
            <p>Selamat datang di situs resmi penjualan jamu Madura asli kami. Dengan mengakses dan menggunakan situs ini, Anda menyetujui untuk mematuhi syarat dan ketentuan yang berlaku. Jika Anda tidak setuju dengan syarat ini, mohon untuk tidak melanjutkan penggunaan layanan kami.</p>

            <h2>1. Penggunaan Layanan</h2>
            <p>Layanan kami disediakan untuk memudahkan pelanggan dalam memesan dan membeli produk jamu Madura asli secara online. Produk kami diracik dari bahan-bahan tradisional dan alami. Penggunaan situs ini harus dilakukan secara sah dan tidak boleh digunakan untuk tujuan yang melanggar hukum atau merugikan pihak lain.</p>

            <h2>2. Pendaftaran dan Akun Pengguna</h2>
            <p>Untuk melakukan pembelian, pengguna dapat mendaftar akun dengan memberikan informasi yang benar dan lengkap, termasuk nama, alamat, nomor telepon, dan email. Anda bertanggung jawab atas kerahasiaan akun dan aktivitas di dalamnya. Kami berhak menangguhkan atau menghapus akun pengguna yang melanggar ketentuan ini.</p>

            <h2>3. Pemesanan dan Pembayaran</h2>
            <p>Setiap pemesanan produk jamu harus dilakukan melalui sistem pemesanan yang tersedia dan dibayar menggunakan metode pembayaran resmi yang kami sediakan. Pesanan akan diproses setelah pembayaran terverifikasi. Pembatalan hanya dapat dilakukan sebelum produk dikirimkan. Produk yang telah dibuka tidak dapat dikembalikan dengan alasan apapun, kecuali terdapat kerusakan pada saat pengiriman.</p>

            <h2>4. Hak Kekayaan Intelektual</h2>
            <p>Seluruh konten, logo, resep, nama produk, dan desain kemasan yang terdapat di situs ini merupakan milik eksklusif kami dan dilindungi oleh hukum hak cipta dan kekayaan intelektual. Dilarang keras menggandakan, menjual kembali, atau menyebarluaskan konten tanpa izin tertulis dari kami.</p>

            <h2>5. Kebijakan Privasi</h2>
            <p>Kami menghargai privasi Anda dan hanya mengumpulkan informasi pribadi untuk keperluan transaksi, pengiriman, dan layanan pelanggan. Data Anda tidak akan dibagikan kepada pihak ketiga tanpa persetujuan Anda. Informasi lebih rinci tersedia di halaman Kebijakan Privasi kami.</p>

            <h2>6. Kesehatan dan Penggunaan Produk</h2>
            <p>Produk jamu yang kami jual berbahan alami dan telah digunakan secara tradisional. Namun, hasil bisa berbeda-beda pada tiap individu. Kami menyarankan konsultasi dengan tenaga medis jika Anda memiliki kondisi kesehatan tertentu atau sedang mengonsumsi obat lain sebelum menggunakan jamu.</p>

            <h2>7. Pembatasan Tanggung Jawab</h2>
            <p>Kami tidak bertanggung jawab atas efek samping atau ketidakcocokan produk terhadap pengguna, terutama jika digunakan tidak sesuai petunjuk. Kami juga tidak bertanggung jawab atas keterlambatan pengiriman yang disebabkan oleh pihak jasa ekspedisi.</p>

            <h2>8. Perubahan Ketentuan dan Layanan</h2>
            <p>Kami berhak untuk memperbarui syarat dan ketentuan ini sewaktu-waktu tanpa pemberitahuan sebelumnya. Pengguna disarankan untuk memeriksa halaman ini secara berkala. Perubahan akan berlaku segera setelah dipublikasikan.</p>

            <h2>9. Hukum yang Berlaku</h2>
            <p>Syarat dan ketentuan ini tunduk pada hukum yang berlaku di wilayah Republik Indonesia. Jika terjadi sengketa, akan diselesaikan terlebih dahulu melalui musyawarah. Jika tidak tercapai mufakat, maka akan diselesaikan di pengadilan yang berwenang.</p>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <?php include 'layout/cusmrLayout/footer.php'; ?>
        </footer>
    </div>

    <script src="../resources/js/slides.js"></script>
    <script src="../resources/js/burgersidebar.js"></script>
</body>

</html>