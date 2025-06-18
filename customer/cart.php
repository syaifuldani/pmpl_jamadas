<?php
// Di PHP, sebelum memulai session
session_start([
    'cookie_secure' => true,
    'cookie_samesite' => 'Lax'
]);

// Security Headers
function setSecurityHeaders()
{
    // Protect against clickjacking
    header("X-Frame-Options: SAMEORIGIN");

    // Protect against XSS and other injections
    // Update Content Security Policy untuk mengizinkan cdnjs
    header("Content-Security-Policy: default-src 'self' https://*.midtrans.com; script-src 'self' https://*.midtrans.com https://cdnjs.cloudflare.com 'unsafe-inline' 'unsafe-eval';  style-src 'self' 'unsafe-inline';  img-src 'self' data: https:;  frame-src https://*.midtrans.com");

    // Prevent MIME-type sniffing
    header("X-Content-Type-Options: nosniff");

    // Enable XSS protection
    header("X-XSS-Protection: 1; mode=block");

    // Use HTTPS only
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

    // Prevent browsers from sending referrer information
    header("Referrer-Policy: same-origin");
}

// Gunakan function di atas
setSecurityHeaders();

// Inklusi file fungsi untuk mengambil data keranjang
require_once '../config/function.php';
require_once '../config/midtrans_config.php';

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Fungsi untuk memperbarui keranjang
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
        if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
            $result = updateCartItem($userId, $_POST['quantities']);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit();
        } else {
            // Jika quantities tidak valid, kirim respons error JSON
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Data kuantitas tidak valid.']);
            exit();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $_POST['shipping_data'] = [
            'courier' => $_POST['shipping_courier'],
            'service' => $_POST['shipping_service'],
            'cost' => $_POST['shipping_cost'],
            'eta' => $_POST['shipping_eta']
        ];

        $response = payment_handled($_POST, $userId);
    }

    // Cek apakah ada permintaan untuk menghapus item dari keranjang (Ini mungkin dari link langsung, bukan AJAX)
    // Blok ini harus dikonversi ke AJAX atau dihapus jika tidak ada lagi link langsung yang memicu
    if (isset($_GET['product_id'])) {
        $productId = $_GET['product_id'];
        // Panggil fungsi deleteCartItems untuk menghapus item
        $result = deleteCartItems($userId, $productId);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }

    // Ambil item keranjang dari database
    $cartItems = getCartItems($userId);

    // Ambil riwayat pesanan (sekarang hanya yang pending)
    $userOrders = getOrdersByID($userId, 'pending');
} else {
    // Set status HTTP menjadi 404 (Not Found)
    http_response_code(404);

    // Tampilkan halaman "Page Not Found"
    echo "<h1>404 - Page Not Found</h1>";
    echo "<p>Halaman yang Anda minta tidak ditemukan</p>";
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $cartId = isset($_GET['cart_id']) ? (int) $_GET['cart_id'] : 0;
    if ($cartId > 0) {
        $result = deleteCartItems($userId, $cartId);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Produk tidak valid untuk dihapus.']);
        exit();
    }
}

// Mendapatkan data item dari database
$cartItems = getCartItems($userId);

// Mendapatkan user_id dari session
$userId = $_SESSION['user_id'];

// Ambil riwayat pesanan (sekarang hanya yang pending)
$userOrders = getOrdersByID($userId, 'pending');

