<?php
session_start();

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) && $_SESSION['user_id'] != 'admin') {
    // Jika tidak ada session login, redirect ke halaman login
    header("Location: login_admin.php");
    exit();
}

$jenishalaman = "Edit Produk";

require '../config/connection.php';

// Alias objek PDO dari $GLOBALS['db'] ke $pdo untuk kompatibilitas
$pdo = $GLOBALS['db'];

// Ambil dan sanitasi ID produk dari URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = (int) $_GET['id'];
} else {
    echo "ID produk tidak valid.";
    exit();
}

// Ambil data produk berdasarkan ID
$sql = "SELECT * FROM products WHERE product_id = :id";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "Produk tidak ditemukan.";
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
    exit();
}

// Proses form saat di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil dan sanitasi data dari form
    $nama_produk = trim($_POST['product_name']);
    $deskripsi = trim($_POST['description']);
    $manfaat = trim($_POST['manfaat']);
    $komposisi = trim($_POST['komposisi']);
    $kategori = trim($_POST['category']);
    $subkategori = trim($_POST['subcategory']);
    $harga_product = trim($_POST['product_price']);

    // Validasi input
    if (empty($nama_produk) || empty($deskripsi) || empty($kategori) || empty($subkategori) || empty($harga_product) || empty($manfaat) || empty($komposisi)) {
        $errors['field'] = 'Semua field wajib diisi!';
    }

    // Validasi harga_product adalah angka
    if (!is_numeric($harga_product)) {
        echo "Harga Produk harus berupa angka!";
        exit();
    }

    // Handle penghapusan gambar
    $delete_gambar_satu = isset($_POST['delete_gambar_satu']) ? true : false;
    $delete_gambar_dua = isset($_POST['delete_gambar_dua']) ? true : false;
    $delete_gambar_tiga = isset($_POST['delete_gambar_tiga']) ? true : false;

    // Handle upload gambar
    $gambar_satu = $product['gambar_satu'];
    $gambar_dua = $product['gambar_dua'];
    $gambar_tiga = $product['gambar_tiga'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    $image_fields = [
        'product_image_satu' => &$gambar_satu,
        'product_image_dua' => &$gambar_dua,
        'product_image_tiga' => &$gambar_tiga
    ];

    foreach ($image_fields as $field_name => &$image) {
        if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === 0) {
            $file_extension = strtolower(pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION));
            if (in_array($file_extension, $allowed_extensions)) {
                $image = file_get_contents($_FILES[$field_name]['tmp_name']);
            } else {
                echo "Format gambar tidak didukung: " . htmlspecialchars($_FILES[$field_name]['name']);
                exit();
            }
        }
    }

    // Hapus gambar jika checkbox diaktifkan
    if ($delete_gambar_satu) {
        $gambar_satu = null;
    }
    if ($delete_gambar_dua) {
        $gambar_dua = null;
    }
    if ($delete_gambar_tiga) {
        $gambar_tiga = null;
    }

    // Update data ke database
    $sql = "UPDATE products SET 
                nama_produk = :nama_produk, 
                deskripsi = :deskripsi, 
                manfaat_produk = :manfaat_produk, 
                komposisi_produk = :komposisi_produk, 
                harga_produk = :harga_produk, 
                gambar_satu = :gambar_satu, 
                gambar_dua = :gambar_dua, 
                gambar_tiga = :gambar_tiga, 
                kategori = :kategori,
                sub_kategori = :subkategori 
            WHERE product_id = :product_id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nama_produk', $nama_produk, PDO::PARAM_STR);
        $stmt->bindParam(':deskripsi', $deskripsi, PDO::PARAM_STR);
        $stmt->bindParam(':manfaat_produk', $manfaat, PDO::PARAM_STR);
        $stmt->bindParam(':komposisi_produk', $komposisi, PDO::PARAM_STR);
        $stmt->bindParam(':harga_produk', $harga_product, PDO::PARAM_STR);
        $stmt->bindParam(':gambar_satu', $gambar_satu, PDO::PARAM_LOB);
        $stmt->bindParam(':gambar_dua', $gambar_dua, PDO::PARAM_LOB);
        $stmt->bindParam(':gambar_tiga', $gambar_tiga, PDO::PARAM_LOB);
        $stmt->bindParam(':kategori', $kategori, PDO::PARAM_STR);
        $stmt->bindParam(':subkategori', $subkategori, PDO::PARAM_STR);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();

        header("Location: product.php?status=success&message=Produk berhasil diperbarui");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Jamadas</title>
    <link rel="stylesheet" href="./style/style.css">
    <style>
        .delete-checkbox {
            margin-left: 10px;
            color: red;
            cursor: pointer;
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
                    <div class="form-group">
                        <label for="product-name">Nama Produk</label>
                        <input type="text" id="product-name" name="product_name"
                            value="<?php echo htmlspecialchars($product['nama_produk']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description"
                            required><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>
                    </div>

                    <!-- Manfaat -->
                    <div class="form-group">
                        <label for="manfaat">Manfaat</label>
                        <textarea id="manfaat" name="manfaat"
                            placeholder="Masukkan manfaat produk"><?php echo htmlspecialchars($product['manfaat_produk']); ?></textarea>
                    </div>

                    <!-- Komposisi -->
                    <div class="form-group">
                        <label for="komposisi">Komposisi</label>
                        <textarea id="komposisi" name="komposisi"
                            placeholder="Masukkan komposisi produk"><?php echo htmlspecialchars($product['komposisi_produk']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="category">Kategori</label>
                        <select id="category" name="category" required>
                            <option value="Perawatan Kecantikan dan Tubuh" <?php echo ($product['kategori'] == 'Perawatan Kecantikan dan Tubuh') ? 'selected' : ''; ?>>
                                Perawatan Kecantikan dan Tubuh</option>
                            <option value="Reproduksi Wanita" <?php echo ($product['kategori'] == 'Reproduksi Wanita') ? 'selected' : ''; ?>>
                                Reproduksi Wanita</option>
                            <option value="Vitalitas Pria" <?php echo ($product['kategori'] == 'Vitalitas Pria') ? 'selected' : ''; ?>>
                                Vitalitas Pria</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subcategory">Sub Kategori</label>
                        <select id="subcategory" name="subcategory" required>
                            <option value="Jamu Cair" <?php echo ($product['sub_kategori'] == 'Jamu Cair') ? 'selected' : ''; ?>>
                                Jamu Cair</option>
                            <option value="Jamu Bubuk" <?php echo ($product['sub_kategori'] == 'Jamu Bubuk') ? 'selected' : ''; ?>>
                                Jamu Bubuk</option>
                            <option value="Lainnya" <?php echo ($product['sub_kategori'] == 'Lainnya') ? 'selected' : ''; ?>>
                                Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group price-group">
                        <div class="price-field">
                            <label for="product-price">Harga Produk</label>
                            <input type="text" id="product-price" name="product_price"
                                value="<?php echo htmlspecialchars($product['harga_produk']); ?>" required>
                        </div>
                    </div>

                    <div class="product-gallery">
                        <label>Gambar Satu</label>
                        <div class="image-upload" style="border: #000000 2px solid; margin-top:1rem;">
                            <div id="image-preview-container-satu" style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <!-- Preview Gambar Satu -->
                                <?php if ($product['gambar_satu']): ?>
                                    <div style="position: relative;">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($product['gambar_satu']); ?>"
                                            style="max-width: 150px; border: 1px solid #ccc;">
                                        <label class="delete-checkbox">
                                            <input type="checkbox" name="delete_gambar_satu" value="1"> Hapus
                                        </label>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="file-upload-satu" name="product_image_satu"
                                accept=".jpg,.jpeg,.png,.gif,.webp"
                                onchange="previewImages(event, 'image-preview-container-satu')">
                        </div>
                    </div>

                    <div class="product-gallery">
                        <label>Gambar Dua</label>
                        <div class="image-upload" style="border: #000000 2px solid; margin-top:1rem;">
                            <div id="image-preview-container-dua" style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <!-- Preview Gambar Dua -->
                                <?php if ($product['gambar_dua']): ?>
                                    <div style="position: relative;">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($product['gambar_dua']); ?>"
                                            style="max-width: 150px; border: 1px solid #ccc;">
                                        <label class="delete-checkbox">
                                            <input type="checkbox" name="delete_gambar_dua" value="1"> Hapus
                                        </label>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="file-upload-dua" name="product_image_dua"
                                accept=".jpg,.jpeg,.png,.gif,.webp"
                                onchange="previewImages(event, 'image-preview-container-dua')">
                        </div>
                    </div>

                    <div class="product-gallery">
                        <label>Gambar Tiga</label>
                        <div class="image-upload" style="border: #000000 2px solid; margin-top:1rem;">
                            <div id="image-preview-container-tiga" style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <!-- Preview Gambar Tiga -->
                                <?php if ($product['gambar_tiga']): ?>
                                    <div style="position: relative;">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($product['gambar_tiga']); ?>"
                                            style="max-width: 150px; border: 1px solid #ccc;">
                                        <label class="delete-checkbox">
                                            <input type="checkbox" name="delete_gambar_tiga" value="1"> Hapus
                                        </label>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="file-upload-tiga" name="product_image_tiga"
                                accept=".jpg,.jpeg,.png,.gif,.webp"
                                onchange="previewImages(event, 'image-preview-container-tiga')">
                        </div>
                    </div>


                    <div class="button-group">
                        <button type="submit" class="btn btn-update">Update</button>
                        <button type="button" class="btn btn-cancel"
                            onclick="window.location.href='product.php'">CANCEL</button>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <script>
        function previewImages(event, containerId) {
            const files = event.target.files;
            const previewContainer = document.getElementById(containerId);
            previewContainer.innerHTML = '';

            Array.from(files).forEach(file => {
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const imgElement = document.createElement('img');
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
</body>

</html>