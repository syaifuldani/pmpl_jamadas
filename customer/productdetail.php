<?php
session_start();
require '../config/function.php';

// Live Search
if (isset($_POST['query'])) {
    $searchTerm = $_POST['query'];
    searchProducts($searchTerm);
}

// Fungsi untuk mengecek apakah user sudah login
function isUserLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Cek apakah form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $product_id = isset($_POST['product_id']) ? trim($_POST['product_id']) : null;
    $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : null;
    $quantity = isset($_POST['quantity']) ? trim($_POST['quantity']) : null;
    $price = isset($_POST['price']) ? trim($_POST['price']) : null;

    // Validasi input
    if ($product_id && $user_id && is_numeric($quantity) && is_numeric($price)) {
        // Hitung total harga
        $total_price = $quantity * $price;

        // Panggil fungsi untuk menyimpan ke database
        if (addToCart($product_id, $user_id, $quantity, $total_price)) {
            // Simpan pesan sukses ke dalam session
            // Cek apakah produk sudah ada di keranjang
            $checkQuery = "SELECT jumlah FROM carts WHERE product_id = :product_id AND user_id = :user_id";
            $checkStmt = $GLOBALS['db']->prepare($checkQuery);
            $checkStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $checkStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $checkStmt->execute();
            $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingItem && $existingItem['jumlah'] > $quantity) {
                $_SESSION['cart_status'] = 'success';
                $_SESSION['cart_message'] = 'Kuantitas produk berhasil diperbarui di keranjang.';
            } else {
                $_SESSION['cart_status'] = 'success';
                $_SESSION['cart_message'] = 'Produk berhasil ditambahkan ke keranjang.';
            }

            // Redirect ke halaman productdetail.php dengan ID produk
            header("Location: productdetail.php?id=$product_id");
            exit();
        } else {
            // Jika gagal, simpan pesan error ke dalam session
            $_SESSION['cart_status'] = 'error';
            $_SESSION['cart_message'] = 'Gagal menambahkan produk ke keranjang.';

            // Redirect ke halaman productdetail.php dengan ID produk
            header("Location: productdetail.php?id=$product_id");
            exit();
        }
    } else {
        // Jika data tidak valid
        $_SESSION['cart_status'] = 'error';
        $_SESSION['cart_message'] = 'Data produk tidak valid.';

        // Redirect ke halaman productdetail.php dengan ID produk
        header("Location: productdetail.php?id=$product_id");
        exit();
    }
}

// Ambil detail produk berdasarkan ID
$product_id = isset($_GET['id']) ? $_GET['id'] : null;
if (is_null($product_id)) {
    echo "Error: Produk tidak ditemukan.";
    exit;
}

$product = getProductDetails($product_id);
$products = getRandomProducts(2);