// Live Search
if (isset($_POST['query'])) {
    $searchTerm = $_POST['query'];
    searchProducts($searchTerm);
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang</title>
    <link rel="icon" href="../resources/img/icons/jamadas2.png" type="image/png">
    <link rel="stylesheet" href="../resources/css/cart.css">
    <link rel="stylesheet" href="../resources/css/navbar.css">
    <!-- @TODO: replace SET_YOUR_CLIENT_KEY_HERE with your client key -->
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key=<?php $_ENV['MIDTRANS_CLIENT_KEY'] ?> crossorigin="anonymous" importance="high" async></script>
    <!-- Note: replace with src="https://app.midtrans.com/snap/snap.js" for Production environment -->
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

        <div class="content">
            <div class="cart-container">
                <div class="cart-section">
                    <h1>Keranjang Anda!</h1>
                    <!-- Form untuk update keranjang -->
                    <form action="" method="POST" onsubmit="return false;">
                        <table>
                            <thead>                                <tr>
                                    <th>Product</th>
                                    <th>Harga Per Kertas</th>
                                    <th>Stok</th>
                                    <th>Jumlah</th>
                                    <th>Sub Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($cartItems)): ?>
                                    <?php foreach ($cartItems as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="items">
                                                    <img alt="Product Image" class="product-image" height="50"
                                                        src="<?= $item['gambar_satu']; ?>" width="50" />
                                                    <p><?= $item['nama_produk']; ?></p>
                                                </div>
                                            </td>                                            <td class="price">Rp.<?= number_format($item['harga_produk'], 2, ',', '.'); ?></td>
                                            <td class="stock">
                                                <span class="stock-badge <?= ($item['stok'] ?? 0) <= 5 ? 'low-stock' : 'normal-stock'; ?>">
                                                    <?= ($item['stok'] ?? 0); ?> unit
                                                </span>
                                            </td>
                                            <td>
                                                <div class="quantity-control">
                                                    <button type="button"
                                                        onclick="decreaseQuantity(<?= $item['cart_id']; ?>)" 
                                                        <?= ($item['stok'] ?? 0) == 0 ? 'disabled' : ''; ?>>-</button>
                                                    <input type="text"
                                                        name="quantities[<?= htmlspecialchars($item['product_id']); ?>]"
                                                        value="<?= htmlspecialchars($item['jumlah']); ?>" min="1"
                                                        max="<?= ($item['stok'] ?? 0); ?>"
                                                        id="quantityInput-<?= $item['cart_id']; ?>"
                                                        data-product-id="<?= htmlspecialchars($item['product_id']); ?>"
                                                        data-stock="<?= ($item['stok'] ?? 0); ?>"
                                                        onchange="handleManualQuantityChange(<?= $item['cart_id']; ?>)"
                                                        <?= ($item['stok'] ?? 0) == 0 ? 'disabled' : ''; ?>>
                                                    <button type="button"
                                                        onclick="increaseQuantity(<?= $item['cart_id']; ?>)"
                                                        <?= ($item['stok'] ?? 0) == 0 ? 'disabled' : ''; ?>>+</button>
                                                </div>
                                            </td>
                                            <td class="subtotal">
                                                Rp.<?= number_format($item['jumlah'] * $item['harga_produk'], 2, ',', '.'); ?>
                                            </td>
                                            <td>
                                                <a href="?action=delete&cart_id=<?= htmlspecialchars($item['cart_id']); ?>"
                                                    class="delete-item">
                                                    <img src="../resources/img/icons/trash.png" alt="Hapus Item">
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">Keranjang kosong.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <!-- Tombol untuk memperbarui keranjang (dihapus karena update real-time via AJAX) -->
                    </form>

                    <!-- Form untuk payment -->
                    <div class="warning-message">
                        Lengkapi Data Pengiriman Anda!!
                    </div>

                    <form id="payment-form" action="" method="POST">
                        <!-- Hidden inputs akan ditambahkan secara dinamis oleh JavaScript -->
                        <input type="hidden" name="shipping_cost" value="">
                        <input type="hidden" name="shipping_eta" value="">
                        <input type="hidden" name="shipping_courier" value="">
                        <input type="hidden" name="shipping_service" value="">
                        <div class="form-section">
                            <!-- Tambahkan hidden input untuk snap token -->
                            <input type="hidden" name="snap_token" id="snap-token">
                            <div class="form-group">
                                <h3>Keterangan Tambahan</h3>
                                <!-- <input type="date" name="tanggalacara" placeholder="Tanggal dan Waktu Acara">
                                <input type="text" name="lokasiacara" placeholder="Tempat/Lokasi Acara"> -->
                                <textarea name="keterangan_order" placeholder="Keterangan Tambahan"></textarea>
                                <p class="info">
                                    Tuliskan keterangan tambahan anda!!. (Apabila ada)
                                </p>
                            </div>
                            <div class="shipping-form">
                                <h3>Data Alamat Kirim</h3>

                                <div class="form-grid">
                                    <!-- Kolom Kiri -->
                                    <div class="form-column">
                                        <div class="form-group">
                                            <label>Nama Penerima</label>
                                            <input type="text" name="namapenerima" placeholder="Nama Lengkap Penerima">
                                        </div>

                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="email" placeholder="Email">
                                        </div>

                                        <div class="form-group">
                                            <label>Nomor Telepon</label>
                                            <input type="text" name="notelppenerima" placeholder="+628123456789">
                                            <small class="helper-text">Diawali dengan +62</small>
                                        </div>

                                        <div class="form-group">
                                            <label>Provinsi</label>
                                            <select name="provinsi" id="provinsi">
                                                <option value="">Pilih Provinsi</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Kabupaten/Kota</label>
                                            <select name="kota" id="kota" disabled>
                                                <option value="">Pilih Kabupaten/Kota</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Kecamatan</label>
                                            <input type="text" name="kecamatan" placeholder="Kecamatan">
                                        </div>
                                    </div>

                                    <!-- Kolom Kanan -->
                                    <div class="form-column">
                                        <div class="form-group">
                                            <label>Kelurahan/Desa</label>
                                            <input type="text" name="kelurahan" placeholder="Kelurahan/Desa">
                                        </div>

                                        <div class="form-group">
                                            <label>Alamat Lengkap</label>
                                            <textarea name="alamatpenerima"
                                                placeholder="Nama jalan, nomor rumah, RT/RW, patokan"></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label>Kode Pos</label>
                                            <input type="text" name="kodepos" placeholder="Kode Pos" pattern="[0-9]{5}">
                                        </div>
                                        <div class="form-group">
                                            <label>Pilih Kurir</label>
                                            <select name="courier" id="courier">
                                                <option value="">Pilih Kurir</option>
                                                <option value="jne">JNE</option>
                                                <!-- <option value="pos">POS Indonesia</option>
                                                <option value="tiki">TIKI</option> -->
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Button cek ongkir di bawah grid -->
                                <div class="form-actions">
                                    <button type="button" id="check-shipping" class="btn-check-shipping">
                                        Cek Ongkir
                                    </button>
                                </div>

                                <!-- Hasil cek ongkir -->
                                <div id="shipping-results" class="shipping-results"></div>
                            </div>
                            <button class="pay-btn" id="pay-btn">Bayar Sekarang</button>
                        </div>
                    </form>
                </div>

                <div id=" snap-container">
                </div>

                <div class="details-section">
                    <div class="order-history">
                        <h3>Pesanan Saya</h3>
                        <ul>
                            <?php if (!empty($userOrders)): ?>
                                <?php foreach ($userOrders as $order): ?>
                                    <li>
                                        <div class="information">
                                            <img src="../resources/img/icons/li-caption.png" alt="">
                                            <span><?= date('d/m/Y', strtotime($order['created_at'])) ?></span>
                                        </div>
                                        <a href="pesanan_saya.php?order_id=<?= htmlspecialchars($order['order_id']) ?>">Lihat →</a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>Tidak ada riwayat pesanan.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../resources/js/burgersidebar.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const cartForm = document.querySelector('.cart-section form');
            if (cartForm) {
                cartForm.addEventListener('submit', function(event) {
                    event.preventDefault(); // Mencegah submit form
                });
            }
        });
    </script>
    <script>
        // Fungsi untuk mengupdate kuantitas via AJAX
        async function updateQuantity(cartId, change) {
            const quantityInput = document.getElementById(`quantityInput-${cartId}`);
            let currentValue = parseInt(quantityInput.value);
            let newQuantity = currentValue + change;

            if (newQuantity < 1) {
                newQuantity = 1; // Pastikan kuantitas tidak kurang dari 1
            }
            quantityInput.value = newQuantity;

            const productId = quantityInput.getAttribute('data-product-id');
            const priceElement = quantityInput.closest('tr').querySelector('.price');
            // Hapus 'Rp.' dan titik ribuan, lalu ganti koma desimal menjadi titik
            let productPrice = parseFloat(priceElement.innerText.replace('Rp.', '').replace(/\./g, '').replace(',', '.'));

            // Buat objek FormData untuk mengirim data
            const formData = new FormData();
            formData.append('update_cart', 'true');
            formData.append(`quantities[${productId}]`, newQuantity);

            try {
                const response = await fetch('cart.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Update subtotal untuk item yang diubah
                    const subtotalElement = quantityInput.closest('tr').querySelector('.subtotal');
                    const newSubtotal = newQuantity * productPrice;
                    subtotalElement.innerText = 'Rp.' + newSubtotal.toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    // Update total pembayaran keseluruhan
                    updateOverallTotal();
                } else {
                    alert('Gagal memperbarui keranjang: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memperbarui keranjang.');
            }
        }        function increaseQuantity(cartId) {
            const quantityInput = document.getElementById(`quantityInput-${cartId}`);
            const currentQuantity = parseInt(quantityInput.value);
            const maxStock = parseInt(quantityInput.getAttribute('data-stock'));
            
            if (currentQuantity < maxStock) {
                updateQuantity(cartId, 1);
            } else {
                alert('Tidak dapat menambah kuantitas. Stok tersedia: ' + maxStock + ' unit');
            }
        }

        function decreaseQuantity(cartId) {
            updateQuantity(cartId, -1);
        }

        // Fungsi untuk menangani perubahan kuantitas manual
        function handleManualQuantityChange(cartId) {
            const quantityInput = document.getElementById(`quantityInput-${cartId}`);
            const maxStock = parseInt(quantityInput.getAttribute('data-stock'));
            let newQuantity = parseInt(quantityInput.value);

            if (isNaN(newQuantity) || newQuantity < 1) {
                newQuantity = 1;
                quantityInput.value = 1;
            } else if (newQuantity > maxStock) {
                newQuantity = maxStock;
                quantityInput.value = maxStock;
                alert('Kuantitas tidak boleh melebihi stok yang tersedia (' + maxStock + ' unit)');
            }
            updateQuantity(cartId, 0); // Panggil updateQuantity dengan perubahan 0 untuk memicu AJAX
        }

        // Fungsi untuk memperbarui total pembayaran keseluruhan
        function updateOverallTotal() {
            let overallTotal = 0;
            document.querySelectorAll('.subtotal').forEach(element => {
                const subtotalValue = parseFloat(element.innerText.replace('Rp.', '').replace(/\./g, '').replace(',', '.'));
                overallTotal += subtotalValue;
            });

            const totalPaymentElement = document.querySelector('.total-payment-amount'); // Asumsi ada elemen ini
            if (totalPaymentElement) {
                totalPaymentElement.innerText = 'Rp.' + overallTotal.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        }

        // Panggil updateOverallTotal saat halaman dimuat pertama kali
        document.addEventListener('DOMContentLoaded', updateOverallTotal);
    </script>
    <script>
        // Hapus event listener dan variabel terkait overlay penghapusan
        document.querySelectorAll('.delete-item').forEach(button => {
            button.addEventListener('click', async function(e) {
                e.preventDefault();
                const cartId = this.getAttribute('href').split('cart_id=')[1];

                if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
                    try {
                        const response = await fetch(`cart.php?action=delete&cart_id=${cartId}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();

                        if (data.success) {
                            // Hapus baris dari tabel HTML
                            this.closest('tr').remove();
                            updateOverallTotal(); // Perbarui total keseluruhan
                        } else {
                            alert('Gagal menghapus item: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menghapus item.');
                    }
                }
            });
        });
    </script>
    <script src="../resources/js/Order.js"></script>
    <script src="../resources/js/CheckOngkir.js"></script>
    <script src="../resources/js/validateInputCart.js"></script>
</body>

</html>