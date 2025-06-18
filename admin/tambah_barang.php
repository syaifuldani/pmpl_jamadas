<?php
session_start();
// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['jenis_pengguna'] != 'admin') {
    // Jika tidak ada session login, redirect ke halaman login
    header("Location: login_admin.php");
    exit();
}
require '../config/connection.php';
require '../config/function.php';

$title = "Jamadas";
$jenishalaman = "Tambah Barang";
$user_email = $_SESSION['user_email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $responseAddItems = addItemsToProduct($_POST);

    if (isset($responseAddItems['status']) && $responseAddItems['status'] === 'success') {
        $success_message = $responseAddItems['message'];
        header("Location: product.php");
        exit();
    } else {
        // If errors exist, handle them
        $errors = $responseAddItems;
    }
}

?>

<!DOCTYPE html>
<html lang="id">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang - PleeART</title>
    <link rel="stylesheet" href="./style/style.css">
    <link rel="stylesheet" href="../node_modules/sweetalert2/dist/sweetalert2.min.css">
    <style>
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>


<body>
    <div class="container">

        <?php require "./template/sidebar.php" ?>

        <main class="main-content">
            <?php require "template/header.php"; ?>

            <section class="product-detail">
                <form action="" method="POST" enctype="multipart/form-data">
                    <!-- Nama Produk -->
                    <div class="form-group">
                        <label for="product-name">
                            <i class="fas fa-box"></i> Nama Produk
                        </label>
                        <input type="text" id="product-name" name="product_name" placeholder="Masukkan nama produk">
                        <span
                            class="error-message"><?= isset($responseAddItems['field']) ? $responseAddItems['field'] : ''; ?></span>
                    </div>

                    <!-- Deskripsi -->
                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-align-left"></i> Deskripsi
                        </label>
                        <textarea id="description" name="description"
                            placeholder="Masukkan deskripsi produk"></textarea>
                        <span
                            class="error-message"><?= isset($responseAddItems['field']) ? $responseAddItems['field'] : ''; ?></span>
                    </div>

                    <!-- Manfaat -->
                    <div class="form-group">
                        <label for="manfaat">
                            <i class="fas fa-star"></i> Manfaat
                        </label>
                        <textarea id="manfaat" name="manfaat" placeholder="Masukkan manfaat produk"></textarea>
                        <span
                            class="error-message"><?= isset($responseAddItems['field']) ? $responseAddItems['field'] : ''; ?></span>
                    </div>

                    <!-- Komposisi -->
                    <div class="form-group">
                        <label for="komposisi">
                            <i class="fas fa-flask"></i> Komposisi
                        </label>
                        <textarea id="komposisi" name="komposisi" placeholder="Masukkan komposisi produk"></textarea>
                        <span
                            class="error-message"><?= isset($responseAddItems['field']) ? $responseAddItems['field'] : ''; ?></span>
                    </div>

                    <!-- Kategori dan Sub Kategori -->
                    <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label for="category">
                                <i class="fas fa-tags"></i> Kategori
                            </label>
                            <select id="category" name="category">
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                <option value="Perawatan Kecantikan dan Tubuh">Perawatan Kecantikan dan Tubuh</option>
                                <option value="Reproduksi Wanita">Reproduksi Wanita</option>
                                <option value="Vitalitas Pria">Vitalitas Pria</option>
                                <option value="Kesehatan Pencernaan">Kesehatan Pencernaan</option>
                                <option value="Kesehatan Umum & Imunitas">Kesehatan Umum & Imunitas</option>
                                <option value="Kesehatan Tulang & Sendi">Kesehatan Tulang & Sendi</option>
                                <option value="Kesehatan Anak">Kesehatan Anak</option>
                                <option value="Kesehatan Lansia">Kesehatan Lansia</option>
                                <option value="Kesehatan Mata">Kesehatan Mata</option>
                                <option value="Kesehatan Jantung">Kesehatan Jantung</option>
                                <option value="Kesehatan Mental & Relaksasi">Kesehatan Mental & Relaksasi</option>
                            </select>
                            <span
                                class="error-message"><?= isset($responseAddItems['category']) ? $responseAddItems['category'] : ''; ?></span>
                        </div>

                        <div>
                            <label for="subcategory">
                                <i class="fas fa-tag"></i> Sub kategori
                            </label>
                            <select id="subcategory" name="subcategory">
                                <option value="" disabled selected>-- Pilih Sub Kategori --</option>
                                <option value="Jamu Cair">Jamu Cair</option>
                                <option value="Jamu Bubuk">Jamu Bubuk</option>
                                <option value="Kapsul">Kapsul</option>
                                <option value="Lulur">Lulur</option>
                                <option value="Minyak Herbal">Minyak Herbal</option>
                                <option value="Minuman Instant">Minuman Instant</option>
                                <option value="Balsem">Balsem</option>
                                <option value="Madu Herbal">Madu Herbal</option>
                                <option value="Minuman Bubuk">Minuman Bubuk</option>
                                <option value="Minuman Tradisional">Minuman Tradisional</option>
                                <option value="Sabun Herbal">Sabun Herbal</option>
                                <option value="Serum">Serum</option>
                                <option value="Toner">Toner</option>
                                <option value="Pil Herbal">Pil Herbal</option>
                                <option value="Minuman Herbal">Minuman Herbal</option>
                                <option value="Teh Herbal">Teh Herbal</option>
                                <option value="Bubuk Herbal">Bubuk Herbal</option>
                                <option value="Sirup Herbal">Sirup Herbal</option>
                                <option value="Permen Herbal">Permen Herbal</option>
                                <option value="Inhaler">Inhaler</option>
                                <option value="Krim">Krim</option>
                                <option value="Patch">Patch</option>
                                <option value="Suplemen">Suplemen</option>
                                <option value="Vitamin Gummy">Vitamin Gummy</option>
                                <option value="Tetes Mata">Tetes Mata</option>
                                <option value="Aromaterapi">Aromaterapi</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <span
                                class="error-message"><?= isset($responseAddItems['subcategory']) ? $responseAddItems['subcategory'] : ''; ?></span>
                        </div>
                    </div> <!-- Harga Produk dan Stok -->
                    <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="price-field">
                            <label for="product-price">
                                <i class="fas fa-tag"></i> Stok Produk
                            </label>
                            <input type="text" id="product-price" name="product_stock"
                                placeholder="Masukkan stok produk">
                        </div>
                        <span
                            class="error-message"><?= isset($responseAddItems['number']) ? $responseAddItems['number'] : ''; ?></span>
                    </div>

                    <!-- Harga Produk -->
                    <div class="form-group price-group">
                        <div class="price-field">
                            <label for="product-price">
                                <i class="fas fa-tag"></i> Harga Produk
                            </label>
                            <input type="text" id="product-price" name="product_price"
                                placeholder="Masukkan harga produk">
                            <span
                                class="error-message"><?= isset($responseAddItems['number']) ? $responseAddItems['number'] : ''; ?></span>
                        </div>

                        <div class="stock-field">
                            <label for="product-stock">
                                <i class="fas fa-warehouse"></i> Stok Produk
                            </label>
                            <input type="number" id="product-stock" name="product_stock" min="0"
                                placeholder="Masukkan jumlah stok">
                            <span
                                class="error-message"><?= isset($responseAddItems['stock']) ? $responseAddItems['stock'] : ''; ?></span>
                        </div>
                    </div>

                    <!-- Product Gallery -->
                    <div class="product-gallery">
                        <label><i class="fas fa-images"></i> Product Gallery (max 3)</label>

                        <!-- Input untuk Gambar Satu -->
                        <div class="image-upload">
                            <label for="gambar_satu">
                                <i class="fas fa-image"></i> Gambar Utama
                            </label>
                            <input type="file" id="gambar_satu" name="gambar_satu" accept=".jpg,.jpeg,.png,.gif,.webp"
                                onchange="previewImage(event, 'preview-satu')">
                            <div id="preview-satu"></div>
                        </div>

                        <!-- Input untuk Gambar Dua -->
                        <div class="image-upload">
                            <label for="gambar_dua">
                                <i class="fas fa-image"></i> Gambar Kedua
                            </label>
                            <input type="file" id="gambar_dua" name="gambar_dua" accept=".jpg,.jpeg,.png,.gif,.webp"
                                onchange="previewImage(event, 'preview-dua')">
                            <div id="preview-dua"></div>
                        </div>

                        <!-- Input untuk Gambar Tiga -->
                        <div class="image-upload">
                            <label for="gambar_tiga">
                                <i class="fas fa-image"></i> Gambar Ketiga
                            </label>
                            <input type="file" id="gambar_tiga" name="gambar_tiga" accept=".jpg,.jpeg,.png,.gif,.webp"
                                onchange="previewImage(event, 'preview-tiga')">
                            <div id="preview-tiga"></div>
                        </div>

                        <span class="error-message">
                            <?= isset($responseAddItems['imageToLarge']) ? $responseAddItems['imageToLarge'] : ''; ?>
                        </span>
                        <span class="error-message">
                            <?= isset($responseAddItems['imageNotSupported']) ? $responseAddItems['imageNotSupported'] : ''; ?>
                        </span>
                        <span class="error-message">
                            <?= isset($responseAddItems['field']) ? $responseAddItems['field'] : ''; ?>
                        </span>
                    </div>

                    <!-- Tombol Submit dan Cancel -->
                    <div class="button-group">
                        <button type="submit" class="btn btn-update">
                            <i class="fas fa-plus"></i> Tambah Produk
                        </button>
                        <button type="button" class="btn btn-cancel" onclick="window.location.href='product.php'">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <script src="../node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
    <script>
        function previewImages(event) {
            var files = event.target.files;
            var previewContainer = document.getElementById('image-preview-container');
            previewContainer.innerHTML = '';

            Array.from(files).forEach(file => {
                if (file && file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        var imgElement = document.createElement('img');
                        imgElement.src = e.target.result;
                        imgElement.style.maxWidth = '150px';
                        imgElement.style.marginBottom = '10px';
                        imgElement.style.border = '1px solid #ccc';
                        previewContainer.appendChild(imgElement);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    </script>
    <script>
        <?php if (isset($success_message)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '<?= $success_message ?>'
            });
        <?php elseif (isset($error_message)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '<?= $error_message ?>'
            });
        <?php endif; ?>
    </script>
    <script>
        function previewImage(event, previewId) {
            const previewContainer = document.getElementById(previewId);
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    // Hapus konten lama jika ada
                    previewContainer.innerHTML = '';
                    // Tambahkan gambar baru
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '100px';
                    img.style.maxHeight = '100px';
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            } else {
                // Hapus pratinjau jika file dihapus
                previewContainer.innerHTML = '';
            }
        }
    </script>

</body>


</html>