// Get reviews for this product
$reviews = [];
$average_rating = 0;
try {
    if (isset($GLOBALS['db'])) {
        $db = $GLOBALS['db'];
        error_log("Fetching reviews for product ID: " . $product_id);

        $stmt = $db->prepare("
            SELECT r.*, u.nama_lengkap, r.created_at as review_date
            FROM reviews r 
            JOIN users u ON r.user_id = u.user_id 
            WHERE r.product_id = :product_id 
            ORDER BY r.created_at DESC
        ");

        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("Found " . count($reviews) . " reviews for product ID: " . $product_id);
    } else {
        error_log("Database connection not available in productdetail.php");
    }

    // Calculate average rating
    if (!empty($reviews)) {
        $total_rating = array_sum(array_column($reviews, 'rating'));
        $average_rating = round($total_rating / count($reviews), 1);
    }
} catch (Exception $e) {
    error_log("Error fetching reviews: " . $e->getMessage());
    $reviews = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk Jamu - Jamu Madura Online</title>
    <link rel="icon" href="../resources/img/icons/jamadas2.png" type="image/png">
    <link rel="stylesheet" href="../resources/css/navbar.css">
    <link rel="stylesheet" href="../resources/css/productdetail.css">
    <link rel="stylesheet" href="../resources/css/chat.css">
    <style>
        .product-category {
            margin: 10px 0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .category-badge,
        .subcategory-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .category-badge {
            background-color: #4CAF50;
            color: white;
        }

        .subcategory-badge {
            background-color: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #4CAF50;
        }

        .price {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
            margin: 15px 0;
        }

        .quantity {
            margin: 20px 0;
        }

        .quantity button {
            padding: 8px 15px;
            margin: 0 5px;
            border: 1px solid #ddd;
            background: #f8f9fa;
            cursor: pointer;
            border-radius: 5px;
        }

        .quantity button:hover {
            background: #e9ecef;
        }

        .quantity input[type="number"] {
            width: 60px;
            text-align: center;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .order-btn {
            background: #4CAF50;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
            transition: background 0.3s ease;
        }

        .order-btn:hover {
            background: #45a049;
        }

        .cart-icon {
            width: 18px;
            height: 18px;
        }
    </style>
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
            <div class="content-detail">
                <div class="product-detail">
                    <!-- Section Gambar -->
                    <div class="image-gallery">
                        <div class="image-zoom">
                            <div class="main-image" onmousemove="zoomImage(event)" onmouseleave="resetImage()">
                                <img id="mainImage" src="<?= $product['gambar_satu']; ?>"
                                    alt="<?= htmlspecialchars($product['nama_produk']); ?>">
                            </div>
                        </div>
                        <div class="thumbnail-images">
                            <img src="<?= $product['gambar_satu']; ?>" alt="Thumbnail 1" class="thumb"
                                onclick="changeImage(this)">
                            <?php if (isset($product['gambar_dua'])): ?>
                                <img src="<?= $product['gambar_dua']; ?>" alt="Thumbnail 2" class="thumb"
                                    onclick="changeImage(this)">
                            <?php endif; ?>
                            <?php if (isset($product['gambar_tiga'])): ?>
                                <img src="<?= $product['gambar_tiga']; ?>" alt="Thumbnail 3" class="thumb"
                                    onclick="changeImage(this)">
                            <?php endif; ?>
                        </div>
                    </div> <!-- Section Informasi Produk -->
                    <div class="product-info">
                        <?php if ($product): ?>
                            <!-- Nama produk -->
                            <h1><?= htmlspecialchars($product['nama_produk']); ?></h1>

                            <!-- Kategori produk -->
                            <?php if (isset($product['kategori']) && isset($product['sub_kategori'])): ?>
                                <div class="product-category">
                                    <span class="category-badge"><?= htmlspecialchars($product['kategori']); ?></span>
                                    <span class="subcategory-badge"><?= htmlspecialchars($product['sub_kategori']); ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Harga produk -->
                            <p class="price">Rp. <?= number_format($product['harga_produk'], 0, ',', '.'); ?></p>

                            <div class="description">
                                <h4>Deskripsi Produk</h4>
                                <p><?= htmlspecialchars($product['deskripsi']); ?></p>
                            </div>
                        <?php else: ?>
                            <h1>Produk tidak ditemukan.</h1>
                        <?php endif; ?>

                        <!-- Inputan Kuantitas -->
                        <div class="quantity">
                            <form action="" method="POST" id="addToCartForm">
                                <input type="hidden" name="product_id"
                                    value="<?= htmlspecialchars($product['product_id']); ?>">
                                <input type="hidden" name="price"
                                    value="<?= htmlspecialchars($product['harga_produk']); ?>">

                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <input type="hidden" name="user_id"
                                        value="<?= htmlspecialchars($_SESSION['user_id']); ?>">

                                    <button type="button" onclick="decreaseQuantity()">-</button>
                                    <input type="number" name="quantity" id="quantityInput" value="1">
                                    <button type="button" onclick="increaseQuantity()">+</button>

                                    <button type="submit" class="order-btn">
                                        <img src="../resources/img/icons/cart.png" class="cart-icon" alt="">
                                        Pesan Sekarang
                                    </button>
                                <?php else: ?>
                                    <a href="login.php" class="order-btn">
                                        <img src="../resources/img/icons/cart.png" class="cart-icon" alt="">
                                        Login untuk Pesan
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                        <h2>Produk Jamu Lainnya</h2>
                        <div class="product-container">
                            <?php
                            if (!empty($products) && !isset($products['error'])):
                                foreach ($products as $product):
                            ?>
                                    <div class="product-card">
                                        <img class="product" src="<?= htmlspecialchars($product['gambar_satu']); ?>"
                                            alt="<?= htmlspecialchars($product['nama_produk']); ?>">
                                        <div class="description">
                                            <p><?= htmlspecialchars($product['deskripsi']); ?></p>
                                        </div>
                                        <div class="product-info">
                                            <p class="product-name"><?= htmlspecialchars($product['nama_produk']); ?></p>
                                            <p class="product-price">Rp. <?= htmlspecialchars(number_format($product['harga_produk'], 0, ',', '.')); ?></p>
                                            <a href="productdetail.php?id=<?= htmlspecialchars($product['product_id']); ?>" class="detail-button">
                                                <p>Lihat Detail</p>
                                            </a>
                                        </div>
                                    </div>
                                <?php
                                endforeach;
                            else:
                                ?>
                                <div class="product-card">
                                    <p>Tidak ada produk jamu tersedia.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ulasan -->
            <div class="reviews-product">
                <div class="header">
                    <h3>Ulasan Produk</h3>
                    <?php if (!empty($reviews)): ?>
                        <div class="average-rating">
                            <div class="rating-number"><?= $average_rating ?></div>
                            <div class="rating-stars">
                                <?php
                                // Display average rating stars (1-5 from left to right)
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $average_rating) {
                                        echo '<span class="star filled">★</span>';
                                    } else {
                                        echo '<span class="star">☆</span>';
                                    }
                                }
                                ?>
                            </div>
                            <div class="rating-count"><?= count($reviews) ?> ulasan</div>
                        </div>
                    <?php endif; ?>
                    <hr color="black">
                </div>

                <div class="reviews">
                    <?php if (empty($reviews)): ?>
                        <p class="no-reviews">Belum ada ulasan untuk produk ini.</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <strong><?= htmlspecialchars($review['nama_lengkap']) ?></strong>
                                        <div class="review-date">
                                            <?= date('d F Y', strtotime($review['review_date'])) ?>
                                        </div>
                                    </div>
                                    <div class="review-rating">
                                        <?php
                                        // Display stars based on rating (1-5 from left to right)
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $review['rating']) {
                                                echo '<span class="star filled">★</span>';
                                            } else {
                                                echo '<span class="star">☆</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                                <p class="review-comment">
                                    <?= nl2br(htmlspecialchars($review['comment'])) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Overlay -->
        <div id="overlay" class="overlay">
            <div class="overlay-content">
                <div class="checkmark-container">
                    <div class="checkmark-circle">
                        <div class="checkmark">✓</div>
                    </div>
                </div>
                <p id="overlayMessage"></p>
                <a href="javascript:hideOverlay()" class="btn-lanjut">Lanjut Belanja</a>
            </div>
        </div>
    </div>

    <script src="../resources/js/thumnail.js"></script>
    <script src="../resources/js/zoomimage.js"></script>
    <script src="../resources/js/overlay.js"></script>
    <script>
        // Fungsi untuk quantity buttons
        function increaseQuantity() {
            const input = document.getElementById('quantityInput');
            input.value = parseInt(input.value) + 1;
        }

        function decreaseQuantity() {
            const input = document.getElementById('quantityInput');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }

        // Cek apakah ada session message dari PHP
        <?php if (isset($_SESSION['cart_status'])): ?>
            const cartStatus = '<?= $_SESSION['cart_status']; ?>';
            const cartMessage = '<?= $_SESSION['cart_message']; ?>';

            if (cartStatus === 'success') {
                showOverlay(cartMessage); // Tampilkan overlay jika berhasil
            } else if (cartStatus === 'error') {
                alert(cartMessage); // Tampilkan pesan error
            }

            // Hapus pesan session setelah ditampilkan
            <?php
            unset($_SESSION['cart_status']);
            unset($_SESSION['cart_message']);
            ?>
        <?php endif; ?>
    </script> <!-- Chatbot -->
    <div class="chat-toggle" id="chatToggleBtn">
        <img src="https://cdn-icons-png.flaticon.com/512/4712/4712027.png" alt="Chat" width="30" height="30">
    </div>

    <div class="chat-container">
        <div class="chat-header">
            <img src="https://cdn-icons-png.flaticon.com/512/4712/4712027.png" alt="Bot Avatar">
            <h3>Asisten Jamu</h3>
        </div>
        <div class="chat-box" id="chatMessages"></div>
        <div class="chat-input">
            <input type="text" placeholder="Tanyakan tentang jamu..." id="chat-input">
            <button onclick="sendMessage()">Kirim</button>
        </div>
    </div>
    <script src="../resources/js/burgersidebar.js?v=<?= time() ?>"></script>
    <script src="../resources/js/livesearch.js?v=<?= time() ?>"></script>
    <script src="../resources/js/chat.js?v=<?= time() ?>"></script>
    <script src="../resources/js/chat-debug.js?v=<?= time() ?>"></script>

    <script>
        // Pastikan chat toggle berfungsi setelah clear cache
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Product detail page chat initializing...');

            // Tunggu sebentar untuk memastikan semua script ter-load
            setTimeout(function() {
                const chatToggle = document.getElementById('chatToggleBtn');

                if (chatToggle) {
                    console.log('Chat toggle button found');

                    // Hapus event listener yang mungkin sudah ada
                    chatToggle.removeEventListener('click', toggleChat);

                    // Tambahkan event listener baru
                    chatToggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Chat toggle clicked!');

                        // Pastikan function toggleChat tersedia
                        if (typeof toggleChat === 'function') {
                            toggleChat();
                        } else {
                            console.error('toggleChat function not found');
                            // Fallback manual toggle
                            const chatContainer = document.querySelector('.chat-container');
                            if (chatContainer) {
                                chatContainer.classList.toggle('active');
                            }
                        }
                    });

                    console.log('Chat toggle event listener attached');
                } else {
                    console.error('Chat toggle button not found');
                }

                // Setup chat input event listener
                const chatInput = document.getElementById('chat-input');
                if (chatInput) {
                    chatInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            sendMessage();
                        }
                    });
                    console.log('Chat input event listener added');
                }

                // Load chat history
                loadChatHistory();
            }, 500);
        });

        // Tambahkan fallback jika semua gagal
        window.addEventListener('load', function() {
            setTimeout(function() {
                const chatToggle = document.getElementById('chatToggleBtn');
                if (chatToggle && !chatToggle.onclick) {
                    console.log('Adding fallback onclick handler');
                    chatToggle.onclick = function() {
                        console.log('Fallback chat toggle activated');
                        const chatContainer = document.querySelector('.chat-container');
                        if (chatContainer) {
                            chatContainer.classList.toggle('active');
                            console.log('Chat toggled via fallback');
                        }
                    };
                }
            }, 1000);
        });

        // Function to load chat history
        function loadChatHistory() {
            fetch('../config/process_chat.php')
                .then(response => response.json())
                .then(data => {
                    const chatBox = document.querySelector('.chat-box');
                    if (chatBox && data.history && data.history.length > 0) {
                        chatBox.innerHTML = ''; // Clear existing content
                        data.history.forEach(chat => {
                            // Add user message
                            const userMessage = document.createElement('div');
                            userMessage.className = 'chat-message user-message';
                            userMessage.textContent = chat.pesan_pengguna;
                            chatBox.appendChild(userMessage);

                            // Add bot response
                            const botMessage = document.createElement('div');
                            botMessage.className = 'chat-message bot-message';
                            botMessage.innerHTML = chat.respons_jawaban;
                            chatBox.appendChild(botMessage);
                        });
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }
                })
                .catch(error => console.error('Error loading chat history:', error));
        }
    </script>
</body>

</html>